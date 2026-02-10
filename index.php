<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: views/home.php');
} else {
    header('Location: auth/login.php');
}
exit;


