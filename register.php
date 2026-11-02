<?php

session_start();
include 'database.php';

$message = "";
$messageType = "";
$activeForm = "login";

// REGISTER FUNCTION
if (isset($_POST['register'])) {

    $activeForm = "register";

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {

        $message = "Username must be 3-50 characters.";
        $messageType = "error";

    } elseif (strlen($password) < 8) {

        $message = "Password must be at least 8 characters.";
        $messageType = "error";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $messageType = "error";

    } else {

        $check = $conn->prepare(
            "SELECT id FROM bagsniclairo WHERE username = ?"
        );
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {

            $message = "Username already exists.";
            $messageType = "error";

        } else {

            $sql = "INSERT INTO bagsniclairo
                    (username, password, created_at)
                    VALUES (?, ?, CURRENT_TIMESTAMP)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();

            $message = "Registration successful! You can now sign in.";
            $messageType = "success";

            $activeForm = "login";
        }
    }
}


// LOGIN FUNCTION
if (isset($_POST['login'])) {

    $activeForm = "login";

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {

        $message = "Please enter your username and password.";
        $messageType = "error";

    } else {

        $sql = "SELECT username, password
                FROM bagsniclairo
                WHERE username = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($storedUsername, $storedPassword);
        $userFound = $stmt->fetch();

        if ($userFound && $password === $storedPassword) {

            $message = "Sign in successful! Welcome, " . $storedUsername . ".";
            $messageType = "success";

        } else {

            $message = "Invalid username or password.";
            $messageType = "error";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bags ni Clairo | Account</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f5f0eb;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 20px;
        }

        .container {
            background: white;
            width: 100%;
            max-width: 420px;

            padding: 40px;

            border-radius: 12px;

            box-shadow: 0 5px 20px rgba(0,0,0,0.1);

            text-align: center;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;

            color: #8b5e3c;

            margin-bottom: 10px;
        }

        .subtitle {
            color: #888;
            font-size: 14px;

            margin-bottom: 25px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .tabs {
            display: flex;
            gap: 10px;

            margin-bottom: 25px;
        }

        .tabs button {
            width: 50%;
            padding: 12px;

            border: none;
            border-radius: 6px;

            background: #eee;
            color: #555;

            font-size: 15px;
            cursor: pointer;
        }

        .tabs button.active {
            background: #8b5e3c;
            color: white;
        }

        form {
            text-align: left;
        }

        label {
            display: block;

            margin-bottom: 8px;

            color: #444;
            font-size: 14px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 13px;

            margin-bottom: 18px;

            border: 1px solid #ddd;
            border-radius: 6px;

            font-size: 14px;
            outline: none;
        }

        input:focus {
            border-color: #8b5e3c;
        }

        .submit-btn {
            width: 100%;
            padding: 13px;

            background: #8b5e3c;
            color: white;

            border: none;
            border-radius: 6px;

            font-size: 15px;
            font-weight: bold;

            cursor: pointer;
        }

        .submit-btn:hover {
            background: #70472c;
        }

        .switch {
            margin-top: 20px;

            color: #777;
            font-size: 14px;
        }

        .switch a {
            color: #8b5e3c;
            font-weight: bold;
            text-decoration: none;
        }

        .message {
            padding: 12px;
            margin-bottom: 18px;

            border-radius: 6px;

            font-size: 13px;
        }

        .error {
            background: #ffe8e8;
            color: #c0392b;
        }

        .success {
            background: #e6f7e9;
            color: #278443;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="logo">
        BAGS NI CLAIRO
    </div>

    <p class="subtitle">
        Find your perfect bag with us.
    </p>


    <!-- SIGN IN AND REGISTER TABS -->

    <div class="tabs">

        <button
            type="button"
            id="loginTab"
            class="<?= $activeForm == 'login' ? 'active' : '' ?>"
            onclick="showLogin()">

            Sign In

        </button>

        <button
            type="button"
            id="registerTab"
            class="<?= $activeForm == 'register' ? 'active' : '' ?>"
            onclick="showRegister()">

            Register

        </button>

    </div>


    <!-- MESSAGE -->

    <?php if (!empty($message)): ?>

        <div class="message <?= $messageType ?>">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <!-- SIGN IN FORM -->

    <div
        id="loginForm"
        style="display: <?= $activeForm == 'login' ? 'block' : 'none' ?>;">

        <h2>Welcome Back!</h2>

        <form method="POST" action="register.php">

            <label>Username</label>

            <input
                type="text"
                name="username"
                placeholder="Enter your username"
                required>

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required>

            <button
                type="submit"
                name="login"
                class="submit-btn">

                Sign In

            </button>

        </form>

        <p class="switch">

            Don't have an account?

            <a href="#" onclick="showRegister(); return false;">
                Register here
            </a>

        </p>

    </div>


    <!-- REGISTER FORM -->

    <div
        id="registerForm"
        style="display: <?= $activeForm == 'register' ? 'block' : 'none' ?>;">

        <h2>Create Account</h2>

        <form method="POST" action="register.php">

            <label>Username</label>

            <input
                type="text"
                name="username"
                placeholder="Create a username"
                minlength="3"
                maxlength="50"
                required>

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Create a password"
                minlength="8"
                required>

            <label>Confirm Password</label>

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm your password"
                required>

            <button
                type="submit"
                name="register"
                class="submit-btn">

                Register

            </button>

        </form>

        <p class="switch">

            Already have an account?

            <a href="#" onclick="showLogin(); return false;">
                Sign In here
            </a>

        </p>

    </div>

</div>


<script>

function showLogin() {

    document.getElementById("loginForm").style.display = "block";

    document.getElementById("registerForm").style.display = "none";

    document.getElementById("loginTab").classList.add("active");

    document.getElementById("registerTab").classList.remove("active");

}

function showRegister() {

    document.getElementById("loginForm").style.display = "none";

    document.getElementById("registerForm").style.display = "block";

    document.getElementById("registerTab").classList.add("active");

    document.getElementById("loginTab").classList.remove("active");

}

</script>

</body>
</html>