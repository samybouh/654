<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 1) {
    header("Location: login.php");
    exit;
}

$doctorId = $_SESSION['user_id'];

// 🔁 Répondre à un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['message'])) {
    $receiverId = (int)$_POST['receiver_id'];
    $msg = trim($_POST['message']);

    // Vérifier si ce patient a bien ce médecin
    $check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND doctor_id = ?");
    $check->execute([$receiverId, $doctorId]);
    if ($check->fetch()) {
        $insert = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $insert->execute([$doctorId, $receiverId, $msg]);
    }
}

// Liste des patients associés au médecin
$stmt = $pdo->prepare("SELECT * FROM users WHERE type = 0 AND doctor_id = ? ORDER BY email");
$stmt->execute([$doctorId]);
$patients = $stmt->fetchAll();

// Tous les messages avec ses patients
$stmt = $pdo->prepare("
    SELECT m.*, u1.email AS sender_email, u2.email AS receiver_email
    FROM messages m
    JOIN users u1 ON m.sender_id = u1.id
    JOIN users u2 ON m.receiver_id = u2.id
    WHERE (m.sender_id = :docId OR m.receiver_id = :docId)
      AND (u1.doctor_id = :docId OR u2.doctor_id = :docId)
    ORDER BY m.sent_at DESC
");
$stmt->execute(['docId' => $doctorId]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Médecin</title>
    <style>
        body { font-family: Arial; padding: 30px; background-color: #f4f4f4; }
        h2 { margin-bottom: 20px; }
        .block { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background: #007bff; color: white; }
        textarea { width: 100%; border-radius: 6px; padding: 8px; }
        .logout {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<h2>🩺 Espace Médecin - Doc Track</h2>

<div class="block">
    <h3>👥 Liste de mes patients</h3>
    <table>
        <tr><th>Email</th><th>Téléphone</th><th>Adresse</th><th>Numéro SS</th></tr>
        <?php foreach ($patients as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['phone']) ?></td>
                <td><?= htmlspecialchars($p['address']) ?></td>
                <td><?= htmlspecialchars($p['ssn']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="block">
    <h3>📨 Messages avec mes patients</h3>
    <?php foreach ($messages as $m): ?>
        <div style="margin-bottom: 15px;">
            <p><strong><?= htmlspecialchars($m['sender_email']) ?></strong> → <strong><?= htmlspecialchars($m['receiver_email']) ?></strong><br>
            <?= nl2br(htmlspecialchars($m['message'])) ?><br>
            <small>🕒 <?= date('d/m/Y H:i', strtotime($m['sent_at'])) ?></small></p>

            <?php if ((int)$m['receiver_id'] === $doctorId): ?>
                <form method="POST" style="margin-top:10px;">
                    <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($m['sender_id']) ?>">
                    <textarea name="message" rows="2" placeholder="Répondre à ce message..." required></textarea>
                    <button type="submit" style="margin-top:5px;">Envoyer</button>
                </form>
            <?php endif; ?>

            <hr>
        </div>
    <?php endforeach; ?>
</div>

<a href="logout.php" class="logout">🚪 Se déconnecter</a>

</body>
</html>
