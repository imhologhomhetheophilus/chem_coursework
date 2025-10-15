<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html>
<head>
  <title>Chemical Engineering Experiments</title>
  <style>
    body {
      background: #f5f7f9;
      font-family: Arial, sans-serif;
    }
    h2 {
      text-align: center;
      color: #004d40;
      margin-top: 20px;
    }
    form {
      background: #fff;
      padding: 20px;
      margin: 20px auto;
      width: 60%;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    label {
      display: block;
      font-weight: bold;
      margin-top: 10px;
      color: #333;
    }
    select, button {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      margin-top: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      background-color: #00796b;
      color: white;
      border: none;
      cursor: pointer;
      margin-top: 20px;
    }
    button:hover {
      background-color: #004d40;
    }
    .experiment-section {
      background: #fff;
      padding: 25px;
      margin: 40px auto;
      width: 80%;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.15);
    }
    h3 {
      color: #00695c;
      border-bottom: 2px solid #e0f2f1;
      padding-bottom: 5px;
    }
    pre {
      background: #f0f0f0;
      padding: 15px;
      border-radius: 8px;
      white-space: pre-wrap;
      line-height: 1.6;
      font-size: 15px;
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
    <option value="NDII">ND II</option>
    <option value="HNDI">HND I</option>
    <option value="HNDII">HND II</option>
  </select>

  <button type="submit" name="show_experiments">Show Experiments</button>
</form>

<?php
if (isset($_POST['show_experiments'])) {
  $session = $_POST['session'];
  $semester = $_POST['semester'];
  $level = $_POST['level'];

  echo "<div class='experiment-section'>";
  echo "<h3>Session: $session | Semester: " . ucfirst($semester) . " | Level: $level</h3>";


  // === ND II FIRST SEMESTER ===
  if ($level == "NDII" && $semester == "first") {
    echo "<pre>
NATIONAL DIPLOMA IN CHEMICAL ENGINEERING TECHNOLOGY
FIRST SEMESTER LABORATORY PRACTICAL 
COURSE: CHEMICAL ENGINEERING LABORATORY I
COURSE CODE: CHE 213 					CONTACT HOURS: 6
DATE: TUESDAY	  					TIME: 10:00 – 6:00 PM  
SECOND SEMESTER
COURSE: CHEMICAL ENGINEERING LABORATORY II
COURSE CODE: CHE 226 					CONTACT HOURS: 6
DATE: TUESDAY	  					TIME: 10:00 – 6:00 PM  

LABORATORY PRACTICAL INDEX 
EXPERIMENT NO	TITLE
CHEMICAL ENGINEERING THERMODYNAMICS
	
EXPERIMENT NO.: 1	DETERMINATION OF HEAT OF REACTION
EXPERIMENT NO.: 2	DETERMINATION OF HEAT OF COMBUSTION
EXPERIMENT NO.: 3	MEASUREMENTS OF SPECIFIC LATENT HEAT OF VAPORIZATION USING ELECTRIC METHOD
	
HEAT TRANSFER
(TRANSPORT PHENOMENA I)
EXPERIMENT NO: 4	MEASUREMENT OF TEMPERATURE DISTRIBUTION FOR STEADY-STATE CONDUCTION 
EXPERIMENT NO: 5	UNDERSTANDING THE USE OF THE FOURIER RATE EQUATION
EXPERIMENT NO: 6	DETERMINATION OF OVERALL HEAT TRANSFER COEFFICIENT FOR COMPOSITE WALL
EXPERIMENT NO: 5	INVESTIGATING THE DRYING CHARACTERISTICS OF A SOLID MATERIAL UNDER NATURAL DRAFT CONDITION
EXPERIMENT NO. 6	DETERMINATION OF CONDUCTIVITY OF METAL SPECIMENS
EXPERIMENT NO. 7	CALIBRATION AND USES OF BASIC TYPES OF TEMPERATURE MEASURING INSTRUMENTS
EXPERIMENT NO. 8	DETERMINATION OF HEAT EXCHANGER PERFORMANCE
EXPERIMENT NO. 9	DETERMINATION OF HEAT EXCHANGER EFFECTIVES AND EFFECT OF FLOWRATE ON HEAT EXCHANGER PERFORMANCE
EXPERIMENT NO. 10	DETERMINATION OF DRYING CHARACTERISTICS OF A GIVEN MATERIAL
MASS TRANSFER
(Transport Phenomena II)
EXPERIMENT NO. 11	DETERMINATION OF DIFFUSIVITY OF VOLATILE LIQUID (ACETONE) INTO AIR.
EXPERIMENT NO. 12	DETERMINATION OF DIFFUSION COEFFICIENT OF 2M SODIUM CHLORIDE SOLUTION IN DISTILLED WATER
FLUID MECHANICS
(Transport Phenomena III)
EXPERIMENT NO. 13	MEASUREMENT OF FLOWRATE USING VENTURI METER AND ORICE METER
EXPERIMENT NO. 14	VERIFICATION OF BERNOULLI’S THEOREM
EXPERIMENT NO. 15	DETERMINATION OF REYNOLD’S NUMBER FOR LAMINAR AND TURBULENT FLOWS.
EXPERIMENT NO. 16	DETERMINATION OF HEAD LOSS AT DIFFERENT FLOW RATES THROUGH AN ORIFICE AND VENTURI METER
EXPERIMENT NO. 17	DETERMINATION OF PRESSURE DROP IN ORIFICE AND VENTURE METER AS A RESULT OF SUDDEN CONTRACTION AND EXPANSION
EXPERIMENT NO. 18	STUDY OF FLUID MIXING SYSTEM
EXPERIMENT NO. 19	DETERMINATION OF PUMP EFFICIENCY
	
	MECHANICAL OPERATION 
EXPERIMENT NO: 20	SEPARATION OF SOLID PARTICLES INTO SIZES AND DETERMINATION OF ITS SURFACE MEAN DIAMETER
EXPERIMENT 21	COMPARING THE EFFICIENCY OF CYCLONE SEPARATOR ON DIFFERENT TYPES OF MATERIALS
	
	INSTRUMENTATION AND PROCESS CONTROL
EXPERIMENT NO. 22	DETERMINATION OF THE ACTUAL TEMPERATURE RESPONSE OF A TEMPERATURE MEASURING INSTRUMENT
EXPERIMENT NO. 23	DETERMINATION OF TEMPERATURE AND PRESSURE OF A SYSTEM WITH ON–OFF CONTROLLER
	
	CORROSION AND MATERIAL SCIENCE (CORROSION STUDIES)
EXPERIMENT NO. 24	DETERMINATION OF THE EFFECT OF pH LEVEL ON CORROSION RATE

    </pre>";
  }

  // === HND I SECOND SEMESTER ===
  elseif ($level == "HNDI" && $semester == "second") {
    echo "<pre>
For HND I second semester
HIGHER NATIONAL DIPLOMA IN CHEMICAL ENGINEERING TECHNOLOGY
SECOND SEMESTER LABORATORY PRACTICAL 
COURSE: CHEMICAL ENGINEERING LABORATORY IV
COURSE CODE: CHE 308 					CONTACT HOURS: 6
DATE: WEDNESDAY 					TIME: 10:00 – 6:00 PM  

LEARNING RESOURCES (EQUIPMENT)
1. TRAY DRYER, ROTARY DRYER, VERTICAL PNEUMATIC DRYER 
2. PACKED DISTILLATION COLUMN, PLATE DISTILLATION COLUMN, 
3. PROCESS CONTROL TRAINING UNIT,
•	TEMPERATURE CONTROL APPARATUS,
4. SEDIMENTATION TANK.
5. GAS ABSORPTION EQUIPMENT
6. FLUIDIZED BED SYSTEMS.
7. CENTRIFUGAL PUMPS

EXPERIMENT NO.	TITLE OF EXPERIMENT	WEEK (s)
EXPERIMENT 1	DETERMINATION OF OVERALL DRYING RATE OF A TRAY DRYER, PNEUMATIC DRYER AND ROTARY DRYER	1
	Mode of operation of drying equipment’s
a) Tray dryer,
b) Rotary dryer and
c) vertical pneumatic dryer	
	Centrifugal Pumps	
EXPERIMENT 2	Operate centrifugal pumps, gear pumps, axial pumps and positive displacement pumps and measure their operating characteristics including:	2
A	Pump head and flow characteristics at constant speed	
B	Pump performance characteristics.	
C	Determination of the relationship between speed, flow, head and power consumption.	
	FIXED AND FLUIDIZED BED SYSTEM
	3 – 5
EXPERIMENT 3
A
	Determination of pressure drop through packed and fluidized beds for both air and water systems	
EXPERIMENT 3
B	Drying characteristics of a given material
	
	GAS ABSORPTION EQUIPMENT	
EXPERIMENT 4	To study the performance of packed bed during absorption process.
	6
	FLUID FLOW CIRCUIT SYSTEM (FLUID FRICTION APPARATUS)	
EXPERIMENT 5	Determination of energy losses, which occur when fluid flows
through pipefittings.	7
EXPERIMENT 6	Determination of drag coefficient for spheres.	8
	PROCESS CONTROL TRAINING UNIT	
EXPERIMENT 7	Determination of controller constant necessary to stabilize a thermal process using its signal curve.	9
EXPERIMENT 8	Operate a feedback control loop and examine the effect of the
control variable of temperature using a temperature control apparatus.	10
	PID CONTROLLER	
EXPERIMENT 9
	To be familiar with PID controller.
how changing PID controller parameter effect on system response	11
	SEDIMENTATION TANK	
EXPERIMENT 10	Determination of hydraulic characteristics of a model sedimentation tank, including short-circuiting, average retention times, holdback and flow profiles as a function of flow rate.	12
    </pre>";
  }

  // === HND II FIRST SEMESTER ===
  elseif ($level == "HNDII" && $semester == "first") {
    echo "<pre>
COURSE TITLE: CHEMICAL ENGINEERING LABORATORY V COURSE CODE: CHE 409
LEVEL: HND II (400)
SEMESTERS: First semester
HEAT TRANSFER II AND UNIT OPERATION
EXPERIMENT 1: To study the drying characteristics of a solid material under batch drying condition.
COOLING TOWER
EXPERIMENT 2: Study of the heat & mass transfer in Water Cooling Tower for different flow & thermodynamic conditions.

CHEMICAL REACTION ENGINEERING II
ISOTHERMAL OPERATION
EXPERIMENT 3: To find the reaction rate constant in a stirred batch reactor EXPERIMENT 4: To determine the effect of reactant concentration on the reaction rate ADIABATIC OPERATION
EXPERIMENT 5: To determine the rate equation for the hydrolysis of acetic anhydride to acetic acid in an adiabatic reactor.

CONTINUOUS STIRRED TANK REACTOR (CSTR)
EXPERIMENT 6: To study of a non-catalytic homogeneous second order liquid phase reaction in a C.
S.T.R under ambient conditions
EXPERIMENT 7: To determine the reaction rate constant for saponification of ethyl-acetate with NaOH at ambient conditions.
PLUG FLOW REACTOR
EXPERIMENT 8: To determine the rate constant for the saponification of ethyl acetate with NaOH at ambient temperature of 28°C using a Plug flow reactor.
MULTI-FUNCTIONAL PLATE DISTILLATION COLUMN
EXPERIMENT 9: To investigate the basic principles and calculation techniques of SIEVE PLATE Distillation and To determine the number of theoretical plates

pH MEASUREMENT AND ITS APPLICATIONS
Experiment 10:
A: To measure the pH of various solutions using pH indicators and meter. B: To determine the value of Ka for an unknown acid. And
C: To perform a pH titration (OPTIONAL, if time permits)
 
PROCESS CONTROL AND INSTRUMENTATION
•	PRESSURE PROCESS CONTROLLER
•	LEVEL PROCESS CONTROLLER
•	TEMPERATURE PROCESS CONTROLLER
•	STUDY OF COMPLEX CONTROL SYSTEM USING MATLAB
Experiment 11: To study the performance of ON-OFF/P/PI/PD/PID controllers on Pressure process.
Experiment 12: To study the performance of ON –OFF/P/PI/PD/PID controllers on level process Experiment 13: To study the performance of ON –OFF/P/PI/PD/PID controllers on flow process
Experiment 14: To study the performance of ON-OFF/P/PI/PD/PID controllers on temperature process.
Experiment 15: To study the complex control system using matlab and to compare the response of simple and cascade loop.
    </pre>";
  }

  else {
    echo "<p style='color:red;'>No experiments found for this selection.</p>";
  }

  echo "</div>";
}
?>
<div class="container py-5" style="margin-bottom:10rem">

</body>
</html>

<?php include 'includes/footer.php'; ?>
