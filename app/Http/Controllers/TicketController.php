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
}
