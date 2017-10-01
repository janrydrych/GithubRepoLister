<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL\Configuration;

use Delight\Auth\Auth;
use Exception;
use Github\Client;
use Github\ResultPager;
use GRL\DIC;
use GRL\RepositoryListFetcher;
use GRL\Storage\DataStorage;
use GRL\Util\FlashMessages;
use GRL\Util\Paginator;
use PDO;
use PDOException;

/**
 * Services definition
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class Services
{
	/**
	 * @return FlashMessages
	 */
	protected function createFlashBagService(): FlashMessages
	{
		return new FlashMessages();
	}

	/**
	 * @return RepositoryListFetcher
	 */
	protected function createRepositoryListFetcherService(): RepositoryListFetcher
	{
		$githubClient = new Client();
		$githubResultPager = new ResultPager($githubClient);
		return new RepositoryListFetcher($githubClient, $githubResultPager);
	}

	/**
	 * @param $dsn
	 * @param null $user
	 * @param null $password
	 * @param null $options
	 *
	 * @return PDO
	 */
	protected function createPDO($dsn, $user = null, $password = null, $options = null): PDO
	{
		try {
			$pdo = new PDO($dsn, $user, $password, $options);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		return $pdo;
	}

	/**
	 * @param $dsn
	 * @param null $username
	 * @param null $password
	 * @param null $options
	 *
	 * @return DataStorage
	 */
	protected function createDataStorageService($dsn, $username = null, $password = null, $options = null): DataStorage
	{
		try {
			return new DataStorage(
				$this->createPDO($dsn, $username, $password, $options)
			);
		} catch (Exception $e) {
			die($e->getMessage());
		}
	}

	/**
	 * @param $dsn
	 * @param null $username
	 * @param null $password
	 * @param null $options
	 *
	 * @return Auth
	 */
	protected function createAuthProviderService($dsn, $username = null, $password = null, $options = null): Auth
	{
		try {
			return new Auth(
				$this->createPDO($dsn, $username, $password, $options)
			);
		} catch (Exception $e) {
			die($e->getMessage());
		}
	}

	/**
	 * @param $itemsPerPage
	 * @param $stepsAroundCurrentPage
	 *
	 * @return Paginator
	 */
	protected function createPaginatorService($itemsPerPage, $stepsAroundCurrentPage): Paginator
	{
		return new Paginator($itemsPerPage, $stepsAroundCurrentPage);
	}

	/**
	 * Inject services into DIC
	 *
	 * @param DIC $dic
	 */
	public function toDIC(DIC $dic)
	{
		try {
			$dic->addService('flashBag', $this->createFlashBagService());
			$dic->addService('repositoryListFetcher', $this->createRepositoryListFetcherService());
			$dic->addService('dataStorage', $this->createDataStorageService(
				$dic->get('dataStorage')['DSN'],
				$dic->get('dataStorage')['username'],
				$dic->get('dataStorage')['password'],
				$dic->get('dataStorage')['options']
			));
			$dic->addService('authProvider', $this->createAuthProviderService(
				$dic->get('authProvider')['DSN'],
				$dic->get('authProvider')['username'],
				$dic->get('authProvider')['password'],
				$dic->get('authProvider')['options']
			));
			$dic->addService('paginator', $this->createPaginatorService(
				$dic->get('paginator')['itemsPerPage'],
				$dic->get('paginator')['stepsAroundCurrentPage']
			));
		} catch (Exception $e) {
			die($e->getMessage());
		}
	}
}