package main;

import database.DatabaseConnection;
import java.sql.Connection;

public class Main {

    public static void main(String[] args) {

        Connection conn = DatabaseConnection.getConnection();

        if (conn != null) {
            System.out.println("Connexion réussie à MySQL !");
        } else {
            System.out.println("Erreur connexion");
        }
    }
}