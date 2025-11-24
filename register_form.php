<?php

require 'configuration.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
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

    if ($password === '' || strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters!";
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
<body>

<h2>Registration Form</h2>

<?php
if (!empty($errors)) {
    echo "<ul style='color:red;'>";
    foreach ($errors as $e) {
        echo "<li>$e</li>";
    }
    echo "</ul>";
}
?>

<form method="post">
    <label>Name:
        <input name="name" required>
    </label><br>

    <label>Email:
        <input name="email" type="email" required>
    </label><br>

    <label>Phone:
        <input name="phone">
    </label><br>

    <label>Password:
        <input name="password" type="password" required>
    </label><br>

    <button type="submit">Register</button>
</form>

</body>
</html>
