<?php

namespace App\Policies;

use App\User;

class TrackerPolicy
{
    public function deleteOwnTimeEntry()
    {

    }

    public function viewTrackerReport()
    {
        $allowed = ['Admin', 'Project Manager'];

        $userRoles = User::roles();

        foreach ($userRoles as $role) {
            if (in_array($role->role, $allowed)) {
                return true;
            }
        }

        return false;
    }


    public function createWeeklyReport()
    {
        $allowed = ['Developer'];

        $userRoles = User::roles();

        foreach ($userRoles as $role) {
            if (in_array($role->role, $allowed)) {
                return true;
            }
        }

        return false;
    }

    public function viewWeeklyReport()
    {
        $allowed = ['Admin', 'Project Manager'];

        $userRoles = User::roles();

        foreach ($userRoles as $role) {
            if (in_array($role->role, $allowed)) {
                return true;
            }
        }

        return false;
    }

}
