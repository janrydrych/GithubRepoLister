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

use GRL\Page\SearchLogPage;
use GRL\Storage\DataStorageInterface;
use GRL\Util\Paginator;

//Sanitize request variables
$sanitizeRules = array('page' => FILTER_SANITIZE_NUMBER_INT);
$_GET          = filter_input_array(INPUT_GET, $sanitizeRules);

//Create page content
$searchLogPage = new SearchLogPage();
$htmlContent = '';
/* @var Paginator $paginator */
$paginator = $searchLogPage->getDIC()
                           ->get('paginator');
/* @var DataStorageInterface $dataStorage */
$dataStorage = $searchLogPage->getDIC()
                             ->get('dataStorage');

$searchLogCount = $dataStorage->countSearchLogs();
$paginator->setItemCount($searchLogCount)
          ->setCurrentPage(min($_GET['page'], $paginator->getLastPage()));


$searchLogs  = $dataStorage->getSearchLogs($paginator->getItemsPerPage(), $paginator->getOffset());
if ($searchLogs !== false) {
	$htmlContent .= $searchLogPage->renderSearchLogs($searchLogs);

	if ($paginator->getPageCount() > 1) {
		$baseUrl     = strtok($_SERVER["REQUEST_URI"], '?');
		$htmlContent .= $searchLogPage->renderPagination($baseUrl);
	}
}

//Visualise page content
$searchLogPage->renderView($htmlContent);

