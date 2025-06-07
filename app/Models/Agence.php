<?php
namespace Touchepasauklaxon\Models;

use Touchepasauklaxon\Database;
use PDO;
use PDOException;

class Agence {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM agences");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM agences WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteById(int $id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM agences WHERE id = ?");
            return $stmt->execute([$id]);
        }
        catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'agence : " . $e->getMessage());
            return false;
        }
    }

    public function updateById(int $id, string $ville) {
        try {
            $stmt = $this->db->prepare("UPDATE agences SET ville = ? WHERE id = ?");
            return $stmt->execute([$ville, $id]);
        }
        catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'agence : " . $e->getMessage());
            return false;
        }
    }
}