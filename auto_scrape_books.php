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

$books = [
    [
        "title" => "Pride and Prejudice",
        "author" => "Jane Austen",
        "description" => "A classic novel about manners, love, and society.",
        "cover" => "https://www.gutenberg.org/cache/epub/1342/pg1342.cover.medium.jpg",
        "read" => "1342",
        "download" => "https://www.gutenberg.org/ebooks/1342.epub3.images"
    ],
    [
        "title" => "Moby Dick",
        "author" => "Herman Melville",
        "description" => "A classic adventure novel about Captain Ahab and the white whale.",
        "cover" => "https://www.gutenberg.org/cache/epub/2701/pg2701.cover.medium.jpg",
        "read" => "2701",
        "download" => "https://www.gutenberg.org/ebooks/2701.epub3.images"
    ],
    [
        "title" => "The Adventures of Sherlock Holmes",
        "author" => "Arthur Conan Doyle",
        "description" => "A collection of detective stories featuring Sherlock Holmes.",
        "cover" => "https://www.gutenberg.org/cache/epub/1661/pg1661.cover.medium.jpg",
        "read" => "1661",
        "download" => "https://www.gutenberg.org/ebooks/1661.epub3.images"
    ],
    [
        "title" => "Frankenstein",
        "author" => "Mary Shelley",
        "description" => "A famous gothic novel about science, creation, and responsibility.",
        "cover" => "https://www.gutenberg.org/cache/epub/84/pg84.cover.medium.jpg",
        "read" => "84",
        "download" => "https://www.gutenberg.org/ebooks/84.epub3.images"
    ],
    [
        "title" => "Alice's Adventures in Wonderland",
        "author" => "Lewis Carroll",
        "description" => "A fantasy story about Alice and her adventures in Wonderland.",
        "cover" => "https://www.gutenberg.org/cache/epub/11/pg11.cover.medium.jpg",
        "read" => "11",
        "download" => "https://www.gutenberg.org/ebooks/11.epub3.images"
    ]
];

$count = 0;

foreach($books as $book){

    $title = mysqli_real_escape_string($conn, $book['title']);
    $author = mysqli_real_escape_string($conn, $book['author']);
    $description = mysqli_real_escape_string($conn, $book['description']);
    $cover = mysqli_real_escape_string($conn, $book['cover']);
    $read_link = mysqli_real_escape_string($conn, $book['read']);
    $download_link = mysqli_real_escape_string($conn, $book['download']);

    $check = mysqli_query($conn, "SELECT * FROM books WHERE title='$title' AND author='$author'");

    if(mysqli_num_rows($check) == 0){

        $query = "INSERT INTO books
        (title, author, description, cover_image_url, read_link, download_epub_link, source)
        VALUES
        ('$title', '$author', '$description', '$cover', '$read_link', '$download_link', 'Project Gutenberg')";

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