<?php
/**
 * This file is part of the GithubRepoLister project
 * Copyright (c) Jan Rydrych <jan.rydrych@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of the GithubRepoLister project
 */
require_once '../vendor/autoload.php';

use GRL\Page\RepoListPage;
use GRL\RepositoryListFetcher;
use GRL\Storage\DataStorageInterface;


//Sanitize request variables
$postSanitizeRules = array(
	'username' => FILTER_SANITIZE_STRING,
	'password' => FILTER_SANITIZE_STRING,
	'hours' => FILTER_SANITIZE_NUMBER_INT,
);
$_POST = filter_input_array(INPUT_POST, $postSanitizeRules);

//Create page content
$repoListPage = new RepoListPage();
$htmlContent = '';

$htmlContent .= '<h3>Enter your Github login credentials</h3>';
$htmlContent .= $repoListPage->renderLoginForm($_POST['username']);

$isLoginPossible = $repoListPage->isNotEmpty($_POST['username'], $_POST['password']);

if ($isLoginPossible) {
	/* @var RepositoryListFetcher $rlf */
	$rlf = $repoListPage->getDIC()
	                    ->get('repositoryListFetcher');
	list($repoResult, $repoData) = $rlf->getRepositories($_POST['username'], $_POST['password']);
	if ($repoResult) {
		// save search event into data storage
		/* @var DataStorageInterface $dataStorage */
		$dataStorage = $repoListPage->getDIC()
		                            ->get('dataStorage');
		$result      = $dataStorage->saveSearchEvent(
			new DateTime(),
			str_replace('%USERNAME%', $_POST['username'], RepositoryListFetcher::GITHUB_API_REPO_URI),
			$_SERVER['REMOTE_ADDR']
		);
		if (!$result) {
			$repoListPage->getDIC()
			             ->getFlashBag()
			             ->add('Search was not saved to storage!');
		}

		//render repositories data
		$htmlContent .= $repoListPage->renderRepositories($repoData, $_POST['username']);
	} else {
		$repoListPage->getDIC()
		             ->getFlashBag()
		             ->add($repoData);
	}
}

//Visualise page content
$repoListPage->renderView($htmlContent);

