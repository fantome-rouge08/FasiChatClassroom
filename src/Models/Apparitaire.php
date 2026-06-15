<?php

class Apparitaire extends Utilisateur {
    private $db;

    public function __construct($db, $data = []) {
        parent::__construct($data);
        $this->db = $db;
    }

    // Publier une nouvelle annonce
    public function publierAnnonce($titre, $contenu) {
        $query = "INSERT INTO annonces (titre, contenu, auteur_id) VALUES (:titre, :contenu, :auteur_id)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'titre' => $titre,
            'contenu' => $contenu,
            'auteur_id' => $this->id
        ]);
    }

    // Supprimer une annonce
    public function supprimerAnnonce($idAnnonce) {
        $query = "DELETE FROM annonces WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $idAnnonce]);
    }

    // Modifier une annonce
    public function modifierAnnonce($idAnnonce, $titre, $contenu) {
        $query = "UPDATE annonces SET titre = :titre, contenu = :contenu WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'titre' => $titre,
            'contenu' => $contenu,
            'id' => $idAnnonce
        ]);
    }
}
