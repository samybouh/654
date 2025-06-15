<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Uploader un document - Doc Track</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f1f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 100px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: #007bff;
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Uploader un document</h2>

    <form action="upload_process.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Titre du document" required>
        <input type="file" name="document" accept=".pdf,.png,.jpg,.jpeg" required>
        <button type="submit">Envoyer</button>
    </form>

    <a class="back" href="dashboard.php">⬅ Retour au tableau de bord</a>
</div>

</body>
</html>
