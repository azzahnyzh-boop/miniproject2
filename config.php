<?php
// Database connection configuration
$servername = "localhost";   
$username   = "root";        
$password   = "";            
$dbname     = "pcrs";        
$port       = 3306;          

// Create a new MySQL connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection status
if ($conn->connect_error) {
    // If connection fails, stop execution and show error message
    die("Connection failed: " . $conn->connect_error);
}
?>
