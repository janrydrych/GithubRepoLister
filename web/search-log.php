<?php
/**
 * This file is part of the GithubRepoLister project
 */
require_once '../vendor/autoload.php';

use GRL\Configuration\Configuration;
use GRL\Configuration\Services;
use GRL\DIC;
use GRL\Page\SearchLogPage;
use GRL\Storage\DataStorageInterface;
use GRL\Util\Paginator;

//Sanitize request variables
$sanitizeRules = array('page' => FILTER_SANITIZE_NUMBER_INT);
$_GET = filter_input_array(INPUT_GET, $sanitizeRules);

//Create page content
$searchLogPage = new SearchLogPage(new DIC(new Configuration(), new Services()));

/* @var Paginator $paginator */
$paginator = $searchLogPage->getDIC()->getService('paginator');
/* @var DataStorageInterface $dataStorage */
$dataStorage = $searchLogPage->getDIC()->getService('dataStorage');

$searchLogCount = $dataStorage->countSearchLogs();
$paginator->setItemCount($searchLogCount)
          ->setCurrentPage(min($_GET['page'], $paginator->getLastPage()));

$searchLogs  = $dataStorage->getSearchLogs($paginator->getItemsPerPage(), $paginator->getOffset());
if ($searchLogs !== false) {
	$searchLogPage->addHtmlContent($searchLogPage->renderSearchLogs($searchLogs));

	if ($paginator->getPageCount() > 1) {
		$baseUrl     = strtok($_SERVER['REQUEST_URI'], '?');
		$searchLogPage->addHtmlContent($searchLogPage->renderPagination($baseUrl));
	}
}

//Visualise page content
$searchLogPage->renderView();
