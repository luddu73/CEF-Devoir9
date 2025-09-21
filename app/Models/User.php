<?php
/**
 * Modèle pour la gestion des utilisateurs.
 *
 * Fournit des méthodes pour interagir avec la table `users`
 * dans la base de données.
 *
 * @category User
 * @package  TouchePasAuKlaxon
 */
namespace Touchepasauklaxon\Models;

use Touchepasauklaxon\Database;
use PDO;

class User {
    private PDO $db;

    /**
     * Constructeur de la classe User.
     *
     * @param PDO|null $db Instance PDO pour la connexion à la base de données.
     *                     Si null, une nouvelle connexion sera créée.
     */
    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Récupère tous les utilisateurs.
     * 
     * @return array<int, array{
     *   id:int,
     *   nom:string,
     *   prenom:string,
     *   email:string,
     *   tel:string,
     *   password:string,
     *   isAdmin:int
     * }>
     */
    public function getAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM users");
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $rows;
    }

    /**
     * Récupère un utilisateur par son email.
     * 
     * @return array{
     *   id:int,
     *   nom:string,
     *   prenom:string,
     *   email:string,
     *   tel:string,
     *   password:string,
     *   isAdmin:int
     * }|null
     */
    public function getByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($row)
            && isset($row['id'], $row['nom'], $row['prenom'], $row['email'], $row['tel'], $row['password'], $row['isAdmin'])
            && is_numeric($row['id'])
            && is_string($row['nom'])
            && is_string($row['prenom'])
            && is_string($row['email'])
            && is_string($row['tel'])
            && is_string($row['password'])
            && is_numeric($row['isAdmin'])
        ) {
            return [
                'id' => (int)$row['id'],
                'nom' => (string)$row['nom'],
                'prenom' => (string)$row['prenom'],
                'email' => (string)$row['email'],
                'tel' => (string)$row['tel'],
                'password' => (string)$row['password'],
                'isAdmin' => (int)$row['isAdmin'],
            ];
        }
        return null;
    }
}
