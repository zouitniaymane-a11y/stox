package model;

public class Produit {
    private int    idProduit;
    private String nom;
    private String designation;
    private double prix;
    private int    quantiteDispoEnStock;

    public Produit(int idProduit, String nom, String designation, double prix, int quantiteDispoEnStock) {
        this.idProduit            = idProduit;
        this.nom                  = nom;
        this.designation          = designation;
        this.prix                 = prix;
        this.quantiteDispoEnStock = quantiteDispoEnStock;
    }

    public Produit(int idProduit, String nom, double prix, int quantiteDispoEnStock) {
        this(idProduit, nom, nom, prix, quantiteDispoEnStock);
    }

    public int    getIdProduit()            { return idProduit; }
    public String getNom()                  { return nom; }
    public String getDesignation()          { return designation; }
    public double getPrix()                 { return prix; }
    public int    getQuantiteStock()        { return quantiteDispoEnStock; }
    public int    getQuantiteDispoEnStock() { return quantiteDispoEnStock; }

    public void setQuantiteDispoEnStock(int q) { this.quantiteDispoEnStock = q; }

    public boolean diminuerStock(int quantite) {
        if (quantite > quantiteDispoEnStock) {
            System.out.println("Stock insuffisant pour : " + nom);
            return false;
        }
        quantiteDispoEnStock -= quantite;
        return true;
    }

    @Override
    public String toString() {
        return String.format("[%d] %-20s  Prix: %.2f DH  Stock: %d",
                idProduit, nom, prix, quantiteDispoEnStock);
    }
}