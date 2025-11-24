<?php

// prevent Javascript from accessing the session cookie 
ini_set('session.cookie_httponly', 1);

// prevent cookies being sent in cross-site requests (CSRF protection)
ini_set('session.cookie_samesite', 'Strict');

session_start(); 


// 2. GENERATE CSRF TOKEN IF it does not EXISTS
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require 'configuration.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // this will validate the csrf token
    if (!isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {

        die("<p style='color:red;'>Security Error: Invalid CSRF token.</p>");
    }

    # prevents against sql injections
    $name = mysqli_real_escape_string($connection, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($connection, trim($_POST['email'] ?? ''));
    $phone = mysqli_real_escape_string($connection, trim($_POST['phone'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($name === '') {
        $errors[] = "name is required!";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }

    if ($phone === '') {
        $errors[] = "Phone number is required";
    }

    // strong password policy validation

    if ($password === '' || strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters!";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }

    if (!preg_match('/[\W]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }

    if (empty($errors)) {

        // Correct hashing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Correct column names + variables
        $statement = $connection->prepare(
            "INSERT INTO users (name, email, phone, password_hash)
            VALUES (?, ?, ?, ?)"
        );

        $statement->bind_param("ssss", $name, $email, $phone, $hashed_password);

        if ($statement->execute()) {
            //echo "<p style='color:green;'>Registration successful!</p>";
            unset($_SESSION['csrf_token']);

            header("Location: index.php?registered=1");
            exit;

            
        } else {
            echo "<p style='color:red;'>Error: Could not register user.</p>";
        }

        $statement->close();
    }
}


?>
<!DOCTYPE html>
<html>
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


<body>

<h2>Registration Form</h2>

<?php
// this is for XSS prevention with
if (!empty($errors)) {
    echo "<ul style='color:red;'>";
    foreach ($errors as $e) {
        echo "<li>" . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . "</li>";
    }
    echo "</ul>";
}
?>

<form method="post">

    <!-- CSRF TOKEN -->
    <input type="hidden" name="csrf_token"
           value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

    <label>Name:
        <input name="name" 
               required
               value="<?php echo isset($name) ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : ''; ?>">
    </label><br>

    <label>Email:
        <input name="email" 
               type="email" 
               required
               value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>">
    </label><br>

    <label>Phone:
        <input name="phone"
               pattern="[0-9]+"
               title="Phone number must contain only digits"
               required
               value="<?php echo isset($phone) ? htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') : ''; ?>">
    </label><br>

    <label>Password:
        <input name="password" 
               type="password" 
               minlength="8"
               required>
    </label><br>

    <button type="submit">Register</button>
</form>

</body>
</html>