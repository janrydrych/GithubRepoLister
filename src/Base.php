<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL;

use Github\Client;
use Github\ResultPager;
use Delight\Auth\Auth;
use GRL\Storage\DataStorage;
use GRL\Util\FlashMessages;
use GRL\Util\Paginator;
use \PDO;
use \PDOException;

/**
 * Base class to provide DIC
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
abstract class Base
{
	/**
	 * @var DIC
	 */
	private $dic;

	/**
	 * Base constructor.
	 */
	public function __construct()
	{
		$this->initializeDIC();
	}

	/**
	 * @return array
	 */
	protected function processConfiguration(): array
	{
		$conf = new Configuration();
		return $conf->configuration;
	}

	/**
	 * @param $dsn
	 * @param null $user
	 * @param null $password
	 * @param null $options
	 *
	 * @return bool|PDO
	 */
	protected function initializePDO($dsn, $user = null, $password = null, $options = null): PDO
	{
		try {
			$pdo = new PDO($dsn, $user, $password, $options);
		} catch (PDOException $e) {
			$this->dic->getFlashBag()->add($e->getMessage());
			return false;
		}
		return $pdo;
	}

	/**
	 * Dependency injection container initialization
	 */
	protected function initializeDIC()
	{
		//Check if DIC is not initializes
		if ($this->dic instanceof DIC && $this->dic->isInitialized()) return;
		
		//Initialization of DIC object
		$this->dic = new DIC($this->processConfiguration());

		//Flash messages initialization
		$this->dic->set('flashBag', new FlashMessages());

		//Repository List Fetcher initialization
		$githubClient = new Client();
		$githubResultPager = new ResultPager($githubClient);
		$this->dic->set(
			'repositoryListFetcher',
			new RepositoryListFetcher(
				$githubClient,
				$githubResultPager
			)
		);

		//Data storage initialization
		$this->dic->set(
			'dataStorage',
			new DataStorage(
				$this->initializePDO(
					$this->dic->get('dataStorageDSN'),
					$this->dic->get('dataStorageUser'),
					$this->dic->get('dataStoragePassword'),
					$this->dic->get('dataStorageOptions')
				)
			)
		);

		//Auth component initialization
		$this->dic->set(
			'authProvider',
			new Auth(
				$this->initializePDO(
					$this->dic->get('authDSN'),
					$this->dic->get('authUser'),
					$this->dic->get('authPassword'),
					$this->dic->get('authOptions')
				)
			)
		);


		//Paginator initialization
		$this->dic->set(
			'paginator',
			new Paginator(
				$this->dic->get('itemsPerPage'),
				$this->dic->get('stepsAroundCurrentPage')
			)
		);
		
		$this->dic->setInitialized();
	}

	/**
	 * @return DIC
	 */
	public function getDIC(): DIC
	{
		return $this->dic;
	}
}