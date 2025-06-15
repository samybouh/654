<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Infos utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) exit("Utilisateur introuvable.");

// Récupération du médecin associé
$doctorId = $user['doctor_id'] ?? null;
$doctor = null;
if ($doctorId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Gestion envoi message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $doctorId) {
    $msg = trim($_POST['message']);
    if (!empty($msg)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $doctorId, $msg]);
    }
}

// Historique des messages
$messages = [];
if ($doctorId) {
    $stmt = $pdo->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY sent_at ASC
    ");
    $stmt->execute([$userId, $doctorId, $doctorId, $userId]);
    $messages = $stmt->fetchAll();
}

// Documents
$stmt = $pdo->prepare("SELECT * FROM documents WHERE user_id = ?");
$stmt->execute([$userId]);
$docs = $stmt->fetchAll();

// Traitements
$stmt = $pdo->prepare("SELECT * FROM treatments WHERE user_id = ?");
$stmt->execute([$userId]);
$treatments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Doc Track</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0; background-color: #f1f4f9;
        }
        .sidebar {
            width: 220px; height: 100vh; position: fixed;
            background-color: #007bff; color: white; padding: 20px;
        }
        .sidebar h3 { text-align: center; margin-bottom: 30px; }
        .sidebar a {
            display: block; padding: 10px 15px; color: white;
            text-decoration: none; margin-bottom: 10px;
            border-radius: 5px;
        }
        .sidebar a:hover { background-color: #0056b3; }
        .main-content {
            margin-left: 240px; padding: 30px;
        }
        h2 { color: #333; margin-bottom: 20px; }
        .section { display: none; }
        .section.active { display: block; }

        .message-box {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            max-width: 70%;
            clear: both;
        }
        .you { background-color: #d1ecf1; float: right; }
        .them { background-color: #f8d7da; float: left; }
        .clearfix::after { content: ""; display: table; clear: both; }
    </style>
    <script>
        function showSection(id) {
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.getElementById(id).classList.add('active');
        }
        window.onload = () => showSection('profil');
    </script>
</head>
<body>

<div class="sidebar">
    <h3>Doc Track</h3>
    <a href="#" onclick="showSection('profil')">👤 Mon profil</a>
    <a href="#" onclick="showSection('documents')">📁 Mes documents</a>
    <a href="#" onclick="showSection('traitements')">💊 Traitements</a>
    <a href="#" onclick="showSection('messagerie')">✉️ Messagerie</a>
    <a href="#" onclick="showSection('forum')">🌐 Forum</a>
    <a href="logout.php">🚪 Se déconnecter</a>
</div>

<div class="main-content">
    <div id="profil" class="section">
        <h2>👤 Mon profil</h2>
        <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Téléphone :</strong> <?= htmlspecialchars($user['phone']) ?></p>
        <p><strong>Adresse :</strong> <?= htmlspecialchars($user['address']) ?></p>
        <p><strong>Numéro de sécu :</strong> <?= htmlspecialchars($user['ssn']) ?></p>
        <p><strong>Médecin :</strong> <?= $doctor ? htmlspecialchars($doctor['email']) : "Aucun" ?></p>
    </div>

    <div id="documents" class="section">
        <h2>📁 Mes documents médicaux</h2>
        <a href="upload.php">➕ Ajouter un document</a>
        <ul>
            <?php foreach ($docs as $doc): ?>
                <li>
                    <?= htmlspecialchars($doc['title']) ?> –
                    <a href="uploads/<?= urlencode($doc['file_path']) ?>" target="_blank">📄 Télécharger</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div id="traitements" class="section">
        <h2>💊 Mes traitements</h2>
        <ul>
            <?php foreach ($treatments as $t): ?>
                <li>
                    <?= htmlspecialchars($t['name']) ?> – <?= htmlspecialchars($t['schedule']) ?>
                    <?php if ($t['reminder_time']): ?> ⏰ à <?= htmlspecialchars($t['reminder_time']) ?><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div id="messagerie" class="section">
        <h2>✉️ Messagerie</h2>
        <?php if ($doctor): ?>
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                <?php foreach ($messages as $m): ?>
                    <div class="message-box <?= $m['sender_id'] == $userId ? 'you' : 'them' ?> clearfix">
                        <?= htmlspecialchars($m['message']) ?>
                        <div style="font-size: 12px; color: #555;">
                            <?= $m['sent_at'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="POST">
                <textarea name="message" rows="4" style="width:100%; border-radius:8px;" required placeholder="Écrire un message..."></textarea>
                <button type="submit" style="margin-top:10px;">Envoyer</button>
            </form>
        <?php else: ?>
            <p>Aucun médecin n’est associé à votre compte.</p>
        <?php endif; ?>
    </div>

    <div id="forum" class="section">
        <h2>🌐 Forum</h2>
        <p><a href="#">Accéder au forum Doc Track</a></p>
    </div>
</div>
</body>
</html>
