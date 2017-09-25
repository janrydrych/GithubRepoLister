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

use Delight\Auth\Auth;
use GRL\Page\LogPurgePage;
use GRL\Storage\DataStorageInterface;

//Sanitize request variables
$postSanitizeRules = array(
	'username' => FILTER_SANITIZE_STRING,
	'password' => FILTER_SANITIZE_STRING,
	'hours' => FILTER_SANITIZE_NUMBER_INT,
);
$_POST             = filter_input_array(INPUT_POST, $postSanitizeRules);

//Create page content
$logPurgePage = new LogPurgePage();
$htmlContent = '';

/* @var Auth $authProvider */
$authProvider = $logPurgePage->getDIC()
                             ->get('authProvider');

if ($authProvider->isLoggedIn()) {
	//Authenticated scope
	if ($logPurgePage->isNotEmpty($_POST['hours'])) {
		/* @var DataStorageInterface $dataStorage */
		$dataStorage = $logPurgePage->getDIC()
		                            ->get('dataStorage');
		$dataStorage->deleteSearchLogs($_POST['hours']);
	}

	$htmlContent .= $logPurgePage->renderAuthenticatedContent($_POST);
} else {
	//Anonymous scope
	if ($logPurgePage->authenticate($_POST['username'], $_POST['password'])) {
		$logPurgePage->redirect($_SERVER['PHP_SELF']);
	}

	$htmlContent .= $logPurgePage->renderAnonymousContent($_POST);
}

//Visualise page content
$logPurgePage->renderView($htmlContent);

