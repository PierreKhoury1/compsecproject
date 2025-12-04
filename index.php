<?php

$success_message = "";
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $success_message = "Registration successful! You can now log in.";
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lovejoy Antique Evaluation - Home</title>

    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        pre {
            font-size: 10px;
            line-height: 10px;
            text-align: center;
            overflow-x: auto;
        }
    </style>
</head>

<body style="background: linear-gradient(135deg, #d9f0ff, #a8d8ff);">

<!-- ASCII BANNER -->
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

<!-- Main Content -->
<div class="container mt-4">

    <div class="card shadow p-4 mx-auto" style="max-width: 600px;">

        <h1 class="text-center">Welcome to Lovejoy's Antique Evaluation</h1>

        <?php if ($success_message !== ""): ?>
            <div class="alert alert-success text-center fw-bold mt-3">
                <?= htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <p class="text-center mt-3">Please choose an option below:</p>

        <div class="d-grid gap-3 mt-4">
            <a href="register_form.php" class="btn btn-primary btn-lg">Register</a>
            <a href="login_form.php" class="btn btn-secondary btn-lg">Login</a>
        </div>

    </div>

</div>

</body>
</html>
