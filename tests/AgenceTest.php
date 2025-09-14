<?php
use PHPUnit\Framework\TestCase;
use Tests\Helpers\PdoMock;
use Touchepasauklaxon\Models\Agence;

final class AgenceTest extends TestCase
{
    /* ---------------------------
     * Tests pour getAll()
     * --------------------------- */

    /**
     * @testdox getAll() retourne un tableau de 2 agences avec les bonnes colonnes
     *
     * Objectif : vérifier que le modèle exécute la requête SELECT et renvoie un array
     * contenant les lignes telles que renvoyées par PDO, sans transformation indésirable.
     */
    public function testGetAllReturnsArrayOfAgences(): void
    {
        $sql = "SELECT * FROM agences";
        $rows = [
            ['id' => 1, 'ville' => 'Chambéry'],
            ['id' => 2, 'ville' => 'Modane'],
        ];
        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: null,
            fetchAllResult: $rows
        );

        $model = new Agence($pdo);
        $result = $model->getAll();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame('Chambéry', $result[0]['ville']);
        $this->assertSame('Modane', $result[1]['ville']);
    }

    /**
     * @testdox getAll() renvoie un tableau vide quand il n’y a aucune ligne
     *
     * Objectif : s’assurer que le modèle normalise correctement le cas sans résultats.
     */
    public function testGetAllReturnsEmptyArrayWhenNoRows(): void
    {
        $sql = "SELECT * FROM agences";

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: null,
            fetchAllResult: []
        );

        $model = new Agence($pdo);
        $result = $model->getAll();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }


    /* ---------------------------
     * Tests pour getById()
     * --------------------------- */

    /**
     * @testdox getById() retourne une agence typée (id int) quand la ligne est complète
     * Objectif :
     *  - vérifier la requête (id bindé avec ?),
     * - caster l'id string numérique en int,
     * - renvoyer un array non nul avec les clés attendues.
     */
    public function testGetByIdReturnsTypedAgence(): void
    {
        $sql = "SELECT * FROM agences WHERE id = ?";
        $params = [1];
        $row = [
            'id' => '1',
            'ville' => 'Chambéry'
        ];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params,
            fetchResult: $row
        );

        $model = new Agence($pdo);
        $agence = $model->getById(1);

        $this->assertIsArray($agence);
        $this->assertCount(1, $agence);
        $this->assertSame(1, $agence[0]['id']);
        $this->assertSame('Chambéry', $agence[0]['ville']);
    }

    /**
     * @testdox getById() renvoie un tableau vide quand l’id n’existe pas
     *
     * Objectif : si fetch() renvoie false, la méthode doit renvoyer [].
     */
    public function testGetByIdReturnsEmptyArrayWhenIdDoesNotExist(): void
    {
        $sql = "SELECT * FROM agences WHERE id = ?";
        $params = [999];
        $row = false;

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params,
            fetchResult: $row
        );

        $model = new Agence($pdo);
        $agence = $model->getById(999);

        $this->assertIsArray($agence);
        $this->assertCount(0, $agence);
    }
    /**
     * @testdox getById() renvoie un tableau vide si une colonne obligatoire manque
     *
     * Objectif : robustesse sur le schéma ; si `ville` (ou autre clé) manque, retour [].
     */
    public function testGetByIdReturnsEmptyArrayWhenRowMissingAField(): void
    {
        $sql = "SELECT * FROM agences WHERE id = ?";
        $params = [1];
        $row = [
            'id' => '1',
            // 'ville' manque
        ];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params,
            fetchResult: $row
        );

        $model = new Agence($pdo);
        $agence = $model->getById(1);

        $this->assertIsArray($agence);
        $this->assertCount(0, $agence);
    }
    /**
     * @testdox getById() renvoie un tableau vide si un type est invalide (ex: id non numérique)
     *
     * Objectif : si `id` n’est pas convertible en int, on renvoie [].
     */
    public function testGetByIdThrowsTypeErrorWhenIdNotInt(): void
    {
        $this->expectException(\TypeError::class);

        /** @var \PDO&\PHPUnit\Framework\MockObject\MockObject $pdo */
        $pdo = $this->createStub(\PDO::class); // pas de vraie connexion

        $model = new Agence($pdo);             // OK pour le constructeur
        /** @phpstan-ignore-next-line */       // on force l'appel invalide
        $model->getById('abc');
    }

    /* ---------------------------
     * Tests pour deleteById()
     * --------------------------- */

    /**
     * @testdox deleteById() supprime une agence existante
     * Objectif :
     *  - vérifier la requête (id bindé avec ?),
     *  - s'assurer que la méthode renvoie true en cas de succès.
     */
    public function testDeleteByIdRemovesExistingAgence(): void
    {
        $sql = "DELETE FROM agences WHERE id = ?";
        $params = [1];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params
        );

        $stmt->method('rowCount')->willReturn(1);

        $model = new Agence($pdo);
        $result = $model->deleteById(1);

        $this->assertTrue($result);
    }


    /**
     * @testdox deleteById() ne fait rien si l'agence n'existe pas
     * Objectif :
     *  - vérifier la requête (id bindé avec ?),
     *  - s'assurer que la méthode renvoie false si l'agence n'existe pas.
     */
    public function testDeleteByIdDoesNothingWhenAgenceNotFound(): void
    {
        $sql = "DELETE FROM agences WHERE id = ?";
        $params = [999];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            "DELETE FROM agences WHERE id = ?",
            executeWithParams: [999],
            fetchAllResult: [],
            fetchResult: false,
            rowCount: 0
        );

        $pdo->expects($this->once())->method('prepare')->with($sql)->willReturn($stmt);

        $stmt->expects($this->once())->method('execute')->with($params)->willReturn(true);
        $stmt->expects($this->once())->method('rowCount')->willReturn(0);

        $model = new Agence($pdo);
        $this->assertFalse($model->deleteById(999));
    }



    /**
     * @testdox deleteById() utilise bien le bind `?`
     * Objectif : test “structurel” de la requête et des paramètres de bind. On s’en fiche
     * du résultat final, c’est le mock qui valide que la bonne requête/params ont été utilisés.
     */
    public function testDeleteByIdBindsIdParam(): void
    {
        $sql = "DELETE FROM agences WHERE id = ?";
        $params = [1];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params
        );

        $stmt->method('rowCount')->willReturn(1);

        $model = new Agence($pdo);
        $ok = $model->deleteById(1);

        // On s’en fiche du résultat mais on évite un test sans assertion
        $this->assertIsBool($ok);
    }

    

    /* ---------------------------
     * Tests pour updateById()
     * --------------------------- */

    /**
     * @testdox updateById() modifie une agence existante
     * Objectif :
     *  - vérifier la requête (id bindé avec ?),
     *  - s'assurer que la méthode renvoie true en cas de succès.
     */
    public function testUpdateByIdModifiesExistingAgence(): void
    {
        $sql = "UPDATE agences SET ville = ? WHERE id = ?";
        $params = ['New York', 1];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params
        );

        $stmt->method('rowCount')->willReturn(1);

        $model = new Agence($pdo);
        $result = $model->updateById(1, 'New York');

        $this->assertTrue($result);
    }

    /**
     * @testdox updateById() utilise bien le bind `?`
     * Objectif : test “structurel” de la requête et des paramètres de bind. On s’en fiche
     * du résultat final, c’est le mock qui valide que la bonne requête/params ont été utilisés.
     */
    public function testUpdateByIdBindsParams(): void
    {
        $sql = "UPDATE agences SET ville = ? WHERE id = ?";
        $params = ['New York', 1];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params
        );

        $stmt->method('rowCount')->willReturn(1);

        $model = new Agence($pdo);
        $ok = $model->updateById(1, 'New York');

        // On s’en fiche du résultat mais on évite un test sans assertion
        $this->assertIsBool($ok);
    }
    /**
     * @testdox updateById() renvoie false si l'agence n'existe pas
     * Objectif : vérifier la requête (id bindé avec ?), s'assurer que la méthode renvoie false si l'agence n'existe pas.
     */
    public function testUpdateByIdReturnsFalseWhenAgenceNotFound(): void
    {
        $sql = "UPDATE agences SET ville = ? WHERE id = ?";
        $params = ['New York', 999];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params,
            fetchAllResult: [],
            fetchResult: false,
            rowCount: 0
        );

        $pdo->expects($this->once())->method('prepare')->with($sql)->willReturn($stmt);

        $stmt->expects($this->once())->method('execute')->with($params)->willReturn(true);
        $stmt->expects($this->once())->method('rowCount')->willReturn(0);

        $model = new Agence($pdo);
        $this->assertFalse($model->updateById(999, 'New York'));
    }
    /**
     * @testdox updateById() renvoie false si une colonne obligatoire manque
     */
    public function testUpdateByIdReturnsFalseWhenRequiredColumnMissing(): void
    {
        /** @var \PDO&\PHPUnit\Framework\MockObject\MockObject $pdo */
        $pdo = $this->createMock(\PDO::class); // inerte, aucune attente SQL
        $model = new Agence($pdo);

        $this->assertFalse($model->updateById(1, ''));
        $this->assertSame('La ville est obligatoire.', $model->getLastError());
    }

    /** 
     * -----------------------------
     * Tests pour add()
     * ----------------------------
     */
    /**
     * @testdox add() ajoute une nouvelle agence
     * Objectif :
     *  - vérifier la requête (ville bindée avec ?),
     * - s'assurer que la méthode renvoie true en cas de succès.
     */
    public function testAddCreatesNewAgence(): void
    {
        $sql = "INSERT INTO agences (ville) VALUES (?)";
        $params = ['Los Angeles'];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params
        );

        $stmt->method('rowCount')->willReturn(1);

        $model = new Agence($pdo);
        $result = $model->add('Los Angeles');

        $this->assertTrue($result);
    }
    /**
     * @testdox add() utilise bien le bind `?`
     * Objectif : test “structurel” de la requête et des paramètres de bind. On s’en fiche
     * du résultat final, c’est le mock qui valide que la bonne requête/params ont été utilisés.
     */
    public function testAddBindsVilleParam(): void
    {
        $sql = "INSERT INTO agences (ville) VALUES (?)";
        $params = ['Los Angeles'];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params
        );

        $stmt->method('rowCount')->willReturn(1);

        $model = new Agence($pdo);
        $ok = $model->add('Los Angeles');

        $this->assertIsBool($ok);
    }
    /**
     * @testdox add() renvoie false si une colonne obligatoire manque
     */
    public function testAddReturnsFalseWhenRequiredColumnMissing(): void
    {
        /** @var \PDO&\PHPUnit\Framework\MockObject\MockObject $pdo */
        $pdo = $this->createMock(\PDO::class); // inerte, aucune attente SQL
        $model = new Agence($pdo);

        $this->assertFalse($model->add(''));
        $this->assertSame('La ville est obligatoire.', $model->getLastError());
    }
    /**
     * @testdox add() renvoie false si l'agence existe déjà (doublon)
     * Objectif : simuler une exception PDO pour contrainte d’unicité (code SQL 23000)
     */
    public function testAddReturnsFalseWhenAgenceAlreadyExists(): void
    {
        $sql = "INSERT INTO agences (ville) VALUES (?)";
        $params = ['Chambéry'];

        ['pdo' => $pdo, 'stmt' => $stmt] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: $params
        );

        $exception = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'Chambéry' for key 'ville'", '23000');
        $stmt->method('execute')->willThrowException($exception);

        $model = new Agence($pdo);
        $result = $model->add('Chambéry');

        $this->assertFalse($result);
        $this->assertSame('Cette agence existe déjà.', $model->getLastError());
    }
}