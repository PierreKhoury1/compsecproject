<?php

// Secure cookie settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php?error=Please login first");
    exit;
}

require 'configuration.php';

// CSRF Token
if (empty($_SESSION['csrf_token_eval'])) {
    $_SESSION['csrf_token_eval'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF Validate
    if (!isset($_POST['csrf_token_eval']) ||
        !hash_equals($_SESSION['csrf_token_eval'], $_POST['csrf_token_eval'])) {

        die("<p style='color:red;'>Security error: Invalid CSRF token.</p>");
    }

    // Sanitize inputs
    $details = trim($_POST['details'] ?? '');
    $contact_method = trim($_POST['contact_method'] ?? '');

    if ($details === "") {
        $errors[] = "You must enter details of the item.";
    }

    if (!in_array($contact_method, ['email', 'phone'])) {
        $errors[] = "Invalid contact option.";
    }

    // File upload validation
    $allowed_types = ['image/jpeg', 'image/png'];

    if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {

        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_type = mime_content_type($file_tmp);

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPEG and PNG images are allowed.";
        }

        $new_filename = uniqid("photo_", true) . ".jpg";
        $upload_path = "uploads/" . $new_filename;

    } else {
        $errors[] = "Please upload a valid image file.";
    }

    // Only store if no errors
    if (empty($errors)) {

        move_uploaded_file($file_tmp, $upload_path);

        $stmt = $connection->prepare(
            "INSERT INTO evaluations (user_id, details, contact_method, photo_path)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param("isss", $_SESSION['user_id'], $details, $contact_method, $new_filename);
        $stmt->execute();
        $stmt->close();

        $success = "Evaluation request submitted successfully!";
        unset($_SESSION['csrf_token_eval']);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Request Evaluation</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        pre {
            font-size: 10px;
            line-height: 10px;
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
\:::\    \        \:::\    \   /:::/    /     |::|    |  /:::/    /\:::\   \:::\   \\::/    /\:::\  /:::/    \::/    / \:::\    \   /:::/    /    /:::/~~~~/~~      
 \:::\    \        \:::\    \ /:::/    /      |::|    | /:::/    /  \:::\   \:::\   \/____/  \\:::\/:::/    / \/____/   \:::\    \ /:::/    /    /:::/    /         
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

<!-- Form Card -->
<div class="container mt-4">
    <div class="card shadow p-4 mx-auto" style="max-width: 700px;">

        <h2 class="text-center mb-3">Request an Evaluation</h2>

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
            <div class="alert alert-success fw-bold text-center">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token_eval"
                   value="<?= htmlspecialchars($_SESSION['csrf_token_eval']) ?>">

            <div class="mb-3">
                <label class="form-label">Object Details</label>
                <textarea class="form-control" name="details" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Preferred Contact Method</label>
                <select class="form-select" name="contact_method" required>
                    <option value="email">Email</option>
                    <option value="phone">Phone</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload Photo (JPEG/PNG)</label>
                <input type="file" class="form-control" name="photo" accept=".jpg,.jpeg,.png" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Submit Request</button>
        </form>

        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

    </div>
</div>

</body>
</html>
