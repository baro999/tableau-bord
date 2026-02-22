<?php
// index.php
require_once 'includes/functions.php';

if (estConnecte()) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>