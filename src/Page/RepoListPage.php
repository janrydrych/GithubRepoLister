<?php
/**
 * This file is part of the GithubRepoLister project
 */

namespace GRL\Page;

/**
 * Repository list page class
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class RepoListPage extends Page {

	/**
	 * Render repositories table
	 *
	 * @param array $repositories
	 * @param $username
	 *
	 * @return string
	 */
	public function renderRepositories(array $repositories, $username): string
	{
		$html = '<div class="container"><table><caption>Github repositories for user '.$username.'</caption>';

		if (!empty($repositories)) {
			foreach ($repositories as $repository) {
				$html .= '<tr><th>ID</th><th>Name</th><th>Created</th></tr>';
				$html .= '<tr><td>' . $repository['id'] . '</td><td>' . $repository['full_name'] . '</td><td>' . $repository['created_at'] . '</td></tr>';
				$html .= '<tr><th>Description</th><td colspan="2">' . $repository['description'] . '</td></tr>';
				$html .= '<tr><th>URL</th><td colspan="2"><a href="' . $repository['html_url'] . '">' . $repository['html_url'] . '</a></td></tr>';
				$html .= '<tr class="spacer"></tr>';
			}
		} else {
			$html .= '<tr><td><h2>No repositories has been found</h2></td></tr>';
		}

		$html .= '</table></div>';

		return $html;
	}
}
