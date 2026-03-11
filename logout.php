<?php
// Destroy session and redirect 
session_start();
session_destroy();
header('Location: index.php');
exit;