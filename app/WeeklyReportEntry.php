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
}
