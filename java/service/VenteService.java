package service;

import database.DatabaseConnection;
import model.Facture;
import model.LigneVente;
import model.VenteCommande;

import java.sql.*;

public class VenteService {

    public int enregistrerCommande(VenteCommande commande) throws SQLException {
        String sqlCommande = "INSERT INTO commandes (id_client, id_employe, statut) VALUES (?, ?, ?)";
        String sqlLigne    = "INSERT INTO lignes_commande (id_commande, id_produit, quantite, prix_applique) VALUES (?, ?, ?, ?)";

        Connection conn = DatabaseConnection.getConnection();
        conn.setAutoCommit(false);

        try {
            int idCommande;
            try (PreparedStatement ps = conn.prepareStatement(sqlCommande, Statement.RETURN_GENERATED_KEYS)) {
                ps.setInt(1, commande.getIdClient());
                ps.setInt(2, commande.getIdEmploye());
                ps.setString(3, commande.getStatut());
                ps.executeUpdate();

                try (ResultSet keys = ps.getGeneratedKeys()) {
                    if (!keys.next()) throw new SQLException("Echec creation commande.");
                    idCommande = keys.getInt(1);
                }
            }

            try (PreparedStatement ps = conn.prepareStatement(sqlLigne)) {
                for (LigneVente l : commande.getLignes()) {
                    ps.setInt(1, idCommande);
                    ps.setInt(2, l.getProduit().getIdProduit());
                    ps.setInt(3, l.getQuantite());
                    ps.setDouble(4, l.getPrixApplique());
                    ps.addBatch();
                }
                ps.executeBatch();
            }

            conn.commit();
            return idCommande;

        } catch (SQLException e) {
            conn.rollback();
            throw e;
        } finally {
            conn.setAutoCommit(true);
        }
    }

    public int enregistrerFacture(Facture facture) throws SQLException {
        String sql = "INSERT INTO factures (id_commande, tva, total_ht, total_ttc) VALUES (?, ?, ?, ?)";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {

            ps.setInt(1, facture.getIdCommande());
            ps.setDouble(2, facture.getTva());
            ps.setDouble(3, 0);
            ps.setDouble(4, 0);
            ps.executeUpdate();

            try (ResultSet keys = ps.getGeneratedKeys()) {
                if (!keys.next()) throw new SQLException("Echec creation facture.");
                return keys.getInt(1);
            }
        }
    }
}