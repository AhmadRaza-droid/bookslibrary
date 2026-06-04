<?php
include 'config.php';

$result = mysqli_query($conn,"SELECT * FROM messages");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1 style="text-align:center;">User Messages</h1>

<table border="1" width="100%" cellpadding="10">

<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Message</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['message']; ?></td>

</tr>

<?php } ?>

</table>

</body>
</html>