<?php
use PHPUnit\Framework\TestCase;
use Tests\Helpers\PdoMock;
use Touchepasauklaxon\Models\User;

final class UserTest extends TestCase
{
    /* ---------------------------
     * Tests pour getAll()
     * --------------------------- */

    public function testGetAllReturnsArrayOfUsers(): void
    {
        $sql = "SELECT * FROM users";
        $rows = [
            ['id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean@ex.fr', 'tel' => '0600000000', 'password' => 'hash', 'isAdmin' => 0],
            ['id' => 2, 'nom' => 'Durand', 'prenom' => 'Marie', 'email' => 'marie@ex.fr', 'tel' => '0611111111', 'password' => 'hash2', 'isAdmin' => 1],
        ];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: null,
            fetchAllResult: $rows
        );

        $model = new User($pdo);
        $result = $model->getAll();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame('Dupont', $result[0]['nom']);
        $this->assertSame('Marie', $result[1]['prenom']);
    }

    public function testGetAllReturnsEmptyArrayWhenNoRows(): void
    {
        $sql = "SELECT * FROM users";

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            executeWithParams: null,
            fetchAllResult: []
        );

        $model = new User($pdo);
        $result = $model->getAll();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    /* ---------------------------
     * Tests pour getByEmail()
     * --------------------------- */

    public function testGetByEmailReturnsTypedUser(): void
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $params = [':email' => 'jean@ex.fr'];
        $row = [
            'id' => '10',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean@ex.fr',
            'tel' => '0600000000',
            'password' => 'hashxxx',
            'isAdmin' => '1',
        ];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            $params,
            fetchAllResult: [],
            fetchResult: $row
        );

        $model = new User($pdo);
        $user = $model->getByEmail('jean@ex.fr');

        $this->assertNotNull($user);
        $this->assertSame(10, $user['id']);
        $this->assertSame('Dupont', $user['nom']);
        $this->assertSame(1, $user['isAdmin']);
    }

    public function testGetByEmailReturnsNullWhenNotFound(): void
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $params = [':email' => 'absent@ex.fr'];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            $params,
            fetchAllResult: [],
            fetchResult: false
        );

        $model = new User($pdo);
        $user = $model->getByEmail('absent@ex.fr');

        $this->assertNull($user);
    }

    public function testGetByEmailReturnsNullWhenRowMissingAField(): void
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $params = [':email' => 'incomplet@ex.fr'];
        $row = [
            'id' => 3,
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'incomplet@ex.fr',
            'tel' => '0600000000',
            // 'password' manquant
            'isAdmin' => 0,
        ];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            $params,
            fetchAllResult: [],
            fetchResult: $row
        );

        $model = new User($pdo);
        $user = $model->getByEmail('incomplet@ex.fr');

        $this->assertNull($user);
    }

    public function testGetByEmailReturnsNullWhenTypeInvalid(): void
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $params = [':email' => 'type@ex.fr'];
        $row = [
            'id' => 5,
            'nom' => 'Type',
            'prenom' => 'Bad',
            'email' => 'type@ex.fr',
            'tel' => '0600000000',
            'password' => 'hash',
            'isAdmin' => 'not-a-number',   // invalide -> doit renvoyer null
        ];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            $params,
            fetchAllResult: [],
            fetchResult: $row
        );

        $model = new User($pdo);
        $user = $model->getByEmail('type@ex.fr');

        $this->assertNull($user);
    }

    public function testGetByEmailBindsEmailParamAndUsesLimit(): void
    {
        // Ce test vérifie l’exactitude de la requête *et* du bind param
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $params = [':email' => 'verif@ex.fr'];

        ['pdo' => $pdo] = PdoMock::build(
            $this,
            $sql,
            $params,
            fetchAllResult: [],
            fetchResult: false
        );

        $model = new User($pdo);
        $user = $model->getByEmail('verif@ex.fr');

        $this->assertNull($user); // peu importe le résultat, on voulait valider SQL + bind
    }
}
