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

      <div class="form-group">
        <label>Developer</label>
        <input type="text" name="developer"
               id="developer" placeholder="Enter the developer name"
               class="form-control" value="{{ $data['user_name'] }}" />
      </div>

      <div class="form-group">
        <label>Week</label>
        <input type="text" name="week"
               id="week" placeholder="Enter the week"
               class="form-control" value="{{ $data['week'] }}"/>
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
               class="form-control" value=""/>
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
        <input type="text" name="comment"
               id="comment" placeholder="Enter the comments"
               class="form-control"/>
      </div>

      <button class="btn btn-primary">Save</button>
    </form>
  </div>

  <div class="col-sm-8">
    <table class="table table-bordered table-striped">
      <thead>
      <tr>
        <th>Name</th>
        <th>Work Days</th>
        <th>Days Worked</th>
        <th>Client Project</th>
        <th>Internal Project</th>
        <th>RND Project</th>
        <th>Week Total</th>
      </tr>
      </thead>

      <tbody>

      <tr>
        <td class="col-sm-1"></td>
        <td class="col-sm-1"></td>
        <td class="col-sm-1"></td>
        <td class="col-sm-1"></td>
        <td class="col-sm-1"></td>
        <td class="col-sm-1"></td>
        <td class="col-sm-1"></td>
      </tr>

      </tbody>
    </table>

  </div>
</div>


@endsection
