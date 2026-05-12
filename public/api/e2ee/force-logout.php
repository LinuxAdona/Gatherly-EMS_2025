<?php
/**
 * Force logout and redirect to signin
 */
session_start();
session_destroy();
header('Location: /public/pages/signin.php');
exit;