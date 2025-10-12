<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Chemical Engineering Dept</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="/chem_coursework_pro/assets/style.css">
<link rel="stylesheet" href="/css/style.css">

</head>
<body class="bg-light"><nav class="navbar navbar-expand-lg navbar-dark bg-primary fs-4 p-4">
<div class="container"><a class="navbar-brand fs-2" href="/chem_coursework-pro/index.php">CHE-Dept</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="nav"><ul class="navbar-nav ms-auto">
<li class="nav-item"><a class="nav-link text-light  " href="/chem_coursework-pro/about.php">About</a></li>
<li class="nav-item  "><a class="nav-link text-light " href="/chem_coursework-pro/personnel.php">Personnnels</a></li>
<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Department Laboratories
          </a>
          <ul class="dropdown-menu">
            <li><a  class="dropdown-item" href="/chem_coursework-pro/mass_transfer_lab.php" target="_self" rel="noope"  >Mass Transfer Operations</a></li>
            <li><a class="dropdown-item" href="/chem_coursework-pro/heat_transfer_lab.php"  target="_self" rel="noope">Heat Transfer Operations</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/chem_coursework-pro/fluid_transfer_lab.php"  target="_self" rel="noope">Fluid and Particles Mechanics</a></li>
            <li><a class="dropdown-item" href="/chem_coursework-pro/instrument_transfer_lab.php"  target="_self" rel="noope">Instrumentation and Process Control</a></li>
            <li><a class="dropdown-item" href="/chem_coursework-pro/chemical_reaction_transfer_lab.php"  target="_self" rel="noope">Chemical Reaction Engineering</a></li>
            <li><a class="dropdown-item" href="/chem_coursework-pro/corrosion_material_transfer_lab.php"  target="_self" rel="noope">Corrosion and Material Science</a></li>
            <li><a class="dropdown-item" href="/chem_coursework-pro/computer_simulation_transfer_lab.php"  target="_self" rel="noope">Computer Simulation Lab</a></li>
            
          </ul>
        </li>
<li class="nav-item"><a class="nav-link text-light hover:bg-danger " href="/chem_coursework-pro/experiments.php"> Experiments</a></li>
<li class="nav-item"><a class="nav-link text-light  " href="/chem_coursework-pro/coursework.php"> Course Work</a></li>
<li class="nav-item"><a class="nav-link text-light  " href="/chem_coursework-pro/contacts.php"> Contacts</a></li>
<li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Mannual
          </a>
          <ul class="dropdown-menu">
            <li><a  class="dropdown-item" href="/chem_coursework-pro/documents/hnd11.pdf" target="_self" rel="noope"  >HND11</a></li>
            <li><a class="dropdown-item" href="/chem_coursework-pro/documents/hnd1.pdf"  target="_self" rel="noope">HND1</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/chem_coursework-pro/documents/nd11.pdf"  target="_self" rel="noope">ND11</a></li>
          </ul>
        </li>
</ul></div></div></nav><div class="container py-4">

    