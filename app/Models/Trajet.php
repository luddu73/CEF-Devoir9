<?php
namespace Touchepasauklaxon\Models;

use Touchepasauklaxon\Database;
use PDO;
use DateTime;
use PDOException;

class Trajet {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM trajets");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM trajets WHERE id = ?");
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
            return false;
        }
    }
}