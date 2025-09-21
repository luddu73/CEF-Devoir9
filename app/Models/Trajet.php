<?php
/**
 * Modèle pour la gestion des trajets.
 *
 * Fournit des méthodes pour interagir avec la table `trajets`
 * dans la base de données.
 *
 * @category Trajet
 * @package  TouchePasAuKlaxon
 */

namespace Touchepasauklaxon\Models;

use Touchepasauklaxon\Database;
use PDO;
use DateTime;
use PDOException;

class Trajet {
    private \PDO $db;

    private ?string $lastError = null;

    /**
     * Constructeur de la classe Trajet.
     *
     * @param PDO|null $db Instance PDO pour la connexion à la base de données.
     *                     Si null, une nouvelle connexion sera créée.
     */
    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getConnection();
    }

     /** 
      * Récupère tous les trajets.
      *
      * @return array<int, array{
      *   id:int,
      *   auteur:int,
      *   auteur_nom:string,
      *   auteur_prenom:string,
      *   date_depart:string,
      *   date_destination:string,
      *   agence_depart:string,
      *   agence_destination:string,
      *   places:int,
      *   places_disponibles:int
      * }>|false $trajets
      */
    public function getAll() {
        try{
            $stmt = $this->db->prepare("
                SELECT t.id, t.auteur, u.nom AS auteur_nom, u.prenom AS auteur_prenom, t.date_depart, t.date_destination, a1.ville AS agence_depart, a2.ville AS agence_destination, t.places, t.places_disponibles 
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id
                ORDER BY t.date_depart ASC");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) {
                if (!empty($r['date_depart']))       { $r['date_depart']       = new DateTime($r['date_depart']); }
                if (!empty($r['date_destination']))  { $r['date_destination']  = new DateTime($r['date_destination']); }
            }
            return $rows;

        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des trajets : " . $e->getMessage());
            $this->lastError = "Une erreur technique est survenue lors de la récupération des trajets. Veuillez réessayer plus tard.";
            return [];
        }
    }

    /** 
     * Récupère les trajets à afficher sur la page d'accueil (futurs avec places dispo).
     *
      * @return array<int, array{
      *   id:int,
      *   auteur:int,
      *   auteur_nom:string,
      *   auteur_prenom:string,
      *   date_depart:string,
      *   date_destination:string,
      *   agence_depart:string,
      *   agence_destination:string,
      *   places:int,
      *   places_disponibles:int
      * }>|false $trajets
      */
    public function getAccueil() {
        try{
            $stmt = $this->db->prepare("
                SELECT t.id, t.auteur, u.nom AS auteur_nom, u.prenom AS auteur_prenom, t.date_depart, t.date_destination, a1.ville AS agence_depart, a2.ville AS agence_destination, t.places, t.places_disponibles 
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id
                WHERE t.date_depart >= NOW() AND t.places_disponibles > 0 ORDER BY t.date_depart ASC");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) {
                if (!empty($r['date_depart']))       { $r['date_depart']       = new DateTime($r['date_depart']); }
                if (!empty($r['date_destination']))  { $r['date_destination']  = new DateTime($r['date_destination']); }
            }
            return $rows;

        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des trajets : " . $e->getMessage());
            $this->lastError = "Une erreur technique est survenue lors de la récupération des trajets. Veuillez réessayer plus tard.";
            return [];
        }
    }
    
    /** 
     * Récupère un trajet par son ID.
     * 
     * @param int $id ID du trajet à récupérer.
     * 
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
     *   places:int,
     *   places_disponibles:int,
     *   agence_depart_ville:string,
     *   agence_destination_ville:string,
     * }|null
     */
    public function getById(int $id) {
        try {
            $stmt = $this->db->prepare("
                SELECT t.id, t.auteur, u.nom AS auteur_nom, u.prenom AS auteur_prenom, u.tel AS auteur_tel, u.email AS auteur_email, t.date_depart, t.date_destination, a1.ville AS agence_depart_ville, a2.ville AS agence_destination_ville, t.places, t.places_disponibles, t.agence_depart, t.agence_destination
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id
                WHERE t.id = ?");

            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($result) || !isset($result['id']) || !isset($result['auteur']) || !isset($result['date_depart']) || !isset($result['date_destination']) || !isset($result['agence_depart']) || !isset($result['agence_destination']) || !isset($result['auteur_nom']) || !isset($result['auteur_prenom']) || !isset($result['auteur_tel']) || !isset($result['auteur_email']) || !isset($result['places']) || !isset($result['places_disponibles'])) {
                return null;
            }
            if (!is_numeric($result['id']) || !is_numeric($result['places']) || !is_numeric($result['places_disponibles'])) {
                return null;
            }
            return [
                'id' => (int)$result['id'],
                'auteur' => (int)$result['auteur'],
                'date_depart' => new DateTime($result['date_depart']),
                'date_destination' => new DateTime($result['date_destination']),
                'agence_depart' => (int)$result['agence_depart'],
                'agence_destination' => (int)$result['agence_destination'],
                'agence_depart_ville' => (string)$result['agence_depart_ville'],
                'agence_destination_ville' => (string)$result['agence_destination_ville'],
                'auteur_nom' => (string)$result['auteur_nom'],
                'auteur_prenom' => (string)$result['auteur_prenom'],
                'auteur_tel' => (string)$result['auteur_tel'],
                'auteur_email' => (string)$result['auteur_email'],
                'places' => (int)$result['places'],
                'places_disponibles' => (int)$result['places_disponibles']
            ];



        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du trajet : " . $e->getMessage());
            $this->lastError = "Une erreur technique est survenue lors de la récupération du trajet. Veuillez réessayer plus tard.";
            return null;
        }
    }

    /** 
     * Supprime un trajet par son ID.
     * 
     * @param int $id ID du trajet à supprimer.
     * 
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
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

    /** 
     * Met à jour un trajet par son ID.
     * 
     * @param int $id ID du trajet à mettre à jour.
     * @param int $userId ID de l'utilisateur auteur du trajet.
     * @param DateTime $date_depart Nouvelle date et heure de départ.
     * @param DateTime $date_destination Nouvelle date et heure de destination.
     * @param int $places Nombre total de places.
     * @param int $places_disponibles Nombre de places disponibles.
     * @param int $agenceDepartId ID de l'agence de départ.
     * @param int $agenceDestinationId ID de l'agence de destination.
     * 
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function updateById(int $id, int $userId, DateTime $date_depart, DateTime $date_destination, int $places, int $places_disponibles, int $agenceDepartId, int $agenceDestinationId): bool {
        try {
            $stmt = $this->db->prepare("UPDATE trajets SET auteur = ?, date_depart = ?, date_destination = ?, places = ?, places_disponibles = ?, agence_depart = ?, agence_destination = ? WHERE id = ?");
         $ok = $stmt->execute([$userId, $date_depart->format('Y-m-d H:i:s'), $date_destination->format('Y-m-d H:i:s'), $places, $places_disponibles, $agenceDepartId, $agenceDestinationId, $id]);
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
     * Crée un nouveau trajet.
     * 
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

    /**
     * Retourne le dernier message d'erreur.
     * 
     * @return string|null Message d'erreur ou null s'il n'y a pas d'erreur.
     */
    public function getLastError(): ?string {
        return $this->lastError ?? null;
    }
}