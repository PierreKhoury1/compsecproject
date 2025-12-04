<?php

// prevent Javascript from accessing the session cookie 
ini_set('session.cookie_httponly', 1);

// prevent cookies being sent in cross-site requests (CSRF protection)
ini_set('session.cookie_samesite', 'Strict');

//this will send cookies over https://
ini_set('session.cookie_secure', 1);


session_start(); 

// Generate CSRF token if it does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require 'configuration.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("<p style='color:red;'>Security Error: Invalid CSRF token.</p>");
    }

    // Sanitize and validate inputs
    $name  = mysqli_real_escape_string($connection, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($connection, trim($_POST['email'] ?? ''));
    $phone = mysqli_real_escape_string($connection, trim($_POST['phone'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($name === '') $errors[] = "Name is required.";
    if (strlen($name) < 3 || strlen($name) > 30) {
        $errors[] = "Username must be between 3 and 30 characters.";
    }

    $phone = trim($phone);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";
    if ($phone === '') $errors[] = "Phone number is required.";

    if (strlen($email) > 255) {
    $errors[] = "Email is too long.";
}

    if (!preg_match('/^[A-Za-z0-9_\-]+$/', $name)) {
        $errors[] = "Name may only contain letters, numbers, underscores, and hyphens.";
    }

    // Password policy
    if ($password === '' || strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "Password must contain at least one uppercase letter.";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "Password must contain at least one lowercase letter.";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must contain at least one number.";
    if (!preg_match('/[\W]/', $password)) $errors[] = "Password must contain at least one special character.";


    // check for duplicate usernames within the user database

    $check_duplicate_user = $connection -> prepare(
        "SELECT id FROM users WHERE name = ? LIMIT 1"
    );

    $check_duplicate_user-> bind_param("s", $name);

    $check_duplicate_user-> execute();

    $check_duplicate_user -> store_result();

    if ($check_duplicate_user-> num_rows > 0){
        $errors[] = "This username is already taken!";
    }

    $check_duplicate_user -> close();

    // This will check for duplicate emails

    $check_duplicate_email = $connection -> prepare(
        "SELECT id FROM users WHERE email = ? LIMIT 1"
    );

    $check_duplicate_email-> bind_param("s", $email);

    $check_duplicate_email -> execute();

    $check_duplicate_email-> store_result();

    if ($check_duplicate_email-> num_rows > 0){
        $errors[] = "An account with this email already exists.";
    }

    $check_duplicate_email -> close();

    if (empty($errors)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $statement = $connection->prepare(
            "INSERT INTO users (name, email, phone, password_hash)
            VALUES (?, ?, ?, ?)"
        );

        $statement->bind_param("ssss", $name, $email, $phone, $hashed_password);

        if ($statement->execute()) {
            unset($_SESSION['csrf_token']);
            header("Location: index.php?registered=1");
            exit;
        } else {
            $errors[] = "Error: Could not register user.";
        }

        $statement->close();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Register</title>

    <!-- BOOTSTRAP CSS -->
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

<!-- Registration Card -->
<div class="container mt-4">
    <div class="card shadow p-4 mx-auto" style="max-width: 480px;">

        <h2 class="text-center mb-3">Registration Form</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="mb-3">
                <label class="form-label">Name:</label>
                <input name="name" class="form-control" required
                       value="<?= isset($name) ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Email:</label>
                <input name="email" type="email" class="form-control" required
                       value="<?= isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Phone:</label>
                <input name="phone" class="form-control" required pattern="[0-9]+"
                       title="Phone number must contain only digits"
                       value="<?= isset($phone) ? htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Password:</label>
                <input name="password" type="password" class="form-control" minlength="8" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>

            <a href="index.php" class="btn btn-outline-secondary w-100 mt-3">⬅ Back to main</a>


        </form>

    </div>
</div>

</body>
</html>
