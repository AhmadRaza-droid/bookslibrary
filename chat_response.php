<?php
include 'config.php';

$message = strtolower(trim($_POST['message']));

if(
strpos($message,'programming') !== false ||
strpos($message,'coding') !== false
){
    $result = mysqli_query($conn,
    "SELECT title,author FROM books
     WHERE category LIKE '%programming%'
     LIMIT 5");
}
elseif(
strpos($message,'islamic') !== false ||
strpos($message,'islam') !== false
){
    $result = mysqli_query($conn,
    "SELECT title,author FROM books
     WHERE category LIKE '%islamic%'
     LIMIT 5");
}
elseif(
strpos($message,'urdu') !== false
){
    $result = mysqli_query($conn,
    "SELECT title,author FROM books
     WHERE category LIKE '%urdu%'
     LIMIT 5");
}
elseif(
strpos($message,'novel') !== false
){
    $result = mysqli_query($conn,
    "SELECT title,author FROM books
     WHERE category LIKE '%novel%'
     LIMIT 5");
}
else{
    $result = mysqli_query($conn,
    "SELECT title,author FROM books
     WHERE title LIKE '%$message%'
     OR author LIKE '%$message%'
     OR category LIKE '%$message%'
     LIMIT 5");
}

if(mysqli_num_rows($result) > 0){

    $reply = "📚 Recommended Books:<br><br>";

    while($row = mysqli_fetch_assoc($result)){

        $reply .= "• ".$row['title'].
                  " - ".$row['author'].
                  "<br>";
    }

    echo $reply;
}
else{

    echo "🤖 Sorry, I couldn't find any matching books. Try another category like Programming, Islamic, Urdu, Novel, Science or History.";
}
?>