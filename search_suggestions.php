<?php
include 'config.php';

$q = mysqli_real_escape_string($conn, $_GET['q'] ?? '');

$result = mysqli_query($conn,
"SELECT title FROM books
 WHERE title LIKE '%$q%'
 OR author LIKE '%$q%'
 OR category LIKE '%$q%'
 LIMIT 5");

while($row = mysqli_fetch_assoc($result)){
    $title = htmlspecialchars($row['title'], ENT_QUOTES);

    echo "<div style='padding:10px;cursor:pointer;border-bottom:1px solid #eee;color:#061b33;'
          onclick=\"selectSuggestion('$title')\">
          $title
          </div>";
}
?>