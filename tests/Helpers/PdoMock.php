<?php
namespace Tests\Helpers;

use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PdoMock
{
    /**
     * On construit un mock PDO + un mock PDOStatement lié à un SQL précis.
     *
     * @param TestCase $test
     * @param string   $expectedSql       SQL attendu dans prepare()
     * @param array    $executeWithParams Tableau de params prévu pour execute()
     * @param array    $fetchAllResult    Données renvoyées par fetchAll()
     * @param array    $fetchResult       Donnée renvoyée par fetch()
     * @param int      $rowCount          Valeur renvoyée par rowCount()
     * @return array{pdo:PDO&MockObject,stmt:PDOStatement&MockObject}
     */
    public static function build(
        TestCase $test,
        string $expectedSql,
        ? array $executeWithParams = null,
        array $fetchAllResult = [],
        array|false $fetchResult = [],
        int $rowCount = 1
    ): array {
        /** @var PDOStatement&MockObject $stmt */
        $stmt = $test->getMockBuilder(PDOStatement::class)->disableOriginalConstructor()->getMock();

        // execute() vérifie les paramètres
        $stmt->expects($test->once())
            ->method('execute')
            ->with($executeWithParams)
            ->willReturn(true);

        // fetchAll()
        $stmt->method('fetchAll')->willReturn($fetchAllResult);
        // fetch()
        $stmt->method('fetch')->willReturn($fetchResult);
        // rowCount()
        $stmt->method('rowCount')->willReturn($rowCount);

        /** @var PDO&MockObject $pdo */
        $pdo = $test->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['prepare', 'lastInsertId'])
            ->getMock();

        $pdo->expects($test->once())
            ->method('prepare')
            ->with($test->callback(function ($sql) use ($expectedSql) {
                // Tolérance légère aux espaces / retours ligne
                $norm = static fn($s) => trim(preg_replace('/\s+/', ' ', $s));
                return $norm($sql) === $norm($expectedSql);
            }))
            ->willReturn($stmt);

        $pdo->method('lastInsertId')->willReturn('42');

        return ['pdo' => $pdo, 'stmt' => $stmt];
    }
}
