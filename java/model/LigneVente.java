package model;

public class LigneVente {
    private Produit produit;
    private int     quantite;
    private double  prixApplique;

    public LigneVente(Produit produit, int quantite) {
        this.produit      = produit;
        this.quantite     = quantite;
        this.prixApplique = produit.getPrix();
    }

    public Produit getProduit()      { return produit; }
    public int     getQuantite()     { return quantite; }
    public double  getPrixApplique() { return prixApplique; }

    @Override
    public String toString() {
        return String.format("%-20s x%-3d", produit.getNom(), quantite);
    }
}