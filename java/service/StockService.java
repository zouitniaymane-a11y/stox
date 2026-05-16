package service;

import database.DatabaseConnection;
import model.Produit;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class StockService {

    public List<Produit> getTousProduits() throws SQLException {
        List<Produit> produits = new ArrayList<>();
        String sql = "SELECT id, nom, designation, prix, stock FROM produits ORDER BY id";

        try (Connection conn = DatabaseConnection.getConnection();
             Statement  stmt = conn.createStatement();
             ResultSet  rs   = stmt.executeQuery(sql)) {

            while (rs.next()) {
                produits.add(new Produit(
                    rs.getInt("id"),
                    rs.getString("nom"),
                    rs.getString("designation"),
                    rs.getDouble("prix"),
                    rs.getInt("stock")
                ));
            }
        }
        return produits;
    }

    public boolean diminuerStock(Produit produit, int quantite) throws SQLException {
        if (!produit.diminuerStock(quantite)) return false;

        String sql = "UPDATE produits SET stock = ? WHERE id = ?";
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setInt(1, produit.getQuantiteStock());
            ps.setInt(2, produit.getIdProduit());
            return ps.executeUpdate() > 0;
        }
    }
}