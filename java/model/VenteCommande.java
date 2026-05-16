package model;

import java.util.ArrayList;
import java.util.List;

public class VenteCommande {
    private int              idVente;
    private int              idClient;
    private int              idEmploye;
    private String           statut;
    private List<LigneVente> lignes;

    public VenteCommande(int idVente, int idClient, int idEmploye) {
        this.idVente   = idVente;
        this.idClient  = idClient;
        this.idEmploye = idEmploye;
        this.statut    = "EN_COURS";
        this.lignes    = new ArrayList<>();
    }

    public void ajouterLigne(LigneVente ligne) { lignes.add(ligne); }
    public void valider()  { this.statut = "VALIDEE"; }
    public void annuler()  { this.statut = "ANNULEE"; }

    public int              getIdVente()   { return idVente;   }
    public int              getIdClient()  { return idClient;  }
    public int              getIdEmploye() { return idEmploye; }
    public String           getStatut()    { return statut;    }
    public List<LigneVente> getLignes()    { return lignes;    }

    @Override
    public String toString() {
        return String.format("Vente #%d  Client: %d  Statut: %s",
                idVente, idClient, statut);
    }
}