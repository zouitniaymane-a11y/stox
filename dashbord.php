<?php
session_start();

if (!isset($_SESSION["utilisateur"])) {
    header("Location: login.php");
    exit();
}

$produits = [
    ["id" => 1, "nom" => "Ordinateur portable", "prix" => 8500, "stock" => 12, "categorie" => "Informatique"],
    ["id" => 2, "nom" => "Souris sans fil",     "prix" => 150,  "stock" => 45, "categorie" => "Informatique"],
    ["id" => 3, "nom" => "Clavier mécanique",   "prix" => 350,  "stock" => 30, "categorie" => "Informatique"],
    ["id" => 4, "nom" => "Écran 24 pouces",     "prix" => 2200, "stock" => 8,  "categorie" => "Informatique"],
    ["id" => 5, "nom" => "Chaise de bureau",    "prix" => 1200, "stock" => 5,  "categorie" => "Mobilier"],
    ["id" => 6, "nom" => "Bureau en bois",      "prix" => 1800, "stock" => 3,  "categorie" => "Mobilier"],
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Stox</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: #1a1a1a;
            color: #f0f0f0;
        }

        /* ---- NAVBAR ---- */
        .navbar {
            background-color: #111111;
            padding: 16px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #c9a84c;
        }

        .navbar h1 {
            font-size: 20px;
            color: #c9a84c;
            letter-spacing: 2px;
        }

        .navbar .user-info {
            font-size: 13px;
            color: #888;
        }

        .navbar .user-info b {
            color: #c9a84c;
        }

        .navbar a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
            margin-left: 20px;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .navbar a:hover {
            color: #ff6b6b;
        }

        /* ---- CONTAINER ---- */
        .container {
            padding: 30px;
        }

        /* ---- CARDS STATS ---- */
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 35px;
        }

        .card-stat {
            background-color: #242424;
            border-radius: 10px;
            padding: 22px 28px;
            flex: 1;
            border: 1px solid #333;
            border-left: 4px solid #c9a84c;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .card-stat h3 {
            font-size: 12px;
            color: #888;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .card-stat p {
            font-size: 28px;
            font-weight: bold;
            color: #c9a84c;
        }

        /* ---- TABLEAU ---- */
        .table-section h2 {
            color: #c9a84c;
            margin-bottom: 15px;
            font-size: 18px;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #242424;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #333;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        thead {
            background-color: #111;
            border-bottom: 2px solid #c9a84c;
        }

        thead th {
            padding: 14px 18px;
            text-align: left;
            font-size: 12px;
            color: #c9a84c;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        tbody tr {
            border-bottom: 1px solid #2e2e2e;
            transition: background 0.2s;
        }

        tbody tr:hover {
            background-color: #2c2c2c;
        }

        tbody td {
            padding: 13px 18px;
            font-size: 14px;
            color: #ccc;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .badge-info     { background-color: #1a2a3a; color: #5dade2; border: 1px solid #2e86c1; }
        .badge-mobilier { background-color: #1a2e1a; color: #58d68d; border: 1px solid #27ae60; }

        .stock-bas {
            color: #e74c3c;
            font-weight: bold;
        }

        .stock-ok {
            color: #58d68d;
            font-weight: bold;
        }

        /* ---- FOOTER ---- */
        .footer {
            text-align: center;
            padding: 20px;
            color: #444;
            font-size: 12px;
            margin-top: 40px;
            border-top: 1px solid #2e2e2e;
        }

        .footer span {
            color: #c9a84c;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <div class="navbar">
        <h1>🏪 STOX — GESTION DE MAGASIN</h1>
        <div>
            <span class="user-info">Connecté : <b><?php echo $_SESSION["utilisateur"]; ?></b></span>
            <a href="logout.php">🔴 DÉCONNEXION</a>
        </div>
    </div>

    <!-- CONTENU -->
    <div class="container">

        <!-- STATS -->
        <div class="stats">
            <div class="card-stat">
                <h3>Total Produits</h3>
                <p><?php echo count($produits); ?></p>
            </div>
            <div class="card-stat">
                <h3>Total Stock</h3>
                <p><?php
                    $totalStock = 0;
                    foreach ($produits as $p) {
                        $totalStock += $p["stock"];
                    }
                    echo $totalStock;
                ?></p>
            </div>
            <div class="card-stat">
                <h3>Valeur Totale</h3>
                <p><?php
                    $totalValeur = 0;
                    foreach ($produits as $p) {
                        $totalValeur += $p["prix"] * $p["stock"];
                    }
                    echo number_format($totalValeur, 0, ',', ' ') . " DH";
                ?></p>
            </div>
        </div>

        <!-- TABLEAU -->
        <div class="table-section">
            <h2>📦 LISTE DES PRODUITS</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom du Produit</th>
                        <th>Catégorie</th>
                        <th>Prix (DH)</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $produit): ?>
                    <tr>
                        <td><?php echo $produit["id"]; ?></td>
                        <td><?php echo $produit["nom"]; ?></td>
                        <td>
                            <span class="badge <?php echo $produit['categorie'] == 'Informatique' ? 'badge-info' : 'badge-mobilier'; ?>">
                                <?php echo $produit["categorie"]; ?>
                            </span>
                        </td>
                        <td><?php echo number_format($produit["prix"], 0, ',', ' ') . " DH"; ?></td>
                        <td class="<?php echo $produit['stock'] <= 5 ? 'stock-bas' : 'stock-ok'; ?>">
                            <?php echo $produit["stock"]; ?>
                            <?php echo $produit['stock'] <= 5 ? ' ⚠️' : ' ✅'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- FOOTER -->
    <div class="footer">
        © 2025 <span>STOX</span> — Tous droits réservés
    </div>

</body>
</html>