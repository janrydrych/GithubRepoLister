<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL\Configuration;

use GRL\DIC;

/**
 * Configuration
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class Configuration
{
	/**
	 * @var array
	 */
	private $configuration = array(
		'dataStorage' => array(
			'DSN' => 'sqlite:'.__DIR__.'/../../data/somedb.sqlite3',
			'username' => null,
			'password' => null,
			'options' => null,
		),
		'authProvider' => array(
			'DSN' => 'sqlite:'.__DIR__.'/../../data/auth.sqlite3',
		    'username' => null,
		    'password' => null,
		    'options' => null,
		),
		'loginRememberDuration' => 30*60,
		'paginator' => array(
			'itemsPerPage' => 10,
			'stepsAroundCurrentPage' => 2,
		),
	);

	/**
	 * Inject configuration into DIC
	 *
	 * @param DIC $dic
	 */
	public function toDIC(DIC $dic)
	{
		foreach ($this->configuration as $key => $value) {
			$dic->set($key, $value);
		}
	}
}