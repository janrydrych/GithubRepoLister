<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace Tests\GRL;

require_once __DIR__ . '/../../../vendor/autoload.php';

use DateTime;
use GRL\Storage\DataStorage;
use PDO;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\TestCase;
use \RuntimeException;

/**
 * Tests for Base class
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class DataStorageTest extends TestCase
{
	/**
	 * @var PDO
	 */
	private $pdo;

	/**
	 * @var Connection
	 */
	private $connection;

	public function getConnection()
	{
		if (null === $this->connection) {
			if (null === $this->pdo) {
				$this->pdo = new PDO('sqlite::memory:');
			}
			$this->connection = $this->createDefaultDBConnection($this->pdo);
		}

		return $this->connection;
	}

	public function setUp()
	{
		$this->getConnection()
		     ->getConnection()
		     ->exec("CREATE TABLE IF NOT EXISTS " . DataStorage::SEARCH_LOG_TABLE_NAME . " (datetime TEXT NOT NULL, uri TEXT NOT NULL, ip_address TEXT)");

		parent::setUp();
	}

	public function tearDown()
	{
		$this->getConnection()
		     ->getConnection()
		     ->exec("DROP TABLE IF EXISTS ".DataStorage::SEARCH_LOG_TABLE_NAME);

		parent::tearDown();
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/initial.xml');
	}

	public function testSearchLogTableInitializationAndStructure()
	{
		$this->getConnection()
		     ->getConnection()
		     ->exec("DROP TABLE IF EXISTS " . DataStorage::SEARCH_LOG_TABLE_NAME);

		$dataStorage = new DataStorage($this->getConnection()->getConnection());

		$result = $this->getConnection()
		               ->getConnection()
		               ->query("SELECT datetime, uri, ip_address FROM  " . DataStorage::SEARCH_LOG_TABLE_NAME)
		               ->fetchAll();

		$this->assertNotFalse($result, 'constructor must initialize search log table if not exists');
	}

	public function testSearchLogTableInitializationThrowsException()
	{
		$mockPDO = $this->createMock(PDO::class);
		$mockPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		try {
			$dataStorage = new DataStorage($mockPDO);
			$this->fail('constructor must throw exception when search log table initialization fails');
		} catch (RuntimeException $e) {
			$this->assertInstanceOf(RuntimeException::class, $e, 'constructor must throw exception when search log table initialization fails');
		}
	}

	public function testSaveSearchEvent()
	{
		$dataStorage = new DataStorage($this->getConnection()
		                                    ->getConnection());
		$dataStorage->saveSearchEvent(new DateTime('2017-03-03 00:00:00'), 'http://acegik', '1.1.1.5');

		$actual = $this->getConnection()
		               ->createQueryTable(DataStorage::SEARCH_LOG_TABLE_NAME,
		                                  'SELECT * FROM ' . DataStorage::SEARCH_LOG_TABLE_NAME);

		$expected = $this->createXMLDataSet(__DIR__ . '/fixtures/expectedSaveSearchLog.xml')
		                 ->getTable(DataStorage::SEARCH_LOG_TABLE_NAME);

		$this->assertTablesEqual($expected, $actual, '->saveSearchEvent() must store the log of the event');
	}

	public function testCountSearchLogs()
	{
		$this->assertEquals(4,
		                    $this->getConnection()
		                         ->getRowCount(DataStorage::SEARCH_LOG_TABLE_NAME),
		                    'Pre condition must be met');

		$dataStorage = new DataStorage($this->getConnection()
		                                    ->getConnection());

		$this->assertEquals($this->getConnection()->getRowCount(DataStorage::SEARCH_LOG_TABLE_NAME), $dataStorage->countSearchLogs(), '->getRowCount() must return correct row count');
		$dataStorage->saveSearchEvent(new DateTime('2017-03-03 00:00:00'), 'http://acegik', '1.1.1.5');
		$this->assertEquals($this->getConnection()->getRowCount(DataStorage::SEARCH_LOG_TABLE_NAME), $dataStorage->countSearchLogs(), '->getRowCount() must return correct row count');
	}

	public function testGetSearchLogsReturnsCorrectRowCount()
	{
		$dataStorage = new DataStorage($this->getConnection()
		                                    ->getConnection());

		$actual = $dataStorage->getSearchLogs(999, 0);
		$this->assertCount(4, $actual, '->getSearchLogs() must return correct result');
	}

	public function testGetSearchLogsReturnsCorrectData()
	{
		$dataStorage = new DataStorage($this->getConnection()
		                                    ->getConnection());

		$actual = $dataStorage->getSearchLogs(2, 0);
		$expected = $this->getConnection()
		               ->createQueryTable(DataStorage::SEARCH_LOG_TABLE_NAME,
		                                  'SELECT * FROM ' . DataStorage::SEARCH_LOG_TABLE_NAME . ' ORDER BY datetime DESC LIMIT 2 OFFSET 0');

		foreach ($actual as $row) {
			$this->assertTrue($expected->assertContainsRow($row), '->getSearchLogs() must return correct result');
		}

		$actual = $dataStorage->getSearchLogs(1, 1);
		$expected = $this->getConnection()
		                 ->createQueryTable(DataStorage::SEARCH_LOG_TABLE_NAME,
		                                    'SELECT * FROM ' . DataStorage::SEARCH_LOG_TABLE_NAME . ' ORDER BY datetime DESC LIMIT 1 OFFSET 1');

		foreach ($actual as $row) {
			$this->assertTrue($expected->assertContainsRow($row), '->getSearchLogs() must return correct result');
		}
	}

	public function testDeleteSearchLogs()
	{
		$dataStorage = new DataStorage($this->getConnection()
		                                    ->getConnection());

		$deleteOlderThanDate = new DateTime('2015-12-10');
		$diff = $deleteOlderThanDate->diff(new DateTime());
		$diffInHours = $diff->days * 24 + $diff->h;

		$result = $dataStorage->deleteSearchLogs($diffInHours);

		$actual = $this->getConnection()
		               ->createQueryTable(DataStorage::SEARCH_LOG_TABLE_NAME,
		                                  'SELECT * FROM ' . DataStorage::SEARCH_LOG_TABLE_NAME);

		$expected = $this->createXMLDataSet(__DIR__ . '/fixtures/expectedDeleteSearchLogs.xml')
		                 ->getTable(DataStorage::SEARCH_LOG_TABLE_NAME);

		$this->assertTablesEqual($expected, $actual, '->deleteSearchLogs() must delete all search logs older than specified');
	}
}
