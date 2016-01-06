<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class TicketController extends Controller
{
    public function getTicketTypes()
    {
        return [
            'new-feature' => 'New Feature',
            'bug' => 'Bug',
            'invalid' => 'Invalid',
            'duplicate' => 'Duplicate',
        ];
    }

    public function getTicketStatus()
    {
        return [
            'Assigned' => 'Assigned',
            'In progress' => 'In progress',
            'Won\'t Fix' => 'Won\'t Fix',
            'Ready for testing' => 'Ready for testing',
            'Done' => 'Done',
        ];
    }
}
