<?php
require_once 'includes/auth.php';
startSession();

if (isset($_SESSION['user_id'])) {
    header('Location: tickets/dashboard.php');
} else {
    header('Location: login.php');
}
exit();