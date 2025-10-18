<?php
<?php
session_start();
session_destroy();
header('Location: group_login.php');
exit;
