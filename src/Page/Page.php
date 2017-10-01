<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL\Page;

use Delight\Auth\Auth;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\UnknownUsernameException;
use Exception;
use GRL\Base;
use GRL\Util\Paginator;

/**
 * Base page class
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
abstract class Page extends Base
{
	/**
	 * @var string
	 */
	private $htmlContent;

	/**
	 * @param $htmlContent
	 */
	public function setHtmlContent($htmlContent)
	{
		$this->htmlContent = $htmlContent;
	}

	/**
	 * @return string
	 */
	public function getHtmlContent(): string
	{
		return $this->htmlContent;
	}

	/**
	 * @param $htmlContent
	 *
	 * @return $this
	 */
	public function addHtmlContent($htmlContent): Page
	{
		$this->htmlContent .= $htmlContent;
		return $this;
	}

	/**
	 * Render base page template
	 */
	public function renderView()
	{
		$flashBag = $this->getDIC()->getFlashBag();
		echo '<html><head><link rel="stylesheet" href="styles/style.css" /></head><body>';
		if ($flashBag->hasMessages()) { echo $flashBag->getMessages(); }
		echo $this->htmlContent;
		echo '</body></html>';
	}

	/**
	 * Render simple login form
	 *
	 * @param null $username
	 *
	 * @return string
	 */
	public function renderLoginForm($username = null): string
	{
		$html = '<div class="container"><form action="'.$_SERVER['PHP_SELF'].'" method="post"><div>';
		$html .= '<label><b>Username</b></label> ';
		$html .= '<input type="text" placeholder="Enter Username" name="username" required value="'.$username.'">';
		$html .= '<label><b>Password</b></label> ';
		$html .= '<input type="password" placeholder="Enter Password" name="password" required>';
		$html .= '<button type="submit">Login</button>';
		$html .= '</div></form></div>';

		return $html;
	}

	/**
	 * Render visual pagination
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function renderPagination(string $url): string
	{
		/* @var Paginator $paginator */
		$paginator = $this->getDIC()->getService('paginator');
		$html = '<div class="container center"><div class="pagination">';
		$html .= '<a href="'.$url.'">&laquo;</a>';

		$stepCount = count($paginator->getSteps());
		$loopIndex = 0;
		foreach ($paginator->getSteps() as $step) {
			$urlQuery = ($step != $paginator->getFirstPage()) ? '?page='.$step : '';
			$class = ($step == $paginator->getCurrentPage()) ? 'class="active" ' : '';

			$html .= '<a '.$class.'href="'.$url.$urlQuery.'">'.$step.'</a>';
			if (++$loopIndex < $stepCount && $paginator->getSteps()[$loopIndex] > $step + 1) {
				$html .= '<a><span>…</span></a>';
			}
		}

		$html .= '<a href="'.$url.'?page='.$paginator->getLastPage().'">&raquo;</a>';
		$html .= '</div></div>';

		return $html;
	}


	/**
	 * Check if provided arguments are not empty
	 *
	 * @return bool
	 */
	public function isNotEmpty(): bool
	{
		$arguments = func_get_args();
		foreach ($arguments as $argument) {
			if (empty($argument)) { return false; }
		}

		return true;
	}


	/**
	 * Authenticate user
	 *
	 * @param $username
	 * @param $password
	 *
	 * @return bool
	 */
	public function authenticate($username, $password): bool
	{
		if (empty($username) || empty($password)) { return false; }

		try {
			/* @var Auth $authProvider */
			$authProvider = $this->getDIC()->getService('authProvider');
			$authProvider->loginWithUsername(
				$username,
				$password,
				$this->getDIC()->get('loginRememberDuration')
			);
			return true;
		}
		catch (UnknownUsernameException $e){
			$this->getDIC()->getFlashBag()->add('Invalid credentials!');
			return false;
		}
		catch (InvalidPasswordException $e){
			$this->getDIC()->getFlashBag()->add('Invalid credentials!');
			return false;
		}
		catch (Exception $e) {
			$this->getDIC()->getFlashBag()->add('Unknown authentication error!');
			return false;
		}
	}

	/**
	 * Redirection
	 *
	 * @param $url
	 * @param int $statusCode
	 */
	public function redirect($url, $statusCode = 303)
	{
		header('Location: ' . $url, true, $statusCode);
		die();
	}
}