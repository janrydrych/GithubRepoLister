<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL\Storage;

use \PDO;
use \DateTime;
use \Exception;

/**
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
interface DataStorageInterface
{
	/**
	 * @param PDO $pdo
	 * @throws Exception
	 */
	public function __construct(PDO $pdo);

	/**
	 * Saves search event
	 *
	 * @param DateTime $dateTime
	 * @param string $uri
	 * @param string $ipAddress
	 *
	 * @return bool
	 */
	public function saveSearchEvent(DateTime $dateTime, string $uri, string $ipAddress): bool;

	/**
	 * Count search logs
	 *
	 * @return int|bool
	 */
	public function countSearchLogs();

	/**
	 * Retrieve search logs paginated by limit/offset
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array|bool
	 */
	public function getSearchLogs(int $limit, int $offset);

	/**
	 * Delete search logs older than specified hours
	 *
	 * @param int $hoursAgo
	 *
	 * @return bool
	 */
	public function deleteSearchLogs(int $hoursAgo): bool;
}