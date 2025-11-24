<?php
session_start();
require 'configuration.php';

$errors = [];

// If form was submitted, process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    #we use mysqli_real_espace_string to 
    $raw_name     = mysqli_real_escape_string($connection, $_POST['name'] ?? '');
    $raw_password = mysqli_real_escape_string($connection, $_POST['password'] ?? '');


    $name     = trim($raw_name);
    $password = trim($raw_password);

    if ($name === '' || $password === '') {
        $errors[] = "Please enter name and password.";
    } else {
        // Secure lookup
        $stmt = $connection->prepare(
            "SELECT id, name, password_hash FROM users WHERE name = ?"
        );
        $stmt->bind_param("s", $name);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = "Invalid name or password.";
        } else {
            // Successful login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];

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

<label>name:
    <input name="name" required>
</label><br>

<label>Password:
    <input name="password" type="password" required>
</label><br><br>

<button type="submit">Login</button>

</form>

</body>
</html>
