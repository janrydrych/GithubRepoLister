<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL\Storage;

use \PDO;
use \DateTime;
use \RuntimeException;

/**
 * Data storage for storing/retrieving data
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class DataStorage implements DataStorageInterface
{
	const SEARCH_LOG_TABLE_NAME = 'search_logs';

	/**
	 * @var PDO
	 */
	private $pdo;

	/**
	 * DataStorage constructor.
	 *
	 * @param PDO $pdo
	 * @throws RuntimeException
	 */
	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;

		$result = $this->initSearchLogTable();
		if ($result === false) {
			throw new RuntimeException('Searches log table initialization error');
		}
	}

	/**
	 * Initialize search log table in the storage
	 * @return bool
	 */
	private function initSearchLogTable(): bool
	{
		$result = $this->pdo->query("CREATE TABLE IF NOT EXISTS ".self::SEARCH_LOG_TABLE_NAME." (datetime TEXT NOT NULL, uri TEXT NOT NULL, ip_address TEXT)");
		if (!$result || $result === false) { return false; }
		$result = $this->pdo->query("CREATE INDEX IF NOT EXISTS idx_datetime ON ".self::SEARCH_LOG_TABLE_NAME."(datetime)");
		return !(!$result || $result === false);
	}

	/**
	 * Saves search event
	 *
	 * @param DateTime $dateTime
	 * @param string $uri
	 * @param string $ipAddress
	 *
	 * @return bool
	 */
	public function saveSearchEvent(DateTime $dateTime, string $uri, string $ipAddress): bool
	{
		$dateTimeStr = $dateTime->format('Y-m-d H:i:s');
		$sth = $this->pdo->prepare("INSERT INTO ".self::SEARCH_LOG_TABLE_NAME." (datetime, uri, ip_address) VALUES (:datetime, :uri, :ip_address)");
		$sth->bindParam(':uri', $uri, PDO::PARAM_STR);
		$sth->bindParam(':datetime', $dateTimeStr, PDO::PARAM_STR);
		$sth->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
		$result = $sth->execute();

		return $result;
	}

	/**
	 * Count search logs
	 *
	 * @return int|bool
	 */
	public function countSearchLogs()
	{
		$result = $this->pdo->query("SELECT count(*) AS count FROM ".self::SEARCH_LOG_TABLE_NAME);
		return ($result !== false) ? (int) $result->fetch(PDO::FETCH_ASSOC)['count'] : false;
	}

	/**
	 * Retrieve search logs paginated by limit/offset
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array|bool
	 */
	public function getSearchLogs(int $limit, int $offset)
	{
		$sth = $this->pdo->prepare("SELECT datetime, uri, ip_address FROM ".self::SEARCH_LOG_TABLE_NAME." ORDER BY datetime DESC LIMIT :limit OFFSET :offset");
		$sth->bindParam(':limit', $limit, PDO::PARAM_INT);
		$sth->bindParam(':offset', $offset, PDO::PARAM_INT);
        $result = $sth->execute();

		return ($result !== false) ? $sth->fetchAll(PDO::FETCH_ASSOC) : false;
	}

	/**
	 * Delete search logs older than specified hours
	 *
	 * @param int $hoursAgo
	 *
	 * @return bool
	 */
	public function deleteSearchLogs(int $hoursAgo): bool
	{
		$thresholdDatetime = new DateTime('-'.$hoursAgo.' hours');
		$dateTimeStr = $thresholdDatetime->format('Y-m-d H:i:s');
		$sth = $this->pdo->prepare("DELETE FROM ".self::SEARCH_LOG_TABLE_NAME." WHERE datetime < :datetime");
		$sth->bindParam(':datetime', $dateTimeStr, PDO::PARAM_STR);
		$result = $sth->execute();

		return ($result !== false) ? $sth->rowCount() : false;
	}
}