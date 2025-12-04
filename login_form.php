<?php
// Start the session so we can store user login information
session_start();

// Include database connection
require 'configuration.php';

// Generate CSRF token (if not created yet)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Array to store error messages
$errors = [];

// Anti brute force settings
$MAX_ATTEMPTS = 5;
$LOCKOUT_MINUTES = 60;

// Check if the login form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ---------------------------
       CSRF PROTECTION
    ---------------------------- */
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
    {
        die("Security error: Invalid CSRF token.");
    }

    /* ---------------------------
       CAPTCHA VALIDATION
    ---------------------------- */
    if (!isset($_POST['captcha_input']) ||
        strtoupper(trim($_POST['captcha_input'])) !== ($_SESSION['captcha_code'] ?? ''))
    {
        $errors[] = "Incorrect CAPTCHA. Please try again.";
    }

    // Prevent special characters from breaking SQL
    $raw_name     = mysqli_real_escape_string($connection, $_POST['name'] ?? '');
    $raw_password = mysqli_real_escape_string($connection, $_POST['password'] ?? '');

    // Trim whitespace
    $name     = trim($raw_name);
    $password = trim($raw_password);

    /* ---------------------------
       INPUT VALIDATION
    ---------------------------- */

    if ($name === '' || $password === '') {
        $errors[] = "Please enter name and password.";
    }

    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $name)) {
        $errors[] = "Username must be 3–30 characters and contain only letters, numbers, or underscore.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    /* STOP if validation failed */
    if (empty($errors)) {

        // Fetch user including security fields
        $stmt = $connection->prepare(
            "SELECT id, name, password_hash, role, failed_attempts, last_failed_login
             FROM users 
             WHERE name = ?"
        );
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // If user exists, check for lockout
        if ($user) {

            if ($user['failed_attempts'] >= $MAX_ATTEMPTS) {

                $lastFail = strtotime($user['last_failed_login']);
                $unlockTime = $lastFail + ($LOCKOUT_MINUTES * 60);

                if (time() < $unlockTime) {
                    $remaining = ceil(($unlockTime - time()) / 60);
                    $errors[] = "Account locked due to multiple failed attempts. Try again in $remaining minute(s).";
                }
            }
        }

        // Proceed only if no lockout errors
        if (empty($errors)) {

            // INVALID LOGIN
            if (!$user || !password_verify($password, $user['password_hash'])) {

                if ($user) {
                    // Increment failed attempts
                    $update = $connection->prepare(
                        "UPDATE users
                         SET failed_attempts = failed_attempts + 1,
                             last_failed_login = NOW()
                         WHERE id = ?"
                    );
                    $update->bind_param("i", $user['id']);
                    $update->execute();
                }

                $errors[] = "Invalid name or password.";

            } else {

                // SUCCESSFUL LOGIN — RESET failures
                $reset = $connection->prepare(
                    "UPDATE users
                     SET failed_attempts = 0,
                         last_failed_login = NULL
                     WHERE id = ?"
                );
                $reset->bind_param("i", $user['id']);
                $reset->execute();

                // Protect against session fixation
                session_regenerate_id(true);

                // Store session info
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];

                header("Location: dashboard.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        pre {
            font-size: 10px;
            line-height: 10px;
            overflow-x: auto;
            text-align: center;
        }
    </style>
</head>

<body style="background: linear-gradient(135deg, #d9f0ff, #a8d8ff);">

<!-- ASCII Banner -->
<div class="container mt-4">
<pre>
          _____           _______                   _____                    _____                    _____                   _______               _____          
         /\    \         /::\    \                 /\    \                  /\    \                  /\    \                 /::\    \             |\    \         
        /::\____\       /::::\    \               /::\____\                /::\    \                /::\    \               /::::\    \            |:\____\        
       /:::/    /      /::::::\    \             /:::/    /               /::::\    \               \:::\    \             /::::::\    \           |::|   |        
      /:::/    /      /::::::::\    \           /:::/    /               /::::::\    \               \:::\    \           /::::::::\    \          |::|   |        
     /:::/    /      /:::/~~\:::\    \         /:::/    /               /:::/\:::\    \               \:::\    \         /:::/~~\:::\    \         |::|   |        
    /:::/    /      /:::/    \:::\    \       /:::/____/               /:::/__\:::\    \               \:::\    \       /:::/    \:::\    \        |::|   |        
   /:::/    /      /:::/    / \:::\    \      |::|    |               /::::\   \:::\    \              /::::\    \     /:::/    / \:::\    \       |::|   |        
  /:::/    /      /:::/____/   \:::\____\     |::|    |     _____    /::::::\   \:::\    \    _____   /::::::\    \   /:::/____/   \:::\____\      |::|___|______  
 /:::/    /      |:::|    |     |:::|    |    |::|    |    /\    \  /:::/\:::\   \:::\    \  /\    \ /:::/\:::\    \ |:::|    |     |:::|    |     /::::::::\    \ 
/:::/____/       |:::|____|     |:::|    |    |::|    |   /::\____\/:::/__\:::\   \:::\____\/::\    /:::/  \:::\____\|:::|____|     |:::|    |    /::::::::::\____\
\:::\    \        \:::\    \   /:::/    /     |::|    |  /:::/    /\:::\   \:::\   \::/    /\:::\  /:::/    \::/    / \:::\    \   /:::/    /    /:::/~~~~/~~      
 \:::\    \        \:::\    \ /:::/    /      |::|    | /:::/    /  \:::\   \:::\   \/____/  \:::\/:::/    / \/____/   \:::\    \ /:::/    /    /:::/    /         
  \:::\    \        \:::\    /:::/    /       |::|____|/:::/    /    \:::\   \:::\    \       \::::::/    /             \:::\    /:::/    /    /:::/    /          
   \:::\    \        \:::\__/:::/    /        |:::::::::::/    /      \:::\   \:::\____\       \::::/    /               \:::\__/:::/    /    /:::/    /           
    \:::\    \        \::::::::/    /         \::::::::::/____/        \:::\   \::/    /        \::/    /                 \::::::::/    /     \::/    /            
     \:::\    \        \::::::/    /           ~~~~~~~~~~               \:::\   \/____/          \/____/                   \::::::/    /       \/____/              
      \:::\    \        \::::/    /                                      \:::\    \                                         \::::/    /                            
       \:::\____\        \::/____/                                        \:::\____\                                         \::/____/                             
        \::/    /         ~~                                               \::/    /                                          ~~                                   
         \/____/                                                            \/____/                                                                                
</pre>
</div>

<!-- Login Card -->
<div class="container mt-4">
    <div class="card shadow p-4 mx-auto" style="max-width: 420px;">

        <h2 class="text-center mb-3">Login</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <div class="mb-3">
                <label class="form-label">Name:</label>
                <input name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password:</label>
                <input name="password" type="password" class="form-control" required>
            </div>

            <!-- CAPTCHA -->
            <div class="mb-3">
                <label class="form-label">Captcha:</label>
                <div class="d-flex align-items-center">
                    <img src="captcha.php" alt="CAPTCHA Image" class="border me-3">
                    <button type="button"
                            onclick="this.previousElementSibling.src='captcha.php?'+Date.now()"
                            class="btn btn-sm btn-outline-secondary">
                        Refresh
                    </button>
                </div>
                <input name="captcha_input" class="form-control mt-2" required>
            </div>

            <div class="mb-3 text-end">
                <a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a>
            </div>

            <button class="btn btn-primary w-100" type="submit">Login</button>

            <a href="index.php" class="btn btn-outline-secondary w-100 mt-3">⬅ Back to main</a>

        </form>
    </div>
</div>

</body>
</html>
