<?php
session_start();
require_once 'database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uname = trim($_POST['username'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if ($uname === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        $_SESSION['role']      = 'admin';
        $_SESSION['campus']    = 'ADMIN';
        $_SESSION['username']  = 'Administrator';
        header("Location: admin_dashboard.php");
        exit;
    }

    $campuses = CAMPUS_USERS;
    if (isset($campuses[$uname]) && $campuses[$uname] === $pass) {
        $_SESSION['logged_in'] = true;
        $_SESSION['role']      = 'user';
        $_SESSION['campus']    = $uname;
        $_SESSION['username']  = $uname;
        header("Location: index.php");
        exit;
    }

    $error = 'Invalid username or password. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UBConnect — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Raleway:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --teal: #1a6b70;
    --teal-light: #2d8a8f;
    --teal-dark: #0e4547;
    --gold: #c9a84c;
    --white: #f5f9f9;
    --glass: rgba(255,255,255,0.12);
    --glass-border: rgba(255,255,255,0.25);
}

body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Raleway', sans-serif;
    background: url('background.jpg') center/cover no-repeat fixed;
    position: relative;
}

body::before {
    content: '';
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg, rgba(10,40,42,0.82) 0%, rgba(26,107,112,0.65) 100%);
    backdrop-filter: blur(2px);
    z-index: 0;
}

.login-wrap {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 440px;
    padding: 20px;
}

.login-card {
    background: var(--glass);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    padding: 48px 40px 40px;
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    box-shadow: 0 24px 60px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
    animation: fadeUp 0.6s ease forwards;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}

.logo-area {
    text-align: center;
    margin-bottom: 36px;
}

.fish-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 14px;
    display: block;
}

.logo-area h1 {
    font-family: 'Cinzel', serif;
    font-size: 1.5rem;
    color: #fff;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.logo-area p {
    color: rgba(255,255,255,0.6);
    font-size: 0.78rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-top: 6px;
}

.divider {
    width: 40px;
    height: 2px;
    background: var(--gold);
    margin: 12px auto 0;
    border-radius: 2px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    color: rgba(255,255,255,0.75);
    font-size: 0.75rem;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
    font-weight: 600;
}

input[type="text"],
input[type="password"] {
    width: 100%;
    padding: 13px 16px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px;
    color: #fff;
    font-family: 'Raleway', sans-serif;
    font-size: 0.95rem;
    transition: all 0.25s;
    outline: none;
}

input::placeholder { color: rgba(255,255,255,0.35); }

input:focus {
    border-color: var(--gold);
    background: rgba(255,255,255,0.15);
    box-shadow: 0 0 0 3px rgba(201,168,76,0.2);
}

.btn-login {
    width: 100%;
    padding: 14px;
    margin-top: 8px;
    background: linear-gradient(135deg, var(--teal-light), var(--teal-dark));
    border: none;
    border-radius: 10px;
    color: #fff;
    font-family: 'Cinzel', serif;
    font-size: 1rem;
    letter-spacing: 2px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 20px rgba(26,107,112,0.5);
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(26,107,112,0.6);
}

.error-msg {
    background: rgba(220,60,60,0.2);
    border: 1px solid rgba(220,60,60,0.4);
    color: #ffa0a0;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 0.85rem;
    margin-bottom: 16px;
    text-align: center;
}

.hint {
    text-align: center;
    color: rgba(255,255,255,0.4);
    font-size: 0.75rem;
    margin-top: 20px;
    letter-spacing: 0.5px;
}

.admin-hint {
    text-align: center;
    margin-top: 14px;
    color: rgba(255,255,255,0.35);
    font-size: 0.72rem;
}
</style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="logo-area">
            <svg class="fish-icon" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Flame body -->
                <path d="M40 6 C36 16 28 18 30 32 C24 24 20 28 23 38 C17 30 19 40 25 46 C20 46 16 50 21 56 C27 64 38 68 50 62 C62 56 66 44 60 34 C55 24 49 28 47 18 C45 10 42 8 40 6Z" fill="rgba(201,168,76,0.9)"/>
                <!-- Inner dove silhouette -->
                <path d="M36 34 C31 30 25 33 27 39 C29 44 35 42 37 46 C39 50 37 54 39 57 C41 59 46 56 45 51 C44 46 40 44 40 39 C40 34 37 34 36 34Z" fill="rgba(14,69,71,0.85)"/>
                <!-- Fish (Ichthys) -->
                <ellipse cx="52" cy="54" rx="11" ry="6" fill="none" stroke="rgba(201,168,76,0.9)" stroke-width="2.5"/>
                <path d="M63 54 L72 47 L72 61 Z" fill="rgba(201,168,76,0.9)"/>
                <line x1="48" y1="51" x2="46" y2="57" stroke="rgba(201,168,76,0.9)" stroke-width="2" stroke-linecap="round"/>
                <circle cx="46" cy="52" r="1.5" fill="rgba(201,168,76,0.9)"/>
            </svg>
            <h1>UBConnect</h1>
            <p>Campus Record System</p>
            <div class="divider"></div>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username / Campus</label>
                <input type="text" name="username" placeholder="Enter your campus name" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <p class="hint">Sign in with your campus credentials to continue</p>
        <p class="admin-hint">Admin? Use username: <strong style="color:rgba(255,255,255,0.5)">admin</strong></p>
    </div>
</div>
</body>
</html>
