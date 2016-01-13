<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TimeEntry extends Model
{
    /**
     * Define the fillable columns.
     *
     * @var array
     */
    protected $fillable = ['desc', 'user_id', 'project_id', 'project_name', 'client_name', 'time'];

    public function getCreatedAtAttribute($value)
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $value);
        return $date->format('D, dS M');
    }

    /**
     * Relation with Time entry and a project.
     *
     * @return Eloquent relation
     */
    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function tags()
    {
        return $this->morphToMany('App\Tag', 'taggable');
    }

    public function getManagerTrackerReport()
    {
        $query = DB::table('time_entries as te')
            ->select(['u.*', 'p.*', 'c.*', 'tg.*', 't.*', 'u.name as username',
                'p.name as projectName', 'c.name as clientName',
                DB::raw("GROUP_CONCAT(t.name) as tags"), 'te.desc as desc',
                'te.time as time', 'te.created_at as createdDate'])
            ->join('users as u', 'u.id', '=', 'te.user_id', 'left')
            ->join('projects as p', 'p.id', '=', 'te.project_id', 'left')
            ->join('clients as c', 'c.id', '=', 'p.client_id', 'left')
            ->join('taggables as tg', 'tg.taggable_id', '=', 'te.id', 'left')
            ->join('tags as t', 't.id', '=', 'tg.tag_id', 'left')
            ->groupBy('te.id')
            ->orderBy('createdDate', 'desc');

        return $query;
    }

    public function getTimerTrackerReport()
    {
        $query = DB::table('time_entries as te')
            ->join('users as u', 'u.id', '=', 'te.user_id', 'left')
            ->join('projects as p', 'p.id', '=', 'te.project_id', 'left')
            ->join('clients as c', 'c.id', '=', 'p.client_id', 'left')
            ->join('taggables as tg', 'tg.taggable_id', '=', 'te.id', 'left')
            ->join('tags as t', 't.id', '=', 'tg.tag_id', 'left')
            ->groupBy('te.id')
            ->orderBy('te.created_at', 'desc');

        return $query;
    }

    public function getProjectWiseReport($sdate, $edate)
    {
        $select = [
            'te.project_id as project_id',
            'p.name as projectName',
            'c.name as clientName',
            DB::raw("GROUP_CONCAT(DISTINCT u.name) as team"),
            DB::raw("SUM(te.time) as totalTime"),
            DB::raw("DATE(te.created_at) as createdDate"),
        ];

        $query = DB::table('time_entries as te')
            ->select($select)
            ->join('users as u', 'u.id', '=', 'te.user_id', 'left')
            ->join('projects as p', 'p.id', '=', 'te.project_id', 'left')
            ->join('clients as c', 'c.id', '=', 'p.client_id', 'left')
            ->whereRaw('DATE(te.created_at) BETWEEN "' . $sdate . '" AND "' . $edate . '" ')
            ->groupBy('te.project_id')
            ->orderBy('te.created_at', 'desc')
            ->get();

        return $query;
    }

    public function getProjectWiseDetailedReport($sdate, $edate)
    {
        $select = [
            'te.project_id as project_id',
            'p.name as projectName',
            'c.name as clientName',
            DB::raw("GROUP_CONCAT(DISTINCT u.name) as team"),
            'te.time as time',
            DB::raw("SUM(te.time) as totalTime"),
            DB::raw("DATE(te.created_at) as createdDate"),
        ];

        $query = DB::table('time_entries as te')
            ->select($select)
            ->join('users as u', 'u.id', '=', 'te.user_id', 'left')
            ->join('projects as p', 'p.id', '=', 'te.project_id', 'left')
            ->join('clients as c', 'c.id', '=', 'p.client_id', 'left')
            ->whereRaw('DATE(te.created_at) BETWEEN "' . $sdate . '" AND "' . $edate . '" ')
            ->groupBy(DB::raw("te.project_id,te.time WITH ROLLUP"))
            ->get();

        return $query;
    }

    public function getDateWiseReport($sdate, $edate)
    {
        $select = [
            'te.desc as description',
            'te.time as time',
            'u.name as username',
            'p.name as projectName',
            'c.name as clientName',
            DB::raw("GROUP_CONCAT(t.name) as tags"),
            DB::raw("DATE(te.created_at) as createdDate"),
        ];

        $query = DB::table('time_entries as te')
            ->select($select)
            ->join('users as u', 'u.id', '=', 'te.user_id', 'left')
            ->join('projects as p', 'p.id', '=', 'te.project_id', 'left')
            ->join('clients as c', 'c.id', '=', 'p.client_id', 'left')
            ->join('taggables as tg', 'tg.taggable_id', '=', 'te.id', 'left')
            ->join('tags as t', 't.id', '=', 'tg.tag_id', 'left')
            ->groupBy('te.created_at')
            ->orderBy('te.created_at', 'desc')
            ->whereRaw('DATE(te.created_at) BETWEEN "' . $sdate . '" AND "' . $edate . '" ')
            ->get();

        return $query;
    }

    public function getLatestBackdateTimeEntries()
    {
        return DB::table('backdate_timeentry as bt')
            ->join('users as u', 'u.id', '=', 'bt.user_id')
            ->orderBy('bt.id', 'desc')
            ->get();
    }

    public function getLatestRequestBackdateTimeEntries()
    {
        $query = DB::table('backdate_requests as br')
            ->join('users as u', 'u.id', '=', 'br.project_manager_id', 'left')
            ->orderBy('br.id', 'desc')
            ->get();
        return $query;
    }

    /**
     * Function to check if users have filled there timesheet for previous day
     * @return [type] [description]
     */
    public function getPreviousDayTimeEntry()
    {
        $query = DB::table('users')
            ->whereRaw(' id not in(select user_id from time_entries
            where DATE(created_at) = DATE( DATE_SUB( NOW() , INTERVAL 1 DAY )) order by `created_at` desc)')
            ->get();

        return $query;
    }

    public function timeEntryForTicket($string)
    {
        $string = trim($string);

        $string = str_replace('#', '', $string);

        $ticket = Ticket::find($string);

        if ($ticket) {
            return $ticket;
        }

        return false;
    }
}
