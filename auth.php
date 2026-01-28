<?php
session_start();

function check_auth() {
    if (!isset($_SESSION['btg_admin_id'])) {
        header('Location: login.php');
        exit();
    }
}

function login($username, $password, $pdo) {
    // ইউজার ডাটা আনা
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // ১. প্রথমে চেক করি পাসওয়ার্ডটি কি মডার্ন সিকিউর হ্যাশ (Bcrypt)?
        // (আপনার mahadirafi ইউজার বা নতুন ইউজারদের জন্য এটি কাজ করবে)
        if (password_verify($password, $user['password'])) {
            $_SESSION['btg_admin_id'] = $user['id'];
            $_SESSION['btg_admin_user'] = $user['username'];
            return true;
        } 
        // ২. যদি না মিলে, তবে চেক করি এটি কি পুরাতন MD5 পাসওয়ার্ড?
        // (আপনার admin ইউজারের জন্য এটি কাজ করবে)
        elseif (md5($password) === $user['password']) {
            // পাসওয়ার্ড মিলেছে! কিন্তু এটি পুরাতন। 
            // সিকিউরিটির জন্য আমরা এখনই এটিকে নতুন হ্যশিং সিস্টেমে আপডেট করে দেব।
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$newHash, $user['id']]);
            
            // লগইন সেশন সেট করা
            $_SESSION['btg_admin_id'] = $user['id'];
            $_SESSION['btg_admin_user'] = $user['username'];
            return true;
        }
    }
    
    // কোনোটিই না মিললে লগইন ফেইল্ড
    return false;
}

function logout() {
    session_destroy();
    header('Location: login.php');
        exit();
    }
    
    // CSRF Protection Functions
    function generate_csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    function verify_csrf_token($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            die("Security Check Failed (CSRF). Please refresh the page and try again.");
        }
    }
    ?>