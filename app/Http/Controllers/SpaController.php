<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\TicketCreatedNotification;
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpaController extends Controller
{
    public function index()
    {
        return view('spa');
    }

    public function test(Request $request)
    {
        DB::reconnect('mysql');
        $ticket = Ticket::find(16);
        $this->dispatch(new TicketCreatedNotification($ticket));
    }
}
