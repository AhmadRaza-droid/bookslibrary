<?php
include 'config.php';

$books = [
    [
        "Pride and Prejudice",
        "Jane Austen",
        "Classic",
        "https://www.gutenberg.org/cache/epub/1342/pg1342.cover.medium.jpg",
        "https://www.gutenberg.org/files/1342/1342-h/1342-h.htm",
        "A classic romantic novel by Jane Austen."
    ],
    [
        "Alice's Adventures in Wonderland",
        "Lewis Carroll",
        "Fantasy",
        "https://www.gutenberg.org/cache/epub/11/pg11.cover.medium.jpg",
        "https://www.gutenberg.org/files/11/11-h/11-h.htm",
        "A fantasy story about Alice and Wonderland."
    ],
    [
        "Frankenstein",
        "Mary Shelley",
        "Horror",
        "https://www.gutenberg.org/cache/epub/84/pg84.cover.medium.jpg",
        "https://www.gutenberg.org/files/84/84-h/84-h.htm",
        "A famous gothic science fiction novel."
    ]
];

foreach($books as $b){
    mysqli_query($conn,
    "INSERT INTO books(book_name, author_name, category, quantity, cover_url, download_url, description)
     VALUES('$b[0]', '$b[1]', '$b[2]', 10, '$b[3]', '$b[4]', '$b[5]')");
}

echo "Free books imported successfully";
?>