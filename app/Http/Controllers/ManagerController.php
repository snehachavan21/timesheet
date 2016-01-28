<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Services\PieGraph;
use App\TimeEntry;
use App\User;
use App\WeeklyReportEntry;
use App\Comment;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Input;
use Session;
use Validator;
use View;

class ManagerController extends Controller
{
    public function __construct()
    {
        $allowed = ['Developer','Admin', 'Project Manager'];
        $userRoles = User::roles();
        $flag = false;

        foreach ($userRoles as $role) {
            if (in_array($role->role, $allowed)) {
                $flag = true;
            }
        }

        if (true != $flag) {
            abort(403, 'Now allowed');
        }
    }

    public function getTimeReport()
    {
        return view('manager.report-main');
    }

    public function downloadReport()
    {
        Excel::create('Timesheet_Report_' . time(), function ($excel) {
            $timeEntryObj = new TimeEntry;
            $timeEntryQuery = $timeEntryObj->getManagerTrackerReport();
            $timeEntries = $timeEntryQuery->get();

            $data = [];
            foreach ($timeEntries as $entry) {
                $data[] = [
                    'date' => Carbon::parse($entry->createdDate)->toDateString(),
                    'description' => $entry->desc,
                    'time' => $entry->time,
                    'username' => $entry->username,
                    'projectName' => $entry->projectName,
                    'clientName' => $entry->clientName,
                    'tags' => $entry->tags,
                ];
            }

            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->download('xls');
    }
    //param 1 : $sdate = 'yyyy-mm-dd'
    //param 2 : $edate = 'yyyy-mm-dd'
    public function downloadProjectWiseReport($sdate, $edate)
    {
        $timeEntryObj = new TimeEntry;
        $timeEntries = $timeEntryObj->getProjectWiseReport($sdate, $edate);

        Excel::create('Timesheet_ProjectWise_Report_' . time(), function ($excel) use ($timeEntries) {

            $data = [];
            foreach ($timeEntries as $entry) {
                $data[] = [
                    'Date' => $entry->createdDate,
                    'Project Name' => $entry->projectName,
                    'Client Name' => $entry->clientName,
                    'Total Time' => $entry->totalTime,
                    'Team' => $entry->team,

                ];
            }

            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->download('xls');
    }

    public function downloadProjectWiseDetailedReport($sdate, $edate)
    {
        $timeEntryObj = new TimeEntry;
        $timeEntries = $timeEntryObj->getProjectWiseDetailedReport($sdate, $edate);

        Excel::create('Timesheet_ProjectWise_Detailed_Report_' . time(), function ($excel) use ($timeEntries) {
            //echo "ss<pre>";print_r($timeEntries);die;
            $data = [];
            foreach ($timeEntries as $entry) {
                $data[] = [
                    'Date' => $entry->createdDate,
                    'Project Name' => $entry->projectName,
                    'Client Name' => $entry->clientName,
                    'Time' => $entry->time,
                    'Total Time' => $entry->totalTime,
                    'Team' => $entry->team,
                ];
            }

            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $last_row = count($data) + 1;
                $sheet->fromArray($data);
                foreach ($data as $k => $v) {
                    $row_num = $k + 2;
                    if (empty($v['Time'])) {
                        $sheet->row($row_num, function ($row) {
                            // call cell manipulation methods
                            $row->setBackground('#FFFF00');
                        });
                        $sheet->row($row_num, array($v['Date'], $v['Project Name'] . ' Total', $v['Client Name'], $v['Time'], $v['Total Time'], $v['Team']));
                    }
                    $sheet->row($last_row, array('', 'Total', '', '', $v['Total Time'], ''));
                    //Last row: total of all projects
                    $sheet->row($last_row, function ($row) {
                        // call cell manipulation methods
                        $row->setBackground('#0000FF');
                    });
                }
            });
        })->download('xls');
    }

    public function downloadDateWiseReport($sdate, $edate)
    {
        $timeEntryObj = new TimeEntry;
        $timeEntries = $timeEntryObj->getDateWiseReport($sdate, $edate);
        Excel::create('Timesheet_DateWise_Report_' . time(), function ($excel) use ($timeEntries) {

            $data = [];
            foreach ($timeEntries as $entry) {
                $data[] = [
                    'Date' => $entry->createdDate,
                    'Task' => $entry->description,
                    'Project Name' => $entry->projectName,
                    'Client Name' => $entry->clientName,
                    'Tags' => $entry->tags,
                    'Duration' => $entry->time,
                    'Team' => $entry->username,
                ];
            }

            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->download('xls');
    }

    public function createPieChart($sdate, $edate)
    {
        $timeEntryObj = new TimeEntry;
        $timeEntries = $timeEntryObj->getProjectWiseReport($sdate, $edate);

        $data = [];
        $cnt = 0;
        $pie = new PieGraph();
        foreach ($timeEntries as $entry) {
            $time_arr[] = $entry->totalTime;
            $pname_arr[] = $entry->projectName;
            $color_arr[] = $pie->random_color();
        }
        $pie->setImage(200, 100, $time_arr);

        // colors for the data
        $color_arr = ["#ff0000", "#ff8800", "#0022ff", "#989898", "#6600CC", "#FF0000 ", "#660066", "#CCFF00", "#FF0099", "#33ff99", "#33ff11"];
        $pie->setColors($color_arr);

        // legends for the data
        $pie->setLegends($pname_arr);

        // Display creation time of the graph
        $pie->DisplayCreationTime();

        // Height of the pie 3d effect
        $pie->set3dHeight(15);

        // Display the graph
        $pie->display();
    }
    public function getWeeklyReport()
    {
        return view('manager.weekly-report-main');
    }

    /**
     *
     * @param Request $request
     * @return mixed
     */
    public function getWeeklyReportSearch(Request $request)
    {
        $weeklyReportObj = new WeeklyReportEntry;
        $weeklyReportQuery = $weeklyReportObj->getAllWeeklyReports();
        $totalCount = 0;

        //used select field here as we are using same query to get count
        //set select fields for listing
        $select = [
            'wre.created_at as created_at',
            'wre.week as week',
            DB::raw("DATE_FORMAT(wre.start_of_week,'%b %d' ) as start"),
            DB::raw("DATE_FORMAT(wre.end_of_week,'%b %d' ) as end"),
            'wre.total_days as total_days',
            'wre.days_worked as days_worked',
            'wre.client_time as client_time',
            'wre.internal_time as internal_time',
            'wre.rnd_time as rnd_time',
            'u.name as username',
            DB::raw("DATE(wre.created_at) as createdDate"),
        ];

        //set filters on query
        $filters = $request->input('filters');


        if(isset($filters['users']) && !empty($filters['users'])) {
            $weeklyReportQuery->whereIn('u.id', $filters['users']);
        }

        if(isset($filters['startDate']) && $filters['startDate']!="") {
            $weeklyReportQuery->whereDate('wre.start_of_week','>=', date('Y-m-d', strtotime($filters['startDate'])));
        }

        if(isset($filters['endDate']) && $filters['endDate']!="") {
            $weeklyReportQuery->whereDate('wre.end_of_week','<=', date('Y-m-d', strtotime($filters['endDate'])));
        }
        //get total count
        $aggregateResult = \DB::table(\DB::raw(' ( ' . $weeklyReportQuery->select('week')->toSql() . ' ) AS week '))
            ->selectRaw('count(*) AS totalCount')
            ->mergeBindings($weeklyReportQuery)->first();

        if($aggregateResult) {
            $totalCount = $aggregateResult->totalCount;
        }

        $weeklyReportQuery->select($select);

        //pagination limit
        $range = explode('-', $request->header('range'));

        $weeklyReportQuery->skip($range[0]);

        $limit = (0 == $range[0]) ? $range[1] : ($range[1] - $range[0]);

        $weeklyReportQuery->limit($limit);

        return response(['data' => $weeklyReportQuery->get()])
            ->header('Content-Range', "{$request->header('range')}/{$totalCount}");
    }

    public function addWeeklyReport()
    {
        $user = Auth::user();
        $role = User::roles();
        $user_role = $role[0]->role;

        $now = Carbon::now();
        $now1 = clone $now;
        $now2 = clone $now;

        $monday = $now->startOfWeek();
        $friday =  $now1->startOfWeek()->addDay(4);
        //$endOfWeek = $now->endOfWeek();
        $timeEntryObj = new TimeEntry;
        $timeEntries = $timeEntryObj->getDaysUserFilledTimesheetInWeek($monday,$friday,$user->id);

        $weekly_report = WeeklyReportEntry::where('user_id', $user->id)->paginate(20);

        $data['user_id'] = $user->id;
        $data['user_name'] = $user->name;
        $data['week'] = ($now2->weekOfMonth < 10) ? '0'.$now2->weekOfMonth : $now2->weekOfMonth;
        $data['start'] = $monday->format('M d');
        $data['start_of_week'] = $monday->format('Y-m-d');
        $data['end']   = $friday->format('M d');
        $data['end_of_week']   = $friday->format('Y-m-d');
        $data['days_worked'] = $timeEntries[0]->cnt;
        $data['weekly_report'] = $weekly_report;

        return view('manager.create-weekly-report',compact('data'));

    }

    public function saveWeeklyReport(Request $request){

        $validator = Validator::make($request->all(), [
            'developer' => 'required',
            'week' => 'required',
            'working_days' => 'required',
            'days_worked' => 'required',
            'client_project_time' => 'required',
            'internal_project_time' => 'required',
            'rnd_time' => 'required'
        ]);

        if ($validator->fails()) {
            return Redirect::to('manager/weekly-report')->withErrors($validator)->withInput();
        }
        $week_no = explode("--", $request->input('week'));
        // store
        $weekly_report = new WeeklyReportEntry;
        $weekly_report->user_id = $request->input('user_id');
        $weekly_report->week = $week_no[0];
        $weekly_report->start_of_week = $request->input('start_of_week');
        $weekly_report->end_of_week = $request->input('end_of_week');
        $weekly_report->total_days = $request->input('working_days');
        $weekly_report->days_worked = $request->input('days_worked');
        $weekly_report->client_time = $request->input('client_project_time');
        $weekly_report->internal_time = $request->input('internal_project_time');
        $weekly_report->rnd_time = $request->input('rnd_time');

        $weekly_report->save();

        $weekly_report_id = $weekly_report->id;
        // make an entry if the comment is added
        if ($request->input('comment')) {
            $comment = Comment::create([
                'user_id' => Auth::user()->id,
                'comment' => $request->input('comment'),
                'parent_id' => 0,
                'thread' => '',
                'status' => 1,
            ]);

            DB::table('commentables')->insert([
                'comment_id' => $comment->id,
                'commentable_id' => $weekly_report_id,
                'commentable_type' => 'weekly_report',
            ]);
        }

        Session::flash('message', 'Weekly Report saved successfully!');
        return redirect('manager/add-weekly-report');

    }

}
