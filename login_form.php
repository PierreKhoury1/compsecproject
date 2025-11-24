<?php
session_start();
require 'configuration.php';

$errors = [];

// If form was submitted, process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = "Please enter username and password.";
    } else {
        // Secure lookup
        $stmt = $connection->prepare(
            "SELECT id, username, password_hash, role FROM users WHERE username = ?"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = "Invalid username or password.";
        } else {
            // Successful login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<body>

<h2>Login</h2>

<?php
// Show errors above the form
if (!empty($errors)) {
    echo "<ul style='color:red;'>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
}
?>

<form method="post">

<label>Username:
    <input name="username" required>
</label><br>

<label>Password:
    <input name="password" type="password" required>
</label><br><br>

<button type="submit">Login</button>

</form>

</body>
</html>
