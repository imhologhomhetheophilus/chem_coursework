<?php
// Prevent output issues and safely start session
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="keywords" content="fedpolychem">
  <meta name="description" content="federal polytechnic nasarawa department of chemical engineering technology coursework submission portal">

  <!-- Open Graph -->
  <meta property="og:title" content="Federal Polytechnic Nasarawa" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://fedpolychem.onrender.com" />
  <meta property="og:image" content="https://fedpolychem.onrender.com/assets/chem_logo.png" />

  <!-- Google Verification -->
  <meta name="google-site-verification" content="federal polytechnic nasarawa" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- AOS Animation -->
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

  <!-- Custom Styles -->
  <link rel="stylesheet" href="/chem_coursework_pro/assets/style.css">
  <link rel="stylesheet" href="/css/style.css">

  <!-- Favicon -->
  <link rel="shortcut icon" href="/assets/chem_logo.png" type="image/x-icon">

  <title>Chemical Engineering Dept</title>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
    .navbar {
      background: #001F1F;
    }
    .navbar-brand {
      font-weight: 600;
    }
    .nav-link:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body class="bg-light">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fs-4 shadow-sm">
    <div class="container">
      <div class="d-md-flex m-3" data-aos="fade-down">
        <a href="index.php">
          <img src="/assets/fpn_logo.png" class="img-fluid rounded m-3" alt="logo" style="width:50px">
        </a>
      </div>
      <a class="navbar-brand fs-2" href="index.php">CHE-Dept</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link text-light" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link text-light" href="personnel.php">Personnel</a></li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-light" href="#" data-bs-toggle="dropdown">Department Laboratories</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="mass_transfer_lab.php">Mass Transfer Operations</a></li>
              <li><a class="dropdown-item" href="heat_transfer_lab.php">Heat Transfer Operations</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="fluid_transfer_lab.php">Fluid & Particle Mechanics</a></li>
              <li><a class="dropdown-item" href="instrument_transfer_lab.php">Instrumentation & Process Control</a></li>
              <li><a class="dropdown-item" href="chemical_reaction_transfer_lab.php">Chemical Reaction Engineering</a></li>
              <li><a class="dropdown-item" href="corrosion_material_transfer_lab.php">Corrosion & Material Science</a></li>
              <li><a class="dropdown-item" href="computer_simulation_transfer_lab.php">Computer Simulation Lab</a></li>
            </ul>
          </li>

          <li class="nav-item"><a class="nav-link text-light" href="experiments.php">Experiments</a></li>
          <li class="nav-item"><a class="nav-link text-light" href="contacts.php">Contacts</a></li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-light" href="#" data-bs-toggle="dropdown">Manual</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="documents/hnd11.pdf" target="_blank">HND II</a></li>
              <li><a class="dropdown-item" href="documents/hnd1.pdf" target="_blank">HND I</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="documents/nd11.pdf" target="_blank">ND II</a></li>
            </ul>
          </li>
        </ul>
      </div>

      <div class="d-none d-md-flex" data-aos="fade-left">
        <a href="index.php">
          <img src="/assets/chem_logo.png" class="img-fluid rounded m-4" alt="dept logo" style="width:50px">
        </a>
      </div>
    </div>
  </nav>

  <div class="container py-4">
