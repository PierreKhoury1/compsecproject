<?php

require 'configuration.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

    <!-- ------------------------------------------------------
         6. INPUT RESTRICTIONS (HTML5 security)
         ------------------------------------------------------- -->

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