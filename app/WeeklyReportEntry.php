<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WeeklyReportEntry extends Model
{
    protected $fillable = ['user_id', 'week','total_days', 'days_worked', 'client_time', 'project_time', 'rnd_time','comments'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getAllWeeklyReports()
    {
        $query = DB::table('weekly_report_entries as wre')
            ->join('users as u', 'u.id', '=', 'wre.user_id', 'left')
            ->orderBy('wre.week', 'desc')
            ->orderBy('wre.user_id', 'desc');
        return $query;
    }
}
