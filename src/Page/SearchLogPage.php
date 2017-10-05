<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL\Page;

use GRL\Util\Paginator;
use InvalidArgumentException;


/**
 * Search log page class
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class SearchLogPage extends Page
{
	/**
	 * Render search logs table
	 *
	 * @param array $searchLogs
	 *
	 * @return string
	 */
	public function renderSearchLogs(array $searchLogs): string
	{
		try {
			/* @var Paginator $paginator */
			$paginator = $this->getDIC()->getService('paginator');
		} catch (InvalidArgumentException $e) {
			return $this->renderErrorContainer($e->getMessage());
		}
		$firstItemShowed = $paginator->getOffset() + 1;
		$lastItemShowed = $paginator->getOffset() + $paginator->getItemCountForCurrentPage();

		$html = '<div class="container"><table><caption>Search logs';
		if ($paginator->getItemCount() > 0) {
			$html .= ' - page ' . $paginator->getCurrentPage();
			$html .= ' - items ' . $firstItemShowed . ' to ' . $lastItemShowed . ' of ' . $paginator->getItemCount();
		}
		$html .= '</caption>';

		if (!empty($searchLogs)) {
			foreach ($searchLogs as $log) {
				$html .= '<tr><th>Created</th><th>Uri</th><th>Client IP address</th></tr>';
				$html .= '<tr><td>' . $log['datetime'] . '</td><td>' . $log['uri'] . '</td><td>' . $log['ip_address'] . '</td></tr>';
				$html .= '<tr class="spacer"></tr>';
			}
		} else {
			$html .= '<tr><td>No logs has been found</td></tr>';
		}

		$html .= '</table></div>';

		return $html;
	}
}