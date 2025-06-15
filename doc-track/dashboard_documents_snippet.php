<?php
$stmt = $pdo->prepare("SELECT * FROM documents WHERE user_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$docs = $stmt->fetchAll();
?>

<h2>📁 Mes documents médicaux</h2>
<p><a href="upload.php">➕ Ajouter un nouveau document</a></p>
<ul>
    <?php foreach ($docs as $doc): ?>
        <li>
            <?= htmlspecialchars($doc['title']) ?> –
            <a href="uploads/<?= urlencode($doc['file_path']) ?>" target="_blank">📄 Télécharger</a>
        </li>
    <?php endforeach; ?>
</ul>