<?php
// MUHAMMAD AFIQ IRSYAD | 2024429922
$servername = "localhost";
$username = "root"; // PHPMyAdmin username
$password = "";     // PHPMyAdmin password (empty default)
$dbname = "mobrevapp"; // Database name 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed : " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>