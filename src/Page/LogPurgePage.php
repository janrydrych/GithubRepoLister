<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL\Page;

use Delight\Auth\Auth;
use GRL\Storage\DataStorageInterface;

/**
 * Log purge page class
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class LogPurgePage extends Page
{
	/**
	 * Render search log purge form
	 *
	 * @param $hours
	 *
	 * @return string
	 */
	public function renderPurgeForm($hours = null): string
	{
		$html = '<div class="container"><form action="'.$_SERVER['PHP_SELF'].'" method="post"><div>';
		$html .= '<label><b>Delete logs older than</b></label> ';
		$html .= '<input type="number" placeholder="Enter hours" name="hours" required value="'.$hours.'">';
		$html .= '<button type="submit">Execute!</button>';
		$html .= '</div></form></div>';

		return $html;
	}

	/**
	 * Render content for authenticated users
	 *
	 * @return string
	 */
	public function renderAuthenticatedContent(): string
	{
		/* @var Auth $authProvider */
		$authProvider = $this->getDIC()->getService('authProvider');
		$html = '<h2>Authenticated as: '.$authProvider->getUsername().'</h2>';
		$html .= $this->renderPurgeForm();
		$html .= '<div class="container">';
		/* @var DataStorageInterface $dataStorage */
		$dataStorage = $this->getDIC()->getService('dataStorage');
		$recordCount = $dataStorage->countSearchLogs();
		switch($recordCount){
			case 0: $html .= 'Currently there are no records in the storage.'; break;
			case 1: $html .= 'Currently there is '.$recordCount.' record in the storage.'; break;
			default: $html .= 'Currently there are '.$recordCount.' records in the storage.';
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render content for anonymous users
	 *
	 * @param $requestVars
	 *
	 * @return string
	 */
	public function renderAnonymousContent($requestVars = null): string
	{
		$username = isset($requestVars['username']) ? $requestVars['username'] : null;
		$html = $this->renderLoginForm($username);

		return $html;
	}
}

