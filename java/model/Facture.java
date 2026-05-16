package model;

public class Facture {
    private int           numFacture;
    private VenteCommande commande;
    private double        tva;

    public Facture(int numFacture, VenteCommande commande, double tva) {
        this.numFacture = numFacture;
        this.commande   = commande;
        this.tva        = tva;
    }

    public int    getNumFacture()   { return numFacture; }
    public int    getIdCommande()   { return commande.getIdVente(); }
    public double getTva()          { return tva; }

    public void afficher() {
        System.out.println("=======================================");
        System.out.println("  FACTURE N " + numFacture);
        System.out.println("=======================================");
        for (LigneVente l : commande.getLignes()) {
            System.out.println("  " + l);
        }
        System.out.println("=======================================");
    }
}