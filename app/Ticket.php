<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    /**
     * Fields which can be filled through create method.
     *
     * @var array
     */
    protected $fillable = ['title', 'description', 'project_id', 'assigned_to', 'estimate_id', 'status', 'complete_date'];
}
