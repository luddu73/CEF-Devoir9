<?php
namespace Touchepasauklaxon\Models;

use Touchepasauklaxon\Database;
use PDO;

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
        // Si possible, fais aussi ça dans Database::getConnection():
        // $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
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
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }
}
