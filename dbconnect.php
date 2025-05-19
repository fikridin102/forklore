<?php

// Database credentials
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'folklore_sem'; //Yes i accidentally named it folklore instead of FORKLORE, big apologies for that


// Create connection
$conn = new mysqli($host, $username, $password, $dbname);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>