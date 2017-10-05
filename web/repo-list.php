<?php
/**
 * This file is part of the GithubRepoLister project
 */
require_once '../vendor/autoload.php';

use GRL\Configuration\Configuration;
use GRL\Configuration\Services;
use GRL\DIC;
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
$repoListPage = new RepoListPage(new DIC(new Configuration(), new Services()));

$repoListPage->addHtmlContent('<h3>Enter your Github login credentials</h3>')
             ->addHtmlContent($repoListPage->renderLoginForm($_POST['username']));

$isLoginPossible = !$repoListPage->isAnyArgumentEmpty($_POST['username'], $_POST['password']);

if ($isLoginPossible) {
	/* @var RepositoryListFetcher $rlf */
	$rlf = $repoListPage->getDIC()->getService('repositoryListFetcher');
	list($repoResult, $repoData) = $rlf->getRepositoriesData($_POST['username'], $_POST['password']);
	if ($repoResult) {
		// save search event into data storage
		/* @var DataStorageInterface $dataStorage */
		$dataStorage = $repoListPage->getDIC()->getService('dataStorage');
		$result = $dataStorage->saveSearchEvent(
			new DateTime(),
			str_replace('%USERNAME%', $_POST['username'], RepositoryListFetcher::GITHUB_API_REPO_URI),
			$_SERVER['REMOTE_ADDR']
		);
		if (!$result) {
			$repoListPage->getDIC()->getFlashBag()->add('Search was not saved to storage!');
		}

		//render repositories data
		$repoListPage->addHtmlContent($repoListPage->renderRepositories($repoData, $_POST['username']));
	} else {
		$repoListPage->getDIC()->getFlashBag()->add($repoData);
	}
}

//Visualise page content
$repoListPage->renderView();

