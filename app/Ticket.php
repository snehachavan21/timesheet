<?php

namespace App;

use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    /**
     * Fields which can be filled through create method.
     *
     * @var array
     */
    protected $fillable = ['title', 'description', 'project_id', 'assigned_to', 'estimate_id', 'status', 'complete_date', 'type'];

    public function comments()
    {
        return $this->morphToMany('App\Comment', 'commentable');
    }

    protected function getTicketBaseQuery()
    {
        $select = ['t.*', 'u.name as assigned_to', 'p.name as project'];
        $query = DB::table('tickets as t');
        $query->select($select);
        $query->join('users as u', 'u.id', '=', 't.assigned_to', 'left');
        $query->join('projects as p', 'p.id', '=', 't.project_id', 'left');
        $query->orderBy('t.id', 'desc');

        return $query;
    }

    public function getTickets()
    {
        $query = DB::select(DB::raw("SELECT t.title, t.id, commentData.*, p.name AS project, u.name AS assigned_to, t.`type`, t.`status`, t.`complete_date`
            FROM tickets AS t
            LEFT JOIN (
              SELECT cb.*, count(*) AS ccount FROM `commentables` AS cb GROUP BY cb.`commentable_id`
              ) AS commentData
        ON commentData.commentable_id = t.id AND commentData.commentable_type LIKE '%Ticket'
        LEFT JOIN projects AS p ON p.id = t.`project_id`
        LEFT JOIN users AS u ON u.id = t.`assigned_to` ORDER BY t.id DESC"));

        return $query;

        $query = $this->getTicketBaseQuery();

        $result = $query->get();

        return $result;
    }

    public function getMyTickets()
    {
        $query = $this->getTicketBaseQuery();
        $query->where('t.assigned_to', Auth::user()->id);
        $result = $query->get();

        return $result;
    }

    public function getTicketById($id)
    {
        $select = ['t.description as comment', 't.title', 't.type', 't.project_id', 't.assigned_to', 't.complete_date', 't.id', 't.created_at', 't.created_by', 't.status'];
        $query = DB::table('tickets as t');
        $query->select($select);
        $query->where('t.id', $id);
        $query->orderBy('t.id', 'desc');

        $result = $query->first();

        // adding projects, users and other information
        $result->project[0] = Project::find($result->project_id);

        $result->users[0] = User::find($result->assigned_to);

        $result->completeDate = Carbon::parse($result->complete_date)->toDateString();

        $result->created_by = User::find($result->created_by);

        $followers = DB::table('ticket_followers')->where('ticket_id', $id)->get();

        foreach ($followers as $follower) {
            $result->followers[] = User::find($follower->user_id);
        }

        return $result;
    }

    public function getTicketComments($id)
    {
        $select = ['c.comment', 'c.id', 'u.name', 'c.created_at'];

        $query = DB::table('commentables as cb')
            ->select($select)
            ->where('cb.commentable_id', $id)
            ->join('comments as c', 'c.id', '=', 'cb.comment_id', 'left')
            ->join('users as u', 'u.id', '=', 'c.user_id', 'left')
            ->orderBy('c.id', 'desc')
            ->get();

        return $query;
    }
}
