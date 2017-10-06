<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL;

use Exception;
use Github\Client;
use Github\ResultPager;

/**
 * Repository fetcher based on KNPLabs github-api
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class RepositoryListFetcher
{
	/**
	 * URI used by repositories() method of Github\Api\User class, which is called by $this->fetchUsersPublicRepositories()
	 */
	const GITHUB_API_REPO_URI = 'https://api.github.com/users/%USERNAME%/repos?type=owner&sort=created&direction=asc';

	/**
	 * Fields which are retrieved from whole repository data returned by Github API
	 * More on possible values https://developer.github.com/v3/repos/
	 */
	const DESIRED_REPOSITORY_FIELDS = [
		'id',
		'full_name',
		'html_url',
		'description',
		'created_at'
	];

	/**
	 * @var Client
	 */
	private $ghClient;

	/**
	 * @var ResultPager
	 */
	private $ghResultPager;

	/**
	 * GithubRepositoryFetcher constructor.
	 *
	 * @param Client|null $ghClient
	 * @param ResultPager|null $ghResultPager
	 */
	public function __construct(Client $ghClient, ResultPager $ghResultPager)
	{
		$this->ghClient = $ghClient;
		$this->ghResultPager = $ghResultPager;
	}

	/**
	 * Fetch user public repositories by Github user-repo API
	 * @link https://api.github.com/users/%USERNAME%/repos?type=owner&sort=created&direction=asc
	 *
	 * @param string $username
	 *
	 * @return array
	 */
	private function fetchUsersPublicRepositories(string $username): array
	{
		$repositories = $this->ghResultPager->fetchAll(
			$this->ghClient->user(),
			'repositories',
			array(
				$username,
				'owner',
				'created',
				'desc'
			)
	    );

		return $repositories;
	}

	/**
	 * Filter out unused repository data obtained from API
	 *
	 * @param array $repositories
	 * @param array|null $desiredRepositoryFields
	 *
	 * @return array
	 */
	private function filterRepositoriesData(array $repositories, array $desiredRepositoryFields = null): array
	{
		$desiredRepositoryFields = $desiredRepositoryFields ?: $this::DESIRED_REPOSITORY_FIELDS;
		foreach ($repositories as &$repo) {
			$repo = array_filter(
				$repo,
				function($element) use ($desiredRepositoryFields)
				{
					return in_array($element, $desiredRepositoryFields);
				},
				ARRAY_FILTER_USE_KEY
			);
		}

		return $repositories;
	}

	/**
	 * Get all repositories for specified Github user
	 *
	 * @param string $username
	 * @param array|null $desiredRepositoryFields
	 *
	 * @return array
	 */
	private function getAllRepositoriesForUser(string $username, array $desiredRepositoryFields = null): array
	{
		$repositories = $this->fetchUsersPublicRepositories($username);
		return $this->filterRepositoriesData($repositories, $desiredRepositoryFields);
	}


	/**
	 * Get repositories data for specified Github user account
	 *
	 * @param $username
	 * @param $password
	 * @param array|null $desiredRepositoryFields
	 *
	 * @return array
	 */
	public function getRepositoriesData($username, $password, array $desiredRepositoryFields = null): array
	{
		if (!isset($username, $password)) { return array(false, 'Invalid Credentials'); }

		try {
			$this->ghClient->authenticate($username, $password);
			$repositories = $this->getAllRepositoriesForUser($username, $desiredRepositoryFields);
		} catch(Exception $e) {
			return array(false, $e->getMessage());
		}

		return array(true, $repositories);
	}
}