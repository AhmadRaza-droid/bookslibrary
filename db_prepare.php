<?php
// ========== PREPARED STATEMENT HELPER ==========
function db_query($conn, $sql, $types = "", $params = []){
    $stmt = $conn->prepare($sql);
    if(!$stmt){
        return false;
    }
    if(!empty($params)){
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// ========== EXAMPLE ==========
// $result = db_query($conn, "SELECT * FROM users WHERE email = ?", "s", [$email]);
// $user = mysqli_fetch_assoc($result);
?>