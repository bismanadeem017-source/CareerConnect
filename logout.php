
<?php

require_once __DIR__ . '/includes/functions.php';

// Clear all session variables
$_SESSION = [];

// Destroy the active session
session_destroy();

// Start a fresh session for the logout message
session_start();

// Display logout confirmation
set_flash('You have been logged out successfully.');

// Redirect to homepage
redirect('index.php');

