<?php
session_start();

if (!isset($_SESSION['user_id']) &&  !isset($_COOKIE['remember_token'])) {
		header('Location: login.php');
		exit;
}

echo 'Logged in as user ID: ' . $_SESSION['user_id'];

