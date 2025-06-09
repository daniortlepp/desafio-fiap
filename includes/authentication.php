<?php
session_start();

error_reporting(0);
ini_set('display_errors', 0);

function protegerPagina() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../admin/login.php");
        exit();
    }
}

function fazerLogin($email, $senha) {
    global $pdo;
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($senha, $admin['password'])) {
        session_regenerate_id();
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        return true;
    }
    return false;
}
?>