<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_FILES['document']) && isset($_POST['title'])) {
    $userId = $_SESSION['user_id'];
    $title = htmlspecialchars($_POST['title']);
    $file = $_FILES['document'];
    $uploadDir = 'uploads/';
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO documents (user_id, title, file_path, uploaded_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $title, $fileName]);
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Erreur lors de l'upload.";
    }
}
?>