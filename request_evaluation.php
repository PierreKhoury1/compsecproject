<?php





// this ensures secure cookies

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

var_dump($_SESSION['user_id']);

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php?error=Please login first");
    exit;
}

require 'configuration.php';


//CSRF Token Generation
if (empty($_SESSION['csrf_token_eval'])) {
    $_SESSION['csrf_token_eval'] = bin2hex(random_bytes(32));
}

// this is to generate the csrf token
if (empty($_SESSION['csrf_token_eval'])) {
    $_SESSION['csrf_token_eval'] = bin2hex(random_bytes(32));
}



$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validating csrf
    if (!isset($_POST['csrf_token_eval']) ||
        !hash_equals($_SESSION['csrf_token_eval'], $_POST['csrf_token_eval'])) {

        die("<p style='color:red;'>Security error: Invalid CSRF token.</p>");
    }

    // sanitize from the inputs below:

    // Sanitize form inputs
    $details = trim($_POST['details'] ?? '');
    $contact_method = trim($_POST['contact_method'] ?? '');

    if ($details === "") {
        $errors[] = "You must enter details of the item.";
    }

    if ($contact_method !== "email" && $contact_method !== "phone") {
        $errors[] = "Invalid contact option.";
    }


    // this is to do security checks on the type of update


    $allowed_types = ['image/jpeg', 'image/png']; //only jpeg and png are allowed

    if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_type = mime_content_type($file_tmp);


        // if the file type is not a jpeg or PNG it throws an error
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPEG and PNG images are allowed.";
        }

        // assigns a unique filename
        $new_filename = uniqid("photo_", true) . ".jpg";
        $upload_path = "uploads/" . $new_filename;

        } else {
        $errors[] = "Please upload a valid image file.";
        }


    // if there were NO ERRORS, then we save + move the file


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

<h2>Request Evaluation</h2>

<?php
if (!empty($errors)) {
    echo "<ul style='color:red;'>";
    foreach ($errors as $e) {
        echo "<li>" . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . "</li>";
    }
    echo "</ul>";
}

if ($success) {
    echo "<p style='color:green; font-weight:bold;'>" . htmlspecialchars($success) . "</p>";
}
?>

<form method="post" enctype="multipart/form-data">

    <input type="hidden" name="csrf_token_eval"
           value="<?php echo htmlspecialchars($_SESSION['csrf_token_eval']); ?>">

    <label>Object Details:<br>
        <textarea name="details" required></textarea>
    </label><br><br>

    <label>Preferred Contact:
        <select name="contact_method" required>
            <option value="email">Email</option>
            <option value="phone">Phone</option>
        </select>
    </label><br><br>

    <label>Upload Photo (JPEG/PNG):
        <input type="file" name="photo" accept=".jpg,.jpeg,.png" required>
    </label><br><br>

    <button type="submit">Submit Request</button>
</form>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>