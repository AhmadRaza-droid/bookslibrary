<?php

$conn = mysqli_connect("localhost","root","","library_db","3307");

if(!$conn){
    die("Connection Failed");
}

?>
<?php

$conn = mysqli_connect("localhost", "root", "", "library_db", 3307);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

?>