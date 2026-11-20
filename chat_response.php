<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

function chat_reply($reply, $books = []) {
    echo json_encode([
        'success' => true,
        'reply' => $reply,
        'books' => $books
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    chat_reply('Please send your question using the chat box.');
}

$message = trim((string)($_POST['message'] ?? ''));
$messageLength = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);
if ($message === '' || $messageLength > 250) {
    http_response_code(400);
    chat_reply('Please enter a question (up to 250 characters).');
}

$normal = function_exists('mb_strtolower') ? mb_strtolower($message, 'UTF-8') : strtolower($message);
$normal = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normal);
$normal = preg_replace('/\s+/u', ' ', $normal);
$normal = trim($normal);

$intents = [
    'greeting' => ['hello','hi','hey','salam','assalam o alaikum','assalamualaikum','asalam o alaikum','aoa','good morning','good evening'],
    'help' => ['help','what can you do','how can you help','options','commands'],
    'download' => ['download','save book','epub'],
    'read' => ['read','online reading','open book','how to read'],
    'login' => ['login','log in','sign in','account'],
    'register' => ['register','signup','sign up','create account'],
    'favorite' => ['favorite','favourite','bookmark','save to list'],
    'review' => ['review','rating','rate book','stars'],
    'request' => ['request book','book request','suggest a book','missing book'],
    'contact' => ['contact','email','support','message admin'],
    'profile' => ['profile','my account','my history'],
    'admin' => ['admin panel','administrator','admin dashboard']
];

foreach ($intents as $intent => $phrases) {
    foreach ($phrases as $phrase) {
        $matched = ($intent === 'greeting')
            ? (($normal === 'assalam o alaikum' || preg_match('/(^|\\s)(hi|hello|hey|salam|aoa)(\\s|$)/u', $normal) === 1))
            : ($normal === $phrase || strpos($normal, $phrase) !== false);
        if ($matched) {
            switch ($intent) {
                case 'greeting':
                    chat_reply('Wa Alaikum Assalam! 👋 Welcome to the Digital Library. You can ask me to find a book, author or category, or ask how to read, download, save favorites and request books.');
                case 'help':
                    chat_reply('I can help you find books by title, author or category. I can also explain how to read or download books, create an account, save favorites, write reviews and request a book.');
                case 'download':
                    chat_reply('To download a book: open the Books page, choose a book and press its Download EPUB option. If a download link is not available, that book may only support online reading.');
                case 'read':
                    chat_reply('Open the Books page and select Read Now / Read on the book you want. Some books open on an external reading website.');
                case 'login':
                    chat_reply('Select Login from the website menu and enter your registered email/username and password. If you cannot access your account, use Forgot Password.');
                case 'register':
                    chat_reply('Select Register from the website menu, fill in your details and complete the verification steps to create an account.');
                case 'favorite':
                    chat_reply('First log in, then open a book and press the Favorite button. You can view your saved books from your Profile.');
                case 'review':
                    chat_reply('Log in, open the book details and use the rating/review section to share your feedback.');
                case 'request':
                    chat_reply('Log in and open your Profile or the Book Request area. Submit the book title and category so the library team can review your request.');
                case 'contact':
                    chat_reply('You can contact the library team through the Contact page. Add your name, email and message so the team can reply.');
                case 'profile':
                    chat_reply('After logging in, open Profile to view your account details, saved books and available reading activity.');
                case 'admin':
                    chat_reply('The Admin Panel is for authorized administrators only. It provides book, category, user, message, review and request management.');
            }
        }
    }
}

// Quick catalogue questions.
if (preg_match('/\b(how many books|total books|book count|kitni books|kitni kitab|kitni kitaab)\b/u', $normal)) {
    $countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM books");
    $countRow = $countResult ? mysqli_fetch_assoc($countResult) : ['total' => 0];
    chat_reply('There are currently ' . (int)$countRow['total'] . ' books in the library collection.');
}
if (preg_match('/\b(show all books|list books|book list|what books do you have|konsi books|kon si books|books available)\b/u', $normal)) {
    $catalogResult = mysqli_query($conn, "SELECT id, title, author, category FROM books ORDER BY id DESC LIMIT 5");
    $catalog = [];
    if ($catalogResult) {
        while ($row = mysqli_fetch_assoc($catalogResult)) {
            $catalog[] = ['id'=>(int)$row['id'], 'title'=>$row['title'], 'author'=>$row['author'], 'category'=>$row['category'] ?? ''];
        }
    }
    if ($catalog) chat_reply('Here are a few books available in the library. Open the Books page to see the full collection.', $catalog);
    chat_reply('The library collection is currently empty.');
}
if (preg_match('/\b(who are you|your name|what are you)\b/u', $normal)) {
    chat_reply('I am your Digital Library Assistant. I can search this library by book title, author and category, and guide you through common library features.');
}

$categoryTerms = [
    'programming' => ['programming','coding','computer','software','java','python','algorithm','assembly','programmer'],
    'science' => ['science','scientific','physics','chemistry','biology','astronomy'],
    'history' => ['history','historical','war','empire','civilization'],
    'novel' => ['novel','fiction','story','romance','love'],
    'horror' => ['horror','ghost','vampire','dracula','frankenstein','scary','fear'],
    'adventure' => ['adventure','journey','treasure','island','pirate','travel'],
    'detective' => ['detective','sherlock','holmes','crime','mystery'],
    'poetry' => ['poetry','poem','poems','poet'],
    'children' => ['children','kids','child','fairy tale'],
    'fantasy' => ['fantasy','magic','dragon','wonderland'],
    'islamic' => ['islamic','islam','quran','muslim'],
    'urdu' => ['urdu','shayari','ghazal']
];

$matchedCategory = '';
foreach ($categoryTerms as $category => $terms) {
    foreach ($terms as $term) {
        if (strpos($normal, $term) !== false) {
            $matchedCategory = $category;
            break 2;
        }
    }
}

$stopWords = ['the','and','for','with','from','book','books','find','show','want','need','please','give','me','about','a','an','is','are','of','in','on','to','recommend','recommendation','search','mujhe','koi','kitab','kitaab','kitabein','chahiye','batao','btao','kaise','kahan','karo','karun','dikhao','hai','ka','ki','ke','mein','ma','do'];
$tokens = preg_split('/\s+/u', $normal);
$tokens = array_values(array_unique(array_filter($tokens, function($word) use ($stopWords) {
    return (function_exists('mb_strlen') ? mb_strlen($word, 'UTF-8') : strlen($word)) >= 2 && !in_array($word, $stopWords, true);
})));

$conditions = [];
$params = [];
$types = '';
if ($matchedCategory !== '') {
    $conditions[] = 'LOWER(category) LIKE ?';
    $params[] = '%' . $matchedCategory . '%';
    $types .= 's';
}
foreach (array_slice($tokens, 0, 6) as $word) {
    $conditions[] = '(LOWER(title) LIKE ? OR LOWER(author) LIKE ? OR LOWER(category) LIKE ? OR LOWER(description) LIKE ?)';
    $like = '%' . $word . '%';
    array_push($params, $like, $like, $like, $like);
    $types .= 'ssss';
}

if (!$conditions) {
    chat_reply('Tell me a book title, author or category. For example: “Find Sherlock Holmes”, “programming books” or “novels”.');
}

$sql = 'SELECT id, title, author, category FROM books WHERE ' . implode(' OR ', $conditions) . ' ORDER BY id DESC LIMIT 5';
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    http_response_code(500);
    chat_reply('Sorry, book search is temporarily unavailable. Please try again later.');
}
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$found = [];
while ($row = mysqli_fetch_assoc($result)) {
    $found[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'author' => $row['author'],
        'category' => $row['category'] ?? ''
    ];
}
mysqli_stmt_close($stmt);

if ($found) {
    chat_reply('I found these books in your library. Select a title to open it.', $found);
}

if ($matchedCategory !== '') {
    chat_reply('I could not find a matching ' . $matchedCategory . ' book in the current collection. Try another category or a specific title/author.');
}
chat_reply('I could not find an exact match. Try a shorter title, the author name or a category such as programming, science, history, novel, horror, adventure or poetry.');
?>