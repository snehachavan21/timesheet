<?php

namespace App\Console\Commands;

use App\Services\Interfaces\SendMailInterface;
use App\TimeEntry;
use Illuminate\Console\Command;

class NotifyUserTimeEntriesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify-user-daily-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(SendMailInterface $mail)
    {
        $timeEntryObj = new TimeEntry();
        $users = $timeEntryObj->getPreviousDayTimeEntry();
        if(!empty($users)) {
            $this->sendEmail($mail, $users);
        }
    }

    private function sendEmail($mail, $userData)
    {
        $date = date('Y-m-d', strtotime('-1 day', time()));

        $mail->mail([
            'from' => 'amitav.roy@focalworks.in',
            'fromName' => 'Amitav Roy',
            'to' => 'pruthvi.paghdal@focalworks.in',
            'toName' => '',
            'subject' => 'List of Employees forgot to fill time sheet for date : '. $date,
            'mailBody' => view('mails.user-time-entry-status-mail')->with('userData', $userData),
        ]);
    }
}
