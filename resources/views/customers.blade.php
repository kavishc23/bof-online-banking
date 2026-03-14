<!DOCTYPE html>
<html>
<head>
<title>Customers</title>
</head>

<body>

<h1>Customers</h1>

<table border="1">

<tr>
<th>ID</th>
<th>First Name</th>
<th>Last Name</th>
<th>Email</th>
</tr>

@foreach($customers as $customer)

<tr>
<td>{{ $customer['id'] }}</td>
<td>{{ $customer['firstName'] ?? '' }}</td>
<td>{{ $customer['lastName'] ?? '' }}</td>
<td>{{ $customer['email'] ?? '' }}</td>
</tr>

@endforeach

</table>

</body>
</html>