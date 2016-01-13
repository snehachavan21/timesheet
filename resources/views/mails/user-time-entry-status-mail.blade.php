<table align="center" cellpadding="2" cellspacing="0" border="1">
  <thead>
  <tr>
    <th>Employee Id</th>
    <th>Name</th>
    <th>Email</th>
  </tr>

  </thead>

  <tbody>
  @foreach($userData as $user)
  <tr>
    <td>{{$user->employee_id}}</td>
    <td>{{$user->name}}</td>
    <td>{{$user->email}}</td>
  </tr>
    @endforeach
  </tbody>

</table>
