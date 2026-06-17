<?php
// ========== INPUT SANITIZATION ==========
function sanitize($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// ========== XSS PROTECTION ==========
function escape($data){
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// ========== VALIDATE EMAIL ==========
function validate_email($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ========== VALIDATE PASSWORD STRENGTH ==========
function validate_password($password){
    if(strlen($password) < 8) return false;
    if(!preg_match("/[A-Z]/", $password)) return false;
    if(!preg_match("/[a-z]/", $password)) return false;
    if(!preg_match("/[0-9]/", $password)) return false;
    return true;
}

// ========== GENERATE CSRF TOKEN ==========
function generate_csrf_token(){
    if(empty($_SESSION['csrf_token'])){
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// ========== VERIFY CSRF TOKEN ==========
function verify_csrf_token($token){
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ========== RATE LIMITING ==========
function check_rate_limit($key, $limit = 10, $time = 60){
    $file = sys_get_temp_dir() . '/rate_limit_' . md5($key);
    $now = time();
    
    if(file_exists($file)){
        $data = json_decode(file_get_contents($file), true);
        if($data['time'] + $time > $now){
            if($data['count'] >= $limit){
                return false;
            }
            $data['count']++;
        } else {
            $data['count'] = 1;
            $data['time'] = $now;
        }
    } else {
        $data = ['count' => 1, 'time' => $now];
    }
    
    file_put_contents($file, json_encode($data));
    return true;
}

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
?>