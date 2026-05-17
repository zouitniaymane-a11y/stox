<?php
// db.php — Connexion PDO + création automatique de la base et de la table

$host = "localhost";
$dbname = "stox_db";
$user = "root";
$pass = "";

try {
    // 1) On se connecte SANS préciser de base pour pouvoir la créer si elle n'existe pas
    $conn = new PDO("mysql:host=localhost;dbname=stox_db","root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   

 }catch (PDOException $e) {
   echo"ereur de conexion";
 }
?>
