<?php
session_start();

// User must be logged in to access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>

<h2>Welcome, <?= htmlspecialchars($_SESSION['name']); ?>!</h2>

<p>You are logged in successfully.</p>

<ul>
    <li><a href="request_evaluation.php">Request Evaluation</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>

</body>
</html>
