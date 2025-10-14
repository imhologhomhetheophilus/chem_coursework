  <?php include 'includes/header.php'; ?>

  <!-- experiments.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Chemical Engineering Experiments</title>
    <style>
        
        h2 {
            text-align: center;
            color: #004d40;
        }
        form {
            background: #fff;
            padding: 20px;
            margin: 0 auto;
            width: 50%;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }
        select, .button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .button{
            
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #004d40;
        }
        .experiment-section {
            background: #fff;
            padding: 20px;
            margin-top: 30px;
            border-radius: 10px;
        }
        h3 {
            color: #00695c;
        }
        .highlight {
            background: #e0f2f1;
            padding: 10px;
            border-left: 4px solid #00796b;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2>CHEMICAL ENGINEERING EXPERIMENTS</h2>

    <form method="POST" action="">
        <label for="session">Select Session:</label>
        <select name="session" id="session" required>
            <option value="">--Select Session--</option>
            <option value="2024/2025">2024/2025</option>
            <option value="2025/2026">2025/2026</option>
            <option value="2026/2027">2026/2027</option>
        </select>

        <label for="semester">Select Semester:</label>
        <select name="semester" id="semester" required>
            <option value="">--Select Semester--</option>
            <option value="first">First Semester</option>
            <option value="second">Second Semester</option>
        </select>

        <label for="level">Select Level:</label>
        <select name="level" id="level" required>
            <option value="">--Select Level--</option>
            <option value="NDII">ND II (200 Level)</option>
            <option value="HNDI">HND I (300 Level)</option>
            <option value="HNDII">HND II (400 Level)</option>
        </select>

        <button type="submit" name="show_experiments" class="button bg-primary">Show Experiments</button>
    </form>

    <?php
    if (isset($_POST['show_experiments'])) {
        $session = $_POST['session'];
        $semester = $_POST['semester'];
        $level = $_POST['level'];

        echo "<div class='experiment-section'>";
        echo "<h3>Showing experiments for:</h3>";
        echo "<div class='highlight'>Session: <b>$session</b> | Semester: <b>" . ucfirst($semester) . "</b> | Level: <b>$level</b></div>";

        // === CONTENT BASED ON SELECTION ===

        if ($level == "NDII" && $semester == "first") {
            echo "<h3>ND II - FIRST SEMESTER</h3>";
            echo "<p><b>Course:</b> CHEMICAL ENGINEERING LABORATORY I<br>
            <b>Course Code:</b> CHE 213<br>
            <b>Contact Hours:</b> 6<br>
            <b>Date:</b> Tuesday<br>
            <b>Time:</b> 10:00 – 6:00 PM</p>";
            echo "<h4>Experiments</h4>
            <ul>
                <li>Determination of Heat of Reaction</li>
                <li>Determination of Heat of Combustion</li>
                <li>Measurement of Specific Latent Heat of Vaporization</li>
            </ul>";
        }

        elseif ($level == "NDII" && $semester == "second") {
            echo "<h3>ND II - SECOND SEMESTER</h3>";
            echo "<p><b>Course:</b> CHEMICAL ENGINEERING LABORATORY II<br>
            <b>Course Code:</b> CHE 226<br>
            <b>Contact Hours:</b> 6<br>
            <b>Date:</b> Tuesday<br>
            <b>Time:</b> 10:00 – 6:00 PM</p>";
            echo "<h4>Experiments</h4>
            <ul>
                <li>Diffusion Coefficient of NaCl in Water</li>
                <li>Measurement of Flowrate using Venturi Meter</li>
                <li>Verification of Bernoulli’s Theorem</li>
            </ul>";
        }

        elseif ($level == "HNDI" && $semester == "first") {
            echo "<h3>HND I - FIRST SEMESTER</h3>";
            echo "<p><b>Course:</b> CHEMICAL ENGINEERING LABORATORY III<br>
            <b>Course Code:</b> CHE 305<br>
            <b>Contact Hours:</b> 6<br>
            <b>Date:</b> Wednesday<br>
            <b>Time:</b> 10:00 – 6:00 PM</p>";
            echo "<h4>Experiments</h4>
            <ul>
                <li>Thermal Conductivity of a Liquid</li>
                <li>Natural and Forced Convection</li>
                <li>Heat Exchanger Performance</li>
            </ul>";
        }

        elseif ($level == "HNDI" && $semester == "second") {
            echo "<h3>HND I - SECOND SEMESTER</h3>";
            echo "<p><b>Course:</b> CHEMICAL ENGINEERING LABORATORY IV<br>
            <b>Course Code:</b> CHE 308<br>
            <b>Contact Hours:</b> 6<br>
            <b>Date:</b> Wednesday<br>
            <b>Time:</b> 10:00 – 6:00 PM</p>";
            echo "<h4>Experiments</h4>
            <ul>
                <li>Tray Dryer and Rotary Dryer</li>
                <li>Centrifugal Pump Efficiency</li>
                <li>Process Control System</li>
            </ul>";
        }

        elseif ($level == "HNDII" && $semester == "first") {
            echo "<h3>HND II - FIRST SEMESTER</h3>";
            echo "<p><b>Course:</b> CHEMICAL ENGINEERING LABORATORY V<br>
            <b>Course Code:</b> CHE 409<br>
            <b>Contact Hours:</b> 6<br>
            <b>Date:</b> Wednesday<br>
            <b>Time:</b> 10:00 – 6:00 PM</p>";
            echo "<h4>Experiments</h4>
            <ul>
                <li>Drying Characteristics of a Solid</li>
                <li>Chemical Reaction Rate Determination</li>
                <li>Distillation Column Operation</li>
                <li>PID Controller Performance</li>
            </ul>";
        }

        else {
            echo "<p style='color:red;'>No experiments found for your selection.</p>";
        }

        echo "</div>";
    }
    ?>
    <div class="container py-5" style="margin-bottom: 10rem;">
</body>
</html>



<?php include 'includes/footer.php'; ?>
