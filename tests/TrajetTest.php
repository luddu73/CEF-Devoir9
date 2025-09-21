<?php
use PHPUnit\Framework\TestCase;
use Tests\Helpers\PdoMock;
use Touchepasauklaxon\Models\Trajet;

final class TrajetTest extends TestCase
{
    /* ---------------------------
     * Tests pour getAll()
     * --------------------------- */

    /**
     * @testdox getAll() retourne un tableau de 2 trajets avec les bonnes colonnes
     *
     * Objectif : vérifier que le modèle exécute la requête SELECT et renvoie un array
     * contenant les lignes telles que renvoyées par PDO, sans transformation indésirable.
     */
    public function testGetAllReturnsArrayOfTrajets(): void
    {
        $sql = "
                SELECT t.id, t.auteur, u.nom AS auteur_nom, u.prenom AS auteur_prenom, t.date_depart, t.date_destination, a1.ville AS agence_depart, a2.ville AS agence_destination, t.places, t.places_disponibles 
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id
                ORDER BY t.date_depart ASC";
        $rows = [
            [
                'id' => 1,
                'auteur' => 1,
                'auteur_nom' => 'Dupont',
                'auteur_prenom' => 'Jean',
                'date_depart' => '2024-07-01 10:00:00',
                'date_destination' => '2024-07-01 12:00:00',
                'agence_depart' => 'Paris',
                'agence_destination' => 'Lyon',
                'places' => 3,
                'places_disponibles' => 2
            ],
            [
                'id' => 2,
                'auteur' => 2,
                'auteur_nom' => 'Durand',
                'auteur_prenom' => 'Marie',
                'date_depart' => '2024-07-02 14:00:00',
                'date_destination' => '2024-07-02 16:00:00',
                'agence_depart' => 'Marseille',
                'agence_destination' => 'Nice',
                'places' => 2,
                'places_disponibles' => 1
            ],
        ];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: null,
            fetchAllResult: $rows
        );

        $model = new Trajet($pdo);
        $result = $model->getAll();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame('Dupont', $result[0]['auteur_nom']);
        $this->assertSame('Marie', $result[1]['auteur_prenom']);
    }
    /**
     * @testdox getAll() renvoie un tableau vide quand il n’y a aucune ligne
     *
     * Objectif : s’assurer que le modèle normalise correctement le cas sans résultats.
     */
    public function testGetAllReturnsEmptyArrayWhenNoRows(): void
    {
        $sql = "
                SELECT t.id, t.auteur, u.nom AS auteur_nom, u.prenom AS auteur_prenom, t.date_depart, t.date_destination, a1.ville AS agence_depart, a2.ville AS agence_destination, t.places, t.places_disponibles 
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id
                ORDER BY t.date_depart ASC";
        $rows = [];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: null,
            fetchAllResult: $rows
        );

        $model = new Trajet($pdo);
        $result = $model->getAll();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /* ---------------------------
     * Tests pour getById()
     * --------------------------- */
    /**
     * @testdox getById() retourne un trajet avec les bonnes colonnes quand l'ID existe
     * Objectif : vérifier que le modèle exécute la requête SELECT avec le bon paramètre
     * et renvoie une ligne telle que renvoyée par PDO, sans transformation indésirable.
     */
    public function testGetByIdReturnsTrajetWhenIdExists(): void
    {
        $sql = "
        SELECT t.id, t.auteur, u.nom AS auteur_nom, u.prenom AS auteur_prenom, u.tel AS auteur_tel, u.email AS auteur_email, t.date_depart, t.date_destination, a1.ville AS agence_depart_ville, a2.ville AS agence_destination_ville, t.places, t.places_disponibles, t.agence_depart, t.agence_destination
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id
                WHERE t.id = ?";

        $row = [
            'id' => 1,
            'auteur' => 1,
            'auteur_nom' => 'Dupont',
            'auteur_prenom' => 'Jean',
            'auteur_tel' => '0600000000',
            'auteur_email' => 'jean.dupont@example.com',
            'date_depart' => '2024-07-01 10:00:00',
            'date_destination' => '2024-07-01 12:00:00',
            'agence_depart_ville' => 'Paris',
            'agence_destination_ville' => 'Lyon',
            'agence_depart' => '1',
            'agence_destination' => '2',

            'places' => 3,
            'places_disponibles' => 2
        ];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: [1],
            fetchResult: $row
        );

        $model = new Trajet($pdo);
        $result = $model->getById(1);

        $this->assertNotNull($result);
        $this->assertIsArray($result);

        $this->assertSame(1, $result['id']);
        $this->assertSame('Dupont', $result['auteur_nom']);
        $this->assertSame('Jean', $result['auteur_prenom']);
        $this->assertSame('0600000000', $result['auteur_tel']);
        $this->assertSame('jean.dupont@example.com', $result['auteur_email']);

        // Dates: vérifier l'objet et le format
        $this->assertInstanceOf(\DateTime::class, $result['date_depart']);
        $this->assertInstanceOf(\DateTime::class, $result['date_destination']);
        $this->assertSame('2024-07-01 10:00:00', $result['date_depart']->format('Y-m-d H:i:s'));
        $this->assertSame('2024-07-01 12:00:00', $result['date_destination']->format('Y-m-d H:i:s'));

        // Agences: si tu renvoies les villes (id) :
        $this->assertSame(1, $result['agence_depart']);
        $this->assertSame(2, $result['agence_destination']);

        $this->assertSame('Paris', $result['agence_depart_ville']);
        $this->assertSame('Lyon', $result['agence_destination_ville']);

        $this->assertSame(3, $result['places']);
        $this->assertSame(2, $result['places_disponibles']);
    }
    /**
     * @testdox getById() retourne null quand l'ID n'existe pas
     *
     * Objectif : s’assurer que le modèle renvoie null quand la requête ne trouve pas de ligne.
     */
    public function testGetByIdReturnsNullWhenIdDoesNotExist(): void
    {
        $sql = "
        SELECT t.id, t.auteur, u.nom AS auteur_nom, u.prenom AS auteur_prenom, u.tel AS auteur_tel, u.email AS auteur_email, t.date_depart, t.date_destination, a1.ville AS agence_depart_ville, a2.ville AS agence_destination_ville, t.places, t.places_disponibles, t.agence_depart, t.agence_destination
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id
                WHERE t.id = ?";

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: [999], // ID qui n'existe pas
            fetchResult: false
        );

        $model = new Trajet($pdo);
        $result = $model->getById(999);

        $this->assertNull($result);
    }
    /**
     * @testdox getById() retourne null quand la ligne est incomplète (colonnes manquantes)
     *
     * Objectif : s’assurer que le modèle renvoie null quand la ligne est incomplète.
     */
    public function testGetByIdReturnsNullWhenRowIsIncomplete(): void
    {
        $sql = "SELECT t.id, t.auteur, u.nom AS auteur_nom, u.prenom AS auteur_prenom, u.tel AS auteur_tel, u.email AS auteur_email, t.date_depart, t.date_destination, a1.ville AS agence_depart_ville, a2.ville AS agence_destination_ville, t.places, t.places_disponibles, t.agence_depart, t.agence_destination
                FROM trajets t 
                JOIN users u ON t.auteur = u.id 
                JOIN agences a1 ON t.agence_depart = a1.id 
                JOIN agences a2 ON t.agence_destination = a2.id
                WHERE t.id = ?";
        $params = [1];
        $row = [
            'id' => '1',
            'auteur' => '1',
            'date_depart' => '2024-07-01 10:00:00',
            'date_destination' => '2024-07-01 12:00:00',
            'agence_depart' => '1',
            'agence_destination' => '2',
            // 'auteur_nom' manque
            'auteur_prenom' => 'Jean',
            'auteur_tel' => '0600000000',
            'auteur_email' => 'jean.dupont@example.com',
            'places' => '3',
            'places_disponibles' => '2'
        ];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params,
            fetchResult: $row
        );

        $model = new Trajet($pdo);
        $agence = $model->getById(1);

        $this->assertNull($agence);
    }
    
    /**
     * =======================
     * Tests pour deleteById()
     * =======================
     */
    /**
     * @testdox deleteById() retourne true quand la suppression réussit
     *
     * Objectif : s’assurer que le modèle renvoie true quand la suppression est effectuée avec succès.
     */
    public function testDeleteByIdReturnsTrueWhenDeletionSucceeds(): void
    {
        $sql = "DELETE FROM trajets WHERE id = ?";
        $params = [1];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params
        );

        $stmt->method('rowCount')->willReturn(1);

        $model = new Trajet($pdo);
        $result = $model->deleteById(1);

        $this->assertTrue($result);
    }
    /**
     * @testdox deleteById() retourne false quand la suppression échoue
     *
     * Objectif : s’assurer que le modèle renvoie false quand la suppression échoue.
     */
    public function testDeleteByIdReturnsFalseWhenDeletionFails(): void
    {
        $sql = "DELETE FROM trajets WHERE id = ?";
        $params = [999];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            "DELETE FROM trajets WHERE id = ?",
            executeWithParams: [999],
            fetchAllResult: [],
            fetchResult: false,
            rowCount: 0
        );

        $pdo->expects($this->once())->method('prepare')->with($sql)->willReturn($stmt);

        $stmt->expects($this->once())->method('execute')->with($params)->willReturn(true);
        $stmt->expects($this->once())->method('rowCount')->willReturn(0);

        $model = new Trajet($pdo);
        $this->assertFalse($model->deleteById(999));
    }
    /**
     * =======================
     * Tests pour updateById()
     * =======================
     */
    /**
     * @testdox updateById() retourne true quand la mise à jour réussit
     */
    public function testUpdateByIdReturnsTrueWhenUpdateSucceeds(): void
    {
        $sql = "UPDATE trajets SET auteur = ?, date_depart = ?, date_destination = ?, places = ?, places_disponibles = ?, agence_depart = ?, agence_destination = ? WHERE id = ?";

        $dateDepart = new DateTime('2024-07-01 10:00:00');
        $dateDest   = new DateTime('2024-07-01 12:00:00');

        $params = [1, 1, $dateDepart, $dateDest, 3, 2, 1, 2];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: [
                1,
                $dateDepart->format('Y-m-d H:i:s'),
                $dateDest->format('Y-m-d H:i:s'),
                3, 2, 1, 2, 1
            ]
        );

        $stmt->method('execute')->with([
            1, // userId (auteur)
            $dateDepart->format('Y-m-d H:i:s'),
            $dateDest->format('Y-m-d H:i:s'),
            3, // places
            2, // places_disponibles
            1, // agence_depart
            2, // agence_destination
            1, // id (WHERE)
        ])->willReturn(true);

        $model = new Trajet($pdo);

        $this->assertTrue($model->updateById(...$params));
    }

    /**
     * =======================
     * Tests pour create()
     * =======================
     */
    /**
     * @testdox create() retourne true quand la création réussit
     */
    public function testCreateReturnsTrueWhenCreationSucceeds(): void
    {
        $sql = "INSERT INTO trajets (auteur, date_depart, date_destination, places, agence_depart, agence_destination) VALUES (?, ?, ?, ?, ?, ?)";
        $dateDepart = new DateTime('2024-07-01 10:00:00');
        $dateDest   = new DateTime('2024-07-01 12:00:00');

        $expectedParams = [1, '2024-07-01 10:00:00', '2024-07-01 12:00:00', 3, 1, 2];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $expectedParams
        );

        $stmt->method('execute')->with($expectedParams)->willReturn(true);

        $model = new Trajet($pdo);

        $result = $model->create(
            $expectedParams[0],
            $dateDepart,
            $dateDest,
            $expectedParams[3],
            $expectedParams[4],
            $expectedParams[5]
        );

        $this->assertTrue($result);
    }
}