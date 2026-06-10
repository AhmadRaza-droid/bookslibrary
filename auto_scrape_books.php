<?php
session_start();
include 'config.php';

if(!isset($_SESSION['admin'])){
    echo "<script>
            alert('Access Denied');
            window.location.href='admin_login.php';
          </script>";
    exit();
}

$url = "https://gutendex.com/books/?languages=en";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");

$response = curl_exec($ch);
$error = curl_error($ch);

curl_close($ch);

if(!$response){
    echo "<script>
            alert('Books import failed. API not responding. $error');
            window.location.href='admin_dashboard.php';
          </script>";
    exit();
}

$data = json_decode($response, true);

if(!isset($data['results'])){
    echo "<script>
            alert('Invalid API response');
            window.location.href='admin_dashboard.php';
          </script>";
    exit();
}

$count = 0;

foreach($data['results'] as $book){

    $title = mysqli_real_escape_string($conn, $book['title']);

    $author = "Unknown";
    if(!empty($book['authors'])){
        $author = mysqli_real_escape_string($conn, $book['authors'][0]['name']);
    }

    $description = "Free public domain book from Project Gutenberg.";

    $cover = "";
    if(isset($book['formats']['image/jpeg'])){
        $cover = mysqli_real_escape_string($conn, $book['formats']['image/jpeg']);
    }

    $read_link = "";
    if(isset($book['formats']['text/html'])){
        $read_link = mysqli_real_escape_string($conn, $book['formats']['text/html']);
    }

    $download_link = "";
    if(isset($book['formats']['application/epub+zip'])){
        $download_link = mysqli_real_escape_string($conn, $book['formats']['application/epub+zip']);
    }

    $check = mysqli_query($conn,
        "SELECT * FROM books 
         WHERE title='$title' 
         AND author='$author'"
    );

    if(mysqli_num_rows($check) == 0){

        $query = "INSERT INTO books
        (title, author, description, cover_image_url, read_link, download_epub_link, source)
        VALUES
        ('$title', '$author', '$description', '$cover', '$read_link', '$download_link', 'Gutendex')";

        if(mysqli_query($conn, $query)){
            $count++;
        }
    }
}

echo "<script>
        alert('$count books imported successfully');
        window.location.href='admin_dashboard.php';
      </script>";
?>