<?php

require_once 'db.php';

// Initialisation des variables
$success = false;
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $ssn = trim($_POST['ssn']);

    // Vérifie si l'utilisateur existe déjà
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $error = "Un compte avec cette adresse e-mail existe déjà. <a href='login.php'>Se connecter</a>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, phone, address, ssn) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$email, $password, $phone, $address, $ssn])) {
            $success = true;
            header("refresh:2;url=dashboard_patient.php");
        } else {
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte - Doc Track</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f1f4f9;
            margin: 0;
            padding: 0;
        }

        .register-container {
            max-width: 420px;
            margin: 100px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .register-container h2 {
            margin-bottom: 24px;
            color: #333;
        }

        .register-container input {
            width: 90%;
            padding: 14px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        .register-container button {
            width: 95%;
            padding: 14px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 12px;
        }

        .register-container button:hover {
            background-color: #218838;
        }

        .success {
            color: green;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Créer un compte</h2>

        <?php if ($success): ?>
            <p class="success">Inscription réussie ! Redirection en cours...</p>
        <?php elseif (!empty($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="text" name="phone" placeholder="Téléphone" required>
            <input type="text" name="address" placeholder="Adresse postale" required>
            <input type="text" name="ssn" placeholder="Numéro de sécurité sociale" required>
            <button type="submit">Créer mon compte</button>
        </form>
    </div>
</body>
</html>
