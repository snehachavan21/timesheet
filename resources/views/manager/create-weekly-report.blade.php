@extends('layouts.master')

@section('title', 'Create Weekly Report')

@section('content')

<div class="row">
  <div class="col-sm-12">
    <h1>Add Weekly Report</h1>

    @if ($errors->has())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
        {{ $error }}<br>
        @endforeach
    </div>
    @endif
  </div>
</div>

<div class="row">
  <div class="col-sm-4">
    <form action="{{ url('manager/save-weekly-report') }}" method="POST" name="weeklyReportForm">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="user_id" id="user_id" value="{{ $data['user_id'] }}">
      <input type="hidden" name="start_of_week" id="start_of_week" value="{{ $data['start_of_week'] }}">
      <input type="hidden" name="end_of_week" id="end_of_week" value="{{ $data['end_of_week'] }}">

      <div class="form-group">
        <label>Developer</label>
        <input type="text" name="developer"
               id="developer" placeholder="Enter the developer name"
               class="form-control" value="{{ $data['user_name'] }}" readonly/>
      </div>

      <div class="form-group">
        <label>Week</label>
        <input type="text" name="week"
               id="week" placeholder="Enter the week"
               class="form-control" value="{{ $data['week']   }} -- {{ $data['start'] }} - {{ $data['end'] }}" readonly/>
      </div>

      <div class="form-group">
        <label>Working Days</label>
        <input type="text" name="working_days"
               id="working_days" placeholder="Enter the total working days"
               class="form-control" value=""/>
      </div>

      <div class="form-group">
        <label>Days Worked</label>
        <input type="text" name="days_worked"
               id="days_worked" placeholder="Enter the days worked"
               class="form-control" value="{{ $data['days_worked'] }}" readonly/>
      </div>

      <div class="form-group">
        <label>Client Project</label>
        <input type="text" name="client_project_time"
               id="client_project_time" placeholder="Enter the time for client projects"
               class="form-control" value="" />
      </div>


      <div class="form-group">
        <label>Internal Project</label>
        <input type="text" name="internal_project_time"
               id="internal_project_time" placeholder="Enter the time for internal projects"
               class="form-control"/>
      </div>

      <div class="form-group">
        <label>RnD</label>
        <input type="text" name="rnd_time"
               id="rnd_time" placeholder="Enter the time for Rnd"
               class="form-control"/>
      </div>

      <div class="form-group">
        <label>Comment</label>
        <textarea rows="4" cols="50" name="comment" id="comment" placeholder="Enter the comments" class="form-control"/>
        </textarea>
      </div>

      <button class="btn btn-primary">Save</button>
    </form>
  </div>

  <div class="col-sm-8">
    <table class="table table-bordered table-striped">
      <thead>
      <tr>
        <th>Name</th>
        <th>Week</th>
        <th>Work Days</th>
        <th>Days Worked</th>
        <th>Client Project</th>
        <th>Internal Project</th>
        <th>RND Project</th>
        <th>Week Total</th>
      </tr>
      </thead>
      <?php ?>
      <tbody>
      @foreach ($data['weekly_report'] as $key => $value)
      <?php $total_time = $value['client_time'] + $value['internal_time']+ $value['rnd_time'];

            $start_of_week = date("M d", strtotime($value['start_of_week']));
            $end_of_week = date("M d", strtotime($value['end_of_week']));
      ?>
      <tr>
        <td class="col-sm-1">{!! $data['user_name'] !!}</td>
        <td class="col-sm-1">{!! $data['week'] !!} -- {!! $start_of_week !!} - {!! $end_of_week !!}</td>
        <td class="col-sm-1">{!! $value['total_days'] !!}</td>
        <td class="col-sm-1">{!! $value['days_worked'] !!}</td>
        <td class="col-sm-1">{!! $value['client_time'] !!}</td>
        <td class="col-sm-1">{!! $value['internal_time'] !!}</td>
        <td class="col-sm-1">{!! $value['rnd_time'] !!}</td>
        <td class="col-sm-1">{!! $total_time !!}</td>
      </tr>
      @endforeach
      </tbody>
    </table>
    {!! $data['weekly_report']->render() !!}
  </div>
</div>


@endsection
