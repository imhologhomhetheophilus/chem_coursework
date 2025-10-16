<?php require_once 'includes/db_connect.php'; ?>


<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $status = " All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $status = "Invalid email address.";
    } else {
        $sql = "INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sss", $name, $email, $message);
            if ($stmt->execute()) {
                $status = "✅ Thank you, $name! Your message has been sent. We will get back to you at $email.";
                $status = "<div style='
    font-weight: bold;
    color: white;
    background-color: #060b44e0;
    font-size: 18px;
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    margin-top: 15px;
'>
✅ Thank you, <strong>$name</strong>! Your message has been sent.<br>
We will get back to you at <span style='color: #ffe600;'>$email</span>.
</div>";

            } else {
                $status = " Error submitting your message: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $status = " SQL Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact the Laboratory</title>
  <style>
   
    
    h2 {
      text-align: center;
      color: #003366;
    }
    form {
      margin-top: 15px;
    }
    label {
      font-weight: bold;
    }
    input, textarea {
      width: 100%;
      padding: 10px;
      margin-top: 8px;
      margin-bottom: 12px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1em;
      outline: none;
    }
    button {
      background: #007bff;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1em;
    }
    button:hover {
      background: #0056b3;
    }
    .emails {
      margin-top: 25px;
      background: #eef6ff;
      padding: 15px;
      border-left: 5px solid #007bff;
      border-radius: 8px;
    }
    .emails h3 {
      margin-top: 0;
      color: #003366;
    }
    .status {
      text-align: center;
      margin-bottom: 15px;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="container " >
  <h2 class="animate__animated animate__backInDown">Contact the Chemical Engineering Laboratory</h2>

  <?php if (isset($status)) echo "<p class='status'>$status</p>"; ?>

  <form method="POST" action="" class="animate__animated animate__backInUp">
    <label for="name">Your Name</label>
    <input type="text" name="name" id="name" required>

    <label for="email">Your Email</label>
    <input type="email" name="email" id="email" required>

    <label for="message">Message</label>
    <textarea name="message" id="message" rows="5" required></textarea>

    <button type="submit">Send Message</button>
  </form>

  <div class="emails">
    <h3>Official Laboratory Emails</h3>
    <p><strong>chelanfpn@gmail.com</strong></p>
    <p><strong>che409hndii23@gmail.com</strong></p>
  </div>
</div>
<div class="container py-4" style="margin-top: 40px; margin-bottom: 10rem;">

</body>
</html>



<?php include 'includes/footer.php'; ?>
