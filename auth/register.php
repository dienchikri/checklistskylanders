<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    header('Location: ../views/home.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username && $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        try {
            $stmt->execute([$username, $hashedPassword]);
            $success = true;
        } catch (PDOException $e) {
            $error = "Username already exists.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Skylanders Vault</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#00bcd4">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/logoimg/collectionvault.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/assets/logoimg/collectionvault.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/assets/logoimg/collectionvault.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/assets/logoimg/collectionvault.png">
    <link rel="icon" type="image/png" href="/assets/logoimg/collectionvault.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root{
            --brand:#00bcd4;
            --card-max:460px;
        }
        html,body{ height:100%; }
        body{
            margin:0;
            font-size:16px;
            background: linear-gradient(135deg, #7ec8e3, #4fa3d1);
            min-height: 100svh;
            display: grid;
            place-items: center;
            padding: 16px;
            padding-left: max(16px, env(safe-area-inset-left));
            padding-right: max(16px, env(safe-area-inset-right));
            padding-top: max(16px, env(safe-area-inset-top));
            padding-bottom: max(16px, env(safe-area-inset-bottom));
        }
        .auth-card{
            width: min(100%, var(--card-max));
            background: #ffffffee;
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(0,0,0,.15);
            padding: 24px;
        }
        .auth-card h3{ color:#0078b7; margin-bottom:.25rem; }
        .brand-logo{ width:80px; height:auto; }
        .form-label{ font-weight:600; }
        .form-control{ border-radius: 10px; }
        .form-control-lg{ padding:.9rem 1rem; }
        .btn-lg{ padding:.85rem 1rem; border-radius: 12px; }
        .link{ text-decoration:none; }
        .link:hover{ text-decoration:underline; }

        @media (max-width: 360px){
            .auth-card{ padding: 18px; }
        }


    </style>
</head>
<body>

<div class="auth-card">
    <div class="text-center mb-3">
        <img src="../assets/logoimg/collectionvault.png" alt="Logo" class="brand-logo">
        <h3 class="mt-2">Skylanders Vault</h3>
        <p class="text-muted m-0">Create your account</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success text-center">
            Registration successful! <a href="login.php" class="link">Log in here</a>.
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="username" class="form-label">
                <i class="bi bi-person-fill"></i> Username
            </label>
            <input type="text" name="username" id="username" class="form-control form-control-lg" required autocomplete="username" inputmode="text">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="bi bi-lock-fill"></i> Password
            </label>
            <input type="password" name="password" id="password" class="form-control form-control-lg" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">
            <i class="bi bi-person-plus-fill"></i> Register
        </button>
    </form>

    <p class="text-center mt-3 mb-0">
        Already have an account?
        <a href="login.php" class="link">Log in here</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
