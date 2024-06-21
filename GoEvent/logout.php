<?php
session_start();

// Regenerate session ID to avoid session fixation attacks
session_regenerate_id(true);

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to the login page
header("Location: index.php");
exit();
?>
