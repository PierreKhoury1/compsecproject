

<?php
$localserver = "localhost"; // this is the mysqli server (default for xampp)
$username = "webuser"; // this is the default username prompeted for xammp
$password = "password123"; // will leave this blank
$database = "lovejoy"; //this is the database name



$connection = new mysqli($localserver, $username, $password, $database);

if ($connection -> connect_error) {
    die("Connection to '$database' failed: " . $connection-> connect_error);
}

echo "Connected successfully to $database!";

?>


