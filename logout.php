<?php
session_start();
session_unset();  // Remove all session variables
session_destroy();  // Destroy the session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="2;url=group_login.php"> <!-- Redirect after 2 seconds -->
    <style>
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f8f9fa;
        }
        .logout-box {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }
        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 15px;
        }
        .dept-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="logout-box">
        <!-- School/Department Logo -->
        <img src="assets/chem_logo.png" alt="School Logo" class="logo">

        <!-- Department Name -->
        <div class="dept-title">
            DEPARTMENT OF CHEMICAL ENGINEERING TECHNOLOGY<br>
            FEDERAL POLYTECHNIC, NASARAWA
        </div>

        <!-- Logout Message -->
        <h4 class="text-danger fw-bold mb-3">Logging out...</h4>
        <p class="text-muted mb-4">You’ll be redirected to the login page shortly.</p>

        <!-- Spinner -->
        <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</body>
</html>
