<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\Interfaces\SendMailInterface;
use App\Ticket;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TicketCreatedNotification extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $ticket;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SendMailInterface $mail)
    {
        $ticketObj = new Ticket;
        $ticket = $ticketObj->getTicketById($this->ticket->id);

        $select = ['u.name', 'u.email'];
        $followers = DB::table('ticket_followers as tf')
            ->select($select)
            ->join('users as u', 'u.id', '=', 'tf.user_id')
            ->where('tf.ticket_id', $this->ticket->id)
            ->get();

        // \Log::info('Handle ticket ' . $this->ticket->id);

        // send email to user who was assigned the ticket
        $mail->mail([
            'from' => 'amitav.roy@focalworks.in',
            'fromName' => 'Amitav Roy',
            'to' => 'amitav.roy@focalworks.in',
            'toName' => 'Amitav Roy',
            'subject' => '[New ticket] ' . $ticket->id . '-' . $ticket->title,
            'mailBody' => view('mails.ticket-assigned', compact('ticket')),
        ]);

        foreach ($followers as $follower) {
            $mail->mail([
                'from' => 'amitav.roy@focalworks.in',
                'fromName' => 'Amitav Roy',
                'to' => $follower->email,
                'toName' => $follower->name,
                'subject' => '[New ticket to follow] ' . $ticket->id . '-' . $ticket->title,
                'mailBody' => view('mails.ticket-assigned', compact('ticket')),
            ]);
        }
    }
}
