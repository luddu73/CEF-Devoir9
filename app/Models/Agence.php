<?php
namespace Touchepasauklaxon\Models;

use Touchepasauklaxon\Database;
use PDO;
use PDOException;

class Agence {
    private \PDO $db;

    private ?string $lastError = null;

    public function __construct() {
        $this->db = Database::getConnection();
    }
    /** 
     * @return array<int, array<string, mixed>>|false $agences
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM agences");
            $stmt->execute();
        } catch (PDOException $e) {
            $sqlState = $e->getCode();

            switch ($sqlState) {
                case '42000':
                    $this->lastError = "Erreur de syntaxe dans la requête SQL.";
                    break;
                case '42S02':
                    $this->lastError = "Table ou colonne inexistante dans la base de données.";
                    break;
                default:
                    $this->lastError = "Une erreur technique est survenue. Veuillez réessayer plus tard.";
            }
            error_log("Erreur lors de l'export de la liste des agences : " . $e->getMessage());
            return false;
        }
        return $stmt->fetchAll();
    }
    /** 
     * @return array<int, array<string, mixed>>|false $agences
     */
    public function getById(int $id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM agences WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $sqlState = $e->getCode();

            switch ($sqlState) {
                case '42000':
                    $this->lastError = "Erreur de syntaxe dans la requête SQL.";
                    break;
                case '42S02':
                    $this->lastError = "Table ou colonne inexistante dans la base de données.";
                    break;
                default:
                    $this->lastError = "Une erreur technique est survenue. Veuillez réessayer plus tard.";
            }
            error_log("Erreur lors de l'export de l'agence : " . $e->getMessage());
            return false;
        }
    }

    public function deleteById(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM agences WHERE id = ?");
            return $stmt->execute([$id]);
        }
        catch (PDOException $e) {
            $sqlState = $e->getCode();

            switch ($sqlState) {
                case '23000': 
                    if (str_contains($e->getMessage(), '1451')) {
                        $this->lastError = "Cette agence est utilisée dans un trajet et ne peut pas être supprimée.";
                    } else {
                        $this->lastError = "Conflit avec une contrainte de base de données.";
                    }
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
            error_log("Erreur lors de la suppression de l'agence : " . $e->getMessage());
            return false;
        }
    }

    public function updateById(int $id, string $ville): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE agences SET ville = ? WHERE id = ?");
            return $stmt->execute([$ville, $id]);
        }
        catch (PDOException $e) {
            $sqlState = $e->getCode();

            switch ($sqlState) {
                case '23000': 
                    if (str_contains($e->getMessage(), '1062')) {
                        $this->lastError = "Cette agence existe déjà.";
                    } else {
                        $this->lastError = "Conflit avec une contrainte de base de données.";
                    }
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
            error_log("Erreur lors de la modification de l'agence : " . $e->getMessage());
            return false;
        }
    }

    public function add(string $ville): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO agences (ville) VALUES (?)");
            return $stmt->execute([$ville]);
        }
        catch (PDOException $e) {
            $sqlState = $e->getCode();

            switch ($sqlState) {
                case '23000': 
                    if (str_contains($e->getMessage(), '1062')) {
                        $this->lastError = "Cette agence existe déjà.";
                    } else {
                        $this->lastError = "Conflit avec une contrainte de base de données.";
                    }
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
            error_log("Erreur lors de l'ajout de l'agence : " . $e->getMessage());
            return false;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError ?? null;
    }
}