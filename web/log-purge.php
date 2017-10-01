<?php
/**
 * This file is part of the GithubRepoLister project
 */
require_once '../vendor/autoload.php';

use Delight\Auth\Auth;
use GRL\Configuration\Configuration;
use GRL\Configuration\Services;
use GRL\DIC;
use GRL\Page\LogPurgePage;
use GRL\Storage\DataStorageInterface;

//Sanitize request variables
$postSanitizeRules = array(
	'username' => FILTER_SANITIZE_STRING,
	'password' => FILTER_SANITIZE_STRING,
	'hours' => FILTER_SANITIZE_NUMBER_INT,
);
$_POST = filter_input_array(INPUT_POST, $postSanitizeRules);

//Create page content
$logPurgePage = new LogPurgePage(new DIC(new Configuration(), new Services()));

/* @var Auth $authProvider */
$authProvider = $logPurgePage->getDIC()->getService('authProvider');

if ($authProvider->isLoggedIn()) {
	//Authenticated scope
	if ($logPurgePage->isNotEmpty($_POST['hours'])) {
		/* @var DataStorageInterface $dataStorage */
		$dataStorage = $logPurgePage->getDIC()->getService('dataStorage');
		$dataStorage->deleteSearchLogs($_POST['hours']);
	}

	$logPurgePage->addHtmlContent($logPurgePage->renderAuthenticatedContent());
} else {
	//Anonymous scope
	if ($logPurgePage->authenticate($_POST['username'], $_POST['password'])) {
		$logPurgePage->redirect($_SERVER['PHP_SELF']);
	}

	$logPurgePage->addHtmlContent($logPurgePage->renderAnonymousContent($_POST));
}

//Visualise page content
$logPurgePage->renderView();

