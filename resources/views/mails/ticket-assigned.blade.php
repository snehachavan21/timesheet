<table width="95%" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td bgcolor="#F0F0F0" style="padding: 20px;" align="left">
      <h1><a href="{{ url('spa/time-tracker-report#/ticket/view/' . $ticket->id) }}">#{{ $ticket->id }} - {{$ticket->title}}</a></h1>
      <p>Created on: {{$ticket->created_at}}</p>
      <p>Created by: {{$ticket->created_by->name}}</p>
      <p>Assigned to: {{$ticket->users[0]->name}}</p>
      <p>Type: {{ucfirst($ticket->type)}}</p>
      <p>Complete date: {{$ticket->complete_date}}</p>
      <p>Project: {{$ticket->project[0]->name}}</p>
      <p>Description: {!!$ticket->comment!!}</p>
    </td>
  </tr>
</table>
