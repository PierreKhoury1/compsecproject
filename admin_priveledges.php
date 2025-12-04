<?php

// Secure cookies
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();
require 'configuration.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php?error=Please login first");
    exit;
}

// Require admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<div class='alert alert-danger text-center mt-5 w-50 mx-auto'>
            <h3>Access Denied</h3>
            <p>This page is for administrators only.</p>
        </div>");
}

// Fetch evaluation requests
$query = "
    SELECT e.id, e.details, e.contact_method, e.photo_path, e.created_at,
           u.name, u.email, u.phone
    FROM evaluations e
    JOIN users u ON u.id = e.user_id
    ORDER BY e.created_at DESC
";

$result = $connection->query($query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Evaluation Requests</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        img.thumbnail-img {
            max-width: 120px;
            border-radius: 6px;
        }
        pre {
            font-size: 10px;
            line-height: 10px;
            text-align: center;
        }
    </style>
</head>

<body style="background: linear-gradient(135deg, #d9f0ff, #a8d8ff);">

<div class="container mt-4">

    <!-- Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center">
            <h2 class="text-primary">Administrator Panel</h2>
            <p class="text-muted">All Submitted Antique Evaluation Requests</p>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow p-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Details</th>
                        <th>Contact Method</th>
                        <th>Photo</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['details'])) ?></td>
                        <td>
                            <span class="badge bg-info text-dark">
                                <?= htmlspecialchars($row['contact_method']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($row['photo_path'])): ?>
                                <img src="uploads/<?= htmlspecialchars($row['photo_path']) ?>"
                                     class="thumbnail-img"
                                     alt="Uploaded Photo">
                            <?php else: ?>
                                <span class="text-muted">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>

            </table>
        </div>
    </div>

    <!-- Back Button -->
    <div class="text-center mt-3">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

</div>

</body>
</html>
