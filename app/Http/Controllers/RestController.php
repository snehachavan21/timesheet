<?php
/**
 * Created by PhpStorm.
 * User: komal
 * Date: 30/12/15
 * Time: 11:37 AM
 */
namespace App\Http\Controllers;

use App\Project;
use App\Tag;
use App\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RestController extends Controller {

    /**
     * Get the list of timeEntries of the user
     *
     * @return mixed
     */
    public function getTimeEntryByUid(Request $request) {
        $uid = $request->input('uid');
        $query = DB::table('time_entries as te')
            ->select(["te.*",'projects.name as project', DB::raw("GROUP_CONCAT(tags.id) AS tags, te.user_id as uid"), DB::raw("UNIX_TIMESTAMP(te.created_at)*1000 as start_time"), 'te.time AS total_time', 'es.estimate_id as estimate_id'])
            ->join('projects', 'projects.id', '=', 'te.project_id')
            ->join('taggables as tgb', 'tgb.taggable_id', '=', 'te.id')
            ->leftJoin('tags as tags', 'tags.id', '=', 'tgb.tag_id')
            ->leftJoin('time_entry_estimates as es', 'es.time_entry_id', '=', 'te.id')
//            ->where('te.user_id', '=', 1)
            ->where('te.user_id', '=', $uid)
            ->where('te.created_at', '>=', DB::raw("NOW( ) - INTERVAL 1
MONTH"))
            ->groupBy('te.id')
            ->orderBy('te.created_at', 'desc')
            ->get();

        return response($query, 201);

    }

    /**
     * Get the list of projects in the system with client and estimate data
     *
     * @return mixed
     */
    public function getProjectList()
    {
        return Project::with('client')->with('estimates')->orderBy('name')->get();
    }

    /**
     * Do user login by checking the username and password
     *
     */
    public function checkAuth(Request $request)
    {
        // setting the credentials array
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        // if the credentials are wrong
        if (!Auth::attempt($credentials)) {
            return response(['message' => 'Username password does not match'], 403);
        }

        return response(['data' => Auth::user()], 201);
    }

    /**
     * Get the list of Tags
     *
     * @return mixed
     */
    public function getTags() {
        $tags = Tag::all();
        return response($tags->toArray(), 201);
    }

    /**
     * Save timeEntry
     *
     * @return mixed
     */
    public function save(Request $request) {
        return  $this->saveTimesheet($request->all());

    }

    /**
     * Helper function to Save timeEntry
     *
     * @return mixed
     */
    public function saveTimesheet($postdata) {
        try {
            $output = array();
//            \Log::info(print_r($postdata, true));
            DB::beginTransaction();
            $project = Project::with('client')->find($postdata['project_id']);

            // if the user id is set, it means it's a backdate entry
            $createdAt = Carbon::now();
            $updatedAt = Carbon::now();
            $userId = 0;
            if ($postdata['uid']) {
                $userId = $postdata['uid'];
                $timestamp = $postdata['start_time']/1000;
                $createdAt = Carbon::createFromTimestamp($timestamp)->toDateTimeString();
                $updatedAt =  Carbon::createFromTimestamp($timestamp)->toDateTimeString();
            }

            $entryId = DB::table('time_entries')->insertGetId([
                'desc' => $postdata['desc'],
                'user_id' => $userId,
                'project_id' => $project->id,
                'project_name' => $project->name,
                'client_name' => $project->client->name,
                'time' => $postdata['total_time'],
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            $tagsArr = explode(',',$postdata['tags']);
            // adding the entry of the ticket with tags mapping table
            foreach ($tagsArr as $key => $value) {
                    DB::table('taggables')->insert([
                        'tag_id' => $value,
                        'taggable_id' => $entryId,
                        'taggable_type' => 'ticket',
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
            }


            $output= array(
                'id' => $entryId,
                'uid' => $userId,
                'desc' => $postdata['desc'],
                'project_id' => $postdata['project_id'],
                'project' => $project->name,
                'total_time' => $postdata['total_time'],
                'start_time' =>$postdata['start_time'],
                'end_time' => $postdata['end_time'],
                'tags' => $postdata['tags'],
                'client_name' => $project->client->name,
            );

            if (isset($postdata['estimate_id']) && $postdata['estimate_id'] != 0) {
                DB::table('time_entry_estimates')->insert([
                    'time_entry_id' => $entryId,
                    'estimate_id' => $postdata['estimate_id'],
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                DB::update("UPDATE estimates SET hours_consumed = hours_consumed + :hours WHERE id = :id", [
                    'hours' => $postdata['total_time'],
                    'id' => $postdata['estimate_id'],
                ]);
            }

            $output['estimate_id'] = $postdata['estimate_id'];

            DB::commit();
            return response($output, 201);
        } catch (\PDOException $e) {
            DB::rollBack();
            abort(403, 'Data was not saved. Try again' . $e->getMessage());
        }

    }

    /**
     * Delete timeEntry
     */
    public function deleteTimeEntry(Request $request) {
        $trackerId = $request->input('id');

        $entry = TimeEntry::findOrFail($trackerId);

        $estimateRecord = DB::table('time_entry_estimates')->where('time_entry_id', $entry->id)->first();

        if (count($estimateRecord) > 0) {
            $estId = $estimateRecord->estimate_id;

            DB::update("UPDATE estimates SET hours_consumed = hours_consumed - :hours WHERE id = :id", [
                'hours' => $entry->time,
                'id' => $estId,
            ]);
        }

        $entry->delete();

        DB::table('time_entry_estimates')->where('time_entry_id', $entry->id)->delete();

        DB::table('taggables')
            ->where('taggable_id', $entry->id)
            ->where('taggable_type', 'ticket')
            ->delete();
    }

    /**
     * Sync offline entries to online
     */
    public function syncTimesheets(Request $request) {
        $post_data = $request->input();
        foreach($post_data as $tData) {
            $uid =  $tData['uid'];
            if(!$tData['status']) {
                $already_saved = 0;
                if(isset($tData['id'])) {
                    $already_saved = Timesheet::where('id', '=', $tData['id'])->count();
                    if($already_saved && $tData['deleted']) {
                        Timesheet::where('id' , '=', $tData['id'])->delete();
                    }
                }

                if($already_saved) {

                }else {
                    $timesheet = $this->saveTimesheet($tData);
                }
            }
        }
//        return $this->getTimeEntryByUid();
        return response("Timesheet synced successfully", 201);
    }
}
