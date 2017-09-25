<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL;

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
	public $configuration = array(
		'dataStorageDSN' => 'sqlite:'.__DIR__.'/../data/somedb.sqlite3',
//		'dataStorageUser' => null,
//		'dataStoragePassword' => null,
//		'dataStorageOptions' = null,
		'authDSN' => 'sqlite:'.__DIR__.'/../data/auth.sqlite3',
//		'authUser' => null,
//		'authPassword' => null,
//		'authOptions' => null,
		'loginRememberDuration' => 30*60,
		'itemsPerPage' => 10,
		'stepsAroundCurrentPage' => 2,
	);
}