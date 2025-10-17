<!-- Include Bootstrap CSS in your <head> if not already -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  /* Ensure footer stays at bottom even if content is short */
  html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
  }

  main {
    flex: 1; /* Pushes footer to bottom */
  }

  footer {
    background-color: #111827; /* Dark navy/black tone */
    color: #f8f9fa;
    padding: 20px 0;
    width: 100%;
  }

  footer a {
    color: #f8f9fa;
    text-decoration: none;
  }

  footer a:hover {
    color: #ffc107;
  }

  footer small {
    display: block;
    line-height: 1.5;
  }
</style>

<main>
  <!-- Your page content goes here -->
</main>

<!-- Footer Section -->
<footer class="text-light mt-auto">
  <div class="container-fluid px-4">
    <div class="row gy-3 align-items-center text-center text-md-start">

      <!-- Left: Department Info -->
      <div class="col-12 col-md-4">
        <small class="fw-bold text-warning d-block">Department of Chemical Engineering Technology</small>
        <small>Federal Polytechnic, Nasarawa</small>
        <small>PMB 001, Nasarawa, Nasarawa State</small>
        <small>
          <a href="https://www.fpn.edu.ng/chet">fpn.edu.ng/chet</a> |
          <a href="https://www.fpn.edu.ng/set">fpn.edu.ng/set</a>
        </small>
        <small>engineeringtech@fpn.edu.ng | chetfpn@fpn.edu.ng</small>
        <small>Tel: 09019110605, 08136243784</small>
      </div>

      <!-- Middle: Quick Links -->
      <div class="col-12 col-md-4">
        <a href="index.php" class="mx-2 small">Home</a> |
        <a href="about.php" class="mx-2 small">About</a> |
        <a href="contacts.php" class="mx-2 small">Contact</a>
      </div>

      <!-- Right: Copyright -->
      <div class="col-12 col-md-4">
        <small>
          &copy; <script>document.write(new Date().getFullYear());</script>
          Dept. of Chemical Engineering Tech.
        </small>
        <small class="text-warning">Web Dev Team collaboration with Job Jacob</small>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
