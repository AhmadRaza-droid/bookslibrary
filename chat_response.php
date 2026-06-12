<?php
include 'config.php';

$message = strtolower(trim($_POST['message'] ?? ''));

$result = mysqli_query($conn,
"SELECT * FROM books
WHERE LOWER(title) LIKE '%$message%'
OR LOWER(author) LIKE '%$message%'
OR LOWER(category) LIKE '%$message%'
LIMIT 5");

if(mysqli_num_rows($result) > 0){

    echo "📚 Recommended Books:<br><br>";

    while($book = mysqli_fetch_assoc($result)){

        echo "✅ ".$book['title']."<br>";
        echo "👤 ".$book['author']."<br><br>";
    }

}
else{

    if(strpos($message,"programming") !== false){
        echo "💻 Search Programming category in Books page.";
    }
    elseif(strpos($message,"assembly") !== false){
        echo "⚙ Try Computer Organization and Assembly Language books.";
    }
    elseif(strpos($message,"novel") !== false){
        echo "📖 Try Novel category books.";
    }
    elseif(strpos($message,"history") !== false){
        echo "🏛 Try History category books.";
    }
    else{
        echo "❌ No matching books found.";
    }
}
?>