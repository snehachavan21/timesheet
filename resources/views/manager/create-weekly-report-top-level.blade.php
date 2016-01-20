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

  <div class="col-sm-12">
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
