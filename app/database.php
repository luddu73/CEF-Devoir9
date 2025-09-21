<?php
/**
 * Classe de gestion de la connexion à la base de données via PDO.
 *
 * Centralise l'ouverture de la connexion et permet de la réutiliser
 * dans les différents modèles.
 *
 * @category Database
 * @package  TouchePasAuKlaxon
 */

namespace Touchepasauklaxon;

use PDO;
use PDOException;

/**
 * Class Database
 * Fournit une connexion PDO unique pour l'application.
 */
class Database {
    private static ?PDO $instance = null;

    /**
     * Retourne une instance PDO connectée à la base de données.
     *
     * @return PDO Instance de PDO configurée pour la BDD.
     *
     * @throws PDOException En cas d'échec de la connexion.
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';

            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

            try {
                self::$instance = new PDO($dsn, $config['user'], $config['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
