<?php


require 'configuration.php'; //this is the file which will conncet to the mysqli database 

$errors = []; // this will store errors

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') {
        $errors[] = "Username is required!";

    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Valid email is required.";
    }

    if ($password === '' || strlen($password) <8) {
        $errors[] = "Password must be atleast 8 characters, and must contain a number!";
    }

    if ($phone === ''){
        $errors[] = "Phone number is required";

    }


    if (empty($errors)){
        $hash_passwords = password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>

<html>
    <body>
        <h2> Registration Form <h2>
            <form method = "post">
                <label> Name: 
                    <input name= "name" required> 
</label><br>

<label> Email:
    <input name = "email" type = "email" required>
</label><br>

<label> Phone:
    <input name = "phone">
</label><br>

<label> password:
    <input name = "password" type = "password" required>
</label><br>

<button type = "submit"> Register </button>
  </form>
</body>
</html>

