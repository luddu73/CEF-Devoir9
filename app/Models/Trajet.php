<?php
namespace Touchepasauklaxon\Models;

use Touchepasauklaxon\Database;
use PDO;
use DateTime;
use PDOException;

class Trajet {
    private \PDO $db;

    private ?string $lastError = null;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getConnection();
    }

     /** 
      * @return array<int, array{
      *   id:int,
      *   auteur:int,
      *   auteur_nom:string,
      *   auteur_prenom:string,
      *   date_depart:string,
      *   date_destination:string,
      *   agence_depart:string,
      *   agence_destination:string,
      *   places:int
      * }>|false $trajets
      */
    public function getAll() {
        try{
            $stmt = $this->db->prepare("
                SELECT t.id, t.auteur, u.nom AS auteur_nom, u.prenom AS auteur_prenom, t.date_depart, t.date_destination, a1.ville AS agence_depart, a2.ville AS agence_destination, t.places 
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
    
    /** 
     * @return array{
     *   id:int,
     *   auteur:int,
     *   date_depart:DateTime,
     *   date_destination:DateTime,
     *   agence_depart:int,
     *   agence_destination:int,
     *   auteur_nom:string,
     *   auteur_prenom:string,
     *   auteur_tel:string,
     *   auteur_email:string,
     *   places:int
     * }|null
     */
    public function getById(int $id) {
        try {
            $stmt = $this->db->prepare("
                SELECT t.id, t.auteur, u.nom AS auteur_nom, u.prenom AS auteur_prenom, u.tel AS auteur_tel, u.email AS auteur_email, t.date_depart, t.date_destination, a1.ville AS agence_depart, a2.ville AS agence_destination, t.places 
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id
                WHERE t.id = ?");

            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($result) || !isset($result['id']) || !isset($result['auteur']) || !isset($result['date_depart']) || !isset($result['date_destination']) || !isset($result['agence_depart']) || !isset($result['agence_destination']) || !isset($result['auteur_nom']) || !isset($result['auteur_prenom']) || !isset($result['auteur_tel']) || !isset($result['auteur_email']) || !isset($result['places'])) {
                return null;
            }
            if (!is_numeric($result['id']) || !is_numeric($result['places'])) {
                return null;
            }
            return [
                'id' => (int)$result['id'],
                'auteur' => (int)$result['auteur'],
                'date_depart' => new DateTime($result['date_depart']),
                'date_destination' => new DateTime($result['date_destination']),
                'agence_depart' => (int)$result['agence_depart'],
                'agence_destination' => (int)$result['agence_destination'],
                'auteur_nom' => (string)$result['auteur_nom'],
                'auteur_prenom' => (string)$result['auteur_prenom'],
                'auteur_tel' => (string)$result['auteur_tel'],
                'auteur_email' => (string)$result['auteur_email'],
                'places' => (int)$result['places']
            ];



        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du trajet : " . $e->getMessage());
            $this->lastError = "Une erreur technique est survenue lors de la récupération du trajet. Veuillez réessayer plus tard.";
            return null;
        }
    }

    public function deleteById(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM trajets WHERE id = ?");
            $ok = $stmt->execute([$id]);
            if (!$ok) {
                return false;
            }
            return $stmt->rowCount() > 0;
        }
        catch (PDOException $e) {
            error_log("Erreur lors de la suppression du trajet : " . $e->getMessage());
            $this->lastError = "Une erreur technique est survenue lors de la suppression du trajet. Veuillez réessayer plus tard.";
            return false;
        }

    }

    public function updateById(int $id, int $userId, DateTime $date_depart, DateTime $date_destination, int $places, int $agenceDepartId, int $agenceDestinationId): bool {
        try {
            $stmt = $this->db->prepare("UPDATE trajets SET auteur = ?, date_depart = ?, date_destination = ?, places = ?, agence_depart = ?, agence_destination = ? WHERE id = ?");
         $ok = $stmt->execute([$userId, $date_depart->format('Y-m-d H:i:s'), $date_destination->format('Y-m-d H:i:s'), $places, $agenceDepartId, $agenceDestinationId, $id]);
         if(!$ok) {
            $this->lastError = "Échec de la mise à jour du trajet.";
             return false;
         }

         if($stmt->rowCount() === 0) {
            $this->lastError = "Aucun trajet mis à jour (ID peut-être inexistant ou données identiques).";
            return false;
         }
        return true;

        } 
        catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du trajet : " . $e->getMessage());
            $this->lastError = "Une erreur technique est survenue lors de la mise à jour du trajet. Veuillez réessayer plus tard.";
            return false;
        }
    }

    /** 
     * @param int $userId
     * @param DateTime $date_depart
     * @param DateTime $date_destination
     * @param int $places
     * @param int $agenceDepartId
     * @param int $agenceDestinationId
     * @return bool
     */

    public function create(int $userId, DateTime $date_depart, DateTime $date_destination, int $places, int $agenceDepartId, int $agenceDestinationId): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO trajets (auteur, date_depart, date_destination, places, agence_depart, agence_destination) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$userId, $date_depart->format('Y-m-d H:i:s'), $date_destination->format('Y-m-d H:i:s'), $places, $agenceDepartId, $agenceDestinationId]);
        } 
        catch (PDOException $e) {
            $sqlState = $e->getCode();

            switch ($sqlState) {
                case 'HY000':
                    $this->lastError = "Conflit avec une contrainte de base de données.";
                    break;
                case '42000':
                    $this->lastError = "Erreur de syntaxe dans la requête SQL.";
                    break;
                case '42S02':
                    $this->lastError = "Table ou colonne inexistante dans la base de données.";
                    break;
                default:
                    $this->lastError = "Une erreur technique est survenue. Veuillez réessayer plus tard.";
            }
            error_log("Erreur lors de la création du trajet : " . $e->getMessage());
            return false;
        }
    }

    public function getLastError(): ?string {
        return $this->lastError ?? null;
    }
}