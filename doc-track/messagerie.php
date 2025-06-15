<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$medecinId = 1; // ID fixe pour le médecin (ex: Dr Admin)

// Envoi de message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $medecinId, $msg]);
}

// Récupérer tous les messages entre le patient et le médecin
$stmt = $pdo->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) 
       OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY sent_at ASC
");
$stmt->execute([$userId, $medecinId, $medecinId, $userId]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie - Doc Track</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f1f4f9;
            padding: 20px;
        }
        .chat-box {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .message {
            margin: 10px 0;
            padding: 12px;
            border-radius: 8px;
            max-width: 80%;
        }
        .me {
            background-color: #dcf8c6;
            align-self: flex-end;
            text-align: right;
        }
        .other {
            background-color: #e4e6eb;
        }
        .messages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 400px;
            overflow-y: auto;
        }
        form {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        input[type="text"] {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .back {
            display: block;
            margin: 20px auto;
            text-align: center;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="chat-box">
        <h2>💬 Messagerie avec mon médecin</h2>

        <div class="messages">
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['sender_id'] === $userId ? 'me' : 'other' ?>">
                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    <br><small><?= date('d/m/Y H:i', strtotime($msg['sent_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="POST">
            <input type="text" name="message" placeholder="Écrire un message..." required>
            <button type="submit">Envoyer</button>
        </form>

        <a class="back" href="dashboard.php">⬅ Retour au tableau de bord</a>
    </div>
</body>
</html>
