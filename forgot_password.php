<?php
// Secure session cookies
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

require 'configuration.php';

$errors = [];
$success = "";

// Generate CSRF token
if (empty($_SESSION['csrf_token_forgot'])) {
    $_SESSION['csrf_token_forgot'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate CSRF token
    if (!isset($_POST['csrf_token_forgot']) ||
        !hash_equals($_SESSION['csrf_token_forgot'], $_POST['csrf_token_forgot'])) {
        die("<p style='color:red;'>Security error: Invalid CSRF token.</p>");
    }

    // Get and sanitize email
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email.";
    }

    if (empty($errors)) {

        // Check if email exists in the system
        $stmt = $connection->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $errors[] = "No account found with that email.";
        } else {
            $user = $result->fetch_assoc();

            // Create reset token
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", time() + 3600); // valid for 1 hour

            // Save token in database
            $stmt = $connection->prepare("
                UPDATE users 
                SET reset_token = ?, reset_expires = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssi", $token, $expires, $user['id']);
            $stmt->execute();

            // Normally you would send an email here
            // Instead, we display the reset link for coursework demonstration:

            $reset_link = "http://localhost/reset_password.php?token=" . urlencode($token);

            $success = "A password reset link has been generated.<br>
                        Since email is disabled, here is your reset URL:<br><br>
                        <a href='$reset_link'>$reset_link</a>";

            unset($_SESSION['csrf_token_forgot']);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        pre { font-size: 10px; line-height: 10px; text-align: center; }
    </style>
</head>

<body style="background: linear-gradient(135deg, #d9f0ff, #a8d8ff);">

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

<div class="container mt-4">
    <div class="card shadow p-4 mx-auto" style="max-width: 500px;">

        <h3 class="text-center mb-3">Forgot Password</h3>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success text-center">
                <?= $success ?>
            </div>
        <?php else: ?>

        <form method="post">

            <input type="hidden" name="csrf_token_forgot"
                   value="<?= htmlspecialchars($_SESSION['csrf_token_forgot']) ?>">

            <div class="mb-3">
                <label class="form-label">Enter your email address:</label>
                <input name="email" type="email" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>

        </form>

        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="login_form.php" class="btn btn-secondary">Back to Login</a>
        </div>

    </div>
</div>

</body>
</html>
