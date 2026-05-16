<?php
$host = "localhost";
$dbname = "stox_db";
$user = "root";
$pass = "";

try {
    $conn = new PDO("mysql:host=localhost;charset=utf8mb4", "root", "");
    $conn->exec("CREATE DATABASE IF NOT EXISTS stox_db");
    $conn->exec("USE stox_db");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom_complet VARCHAR(100),
        login VARCHAR(50) UNIQUE,
        password VARCHAR(255),
        role ENUM('admin','employe','client') DEFAULT 'client',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS produits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100),
        designation VARCHAR(100),
        categorie VARCHAR(50),
        prix DOUBLE,
        stock INT DEFAULT 0
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS commandes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_client INT,
        id_employe INT,
        statut VARCHAR(20)
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS lignes_commande (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_commande INT,
        id_produit INT,
        quantite INT,
        prix_applique DOUBLE
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS factures (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_commande INT,
        tva DOUBLE,
        total_ht DOUBLE,
        total_ttc DOUBLE
    )");

} catch (PDOException $e) {
    echo "erreur de connexion";
}
?>