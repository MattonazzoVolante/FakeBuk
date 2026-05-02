<?php
/* 
Questo è lo script che connette al database
basta includerlo per connettersi
*/

$DB_servername = "localhost";
$DB_username = "root";
$DB_password = "";
$dbname = "my_testwebsitefkb"; //<- SE IL DATABASE NON SI CONNETTE, assicurati di avergli messo questo nome

// Create connection
$db = new mysqli($DB_servername, $DB_username, $DB_password, $dbname);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>