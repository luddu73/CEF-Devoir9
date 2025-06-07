<?php
namespace Touchepasauklaxon\Models;

use Touchepasauklaxon\Database;
use PDO;
use DateTime;
use PDOException;

class Trajet {
    private $db;

    private $lastError;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        try{
            $stmt = $this->db->prepare("
                SELECT t.id, u.nom AS auteur_nom, u.prenom AS auteur_prenom, t.date_depart, t.date_destination, a1.ville AS ville_depart, a2.ville AS ville_arrivee, t.places 
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des trajets : " . $e->getMessage());
            $this->lastError = "Une erreur technique est survenue lors de la récupération des trajets. Veuillez réessayer plus tard.";
            return [];
        }
    }

    public function getById(int $id) {
        try {
            $stmt = $this->db->prepare("
                SELECT t.id, u.nom AS auteur_nom, u.prenom AS auteur_prenom, t.date_depart, t.date_destination, a1.ville AS ville_depart, a2.ville AS ville_arrivee, t.places 
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id
                WHERE t.id = ?");
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du trajet : " . $e->getMessage());
            $this->lastError = "Une erreur technique est survenue lors de la récupération du trajet. Veuillez réessayer plus tard.";
            return null;
        }
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteById(int $id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM trajets WHERE id = ?");
            return $stmt->execute([$id]);
        }
        catch (PDOException $e) {
            error_log("Erreur lors de la suppression du trajet : " . $e->getMessage());
            $this->lastError = "Une erreur technique est survenue lors de la suppression du trajet. Veuillez réessayer plus tard.";
            return false;
        }

    }

    public function updateById(int $id, int $userId, DateTime $date_depart, DateTime $date_destination, int $places, int $agenceDepartId, int $agenceDestinationId) {
        try {
            $stmt = $this->db->prepare("UPDATE trajets SET auteur = ?, date_depart = ?, date_destination = ?, places = ?, agence_depart = ?, agence_destination = ? WHERE id = ?");
         return $stmt->execute([$userId, $date_depart->format('Y-m-d H:i:s'), $date_destination->format('Y-m-d H:i:s'), $places, $agenceDepartId, $agenceDestinationId, $id]);
        } 
        catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du trajet : " . $e->getMessage());
            $this->lastError = "Une erreur technique est survenue lors de la mise à jour du trajet. Veuillez réessayer plus tard.";
            return false;
        }
    }

    public function getLastError() {
        return $this->lastError ?? null;
    }
}