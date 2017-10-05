<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace Tests\GRL\Page;

require_once __DIR__ . '/../../../vendor/autoload.php';

use GRL\DIC;
use GRL\Page\RepoListPage;
use PHPUnit\Framework\TestCase;

/**
 * RepoListPage class tests
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class RepoListPageTest extends TestCase
{
	/**
	 * @var RepoListPage
	 */
	private $repoListPage;

	protected function setUp()
	{
		$mockDIC = $this->createMock(DIC::class);
		$this->repoListPage = new RepoListPage($mockDIC);

		parent::setUp();
	}

	protected function tearDown()
	{
		unset($this->repoListPage);

		parent::tearDown();
	}

	public function testRenderRepositories()
	{
		$testRepoData = array(
			array(
				'id' => '123',
				'full_name' => 'Dummy 1',
				'html_url' => 'https://dummy.com/dummy1',
				'description' => '9723a370-8fe0-42fb-8e48-fa0e78805b70',
				'created_at' => '1.1.1999'
			),
			array(
				'id' => '456',
				'full_name' => 'Dummy 2',
				'html_url' => 'https://dummy.com/dummy2',
				'description' => '21d61dc2-cd22-4da0-b15f-de2c8815bbf6',
				'created_at' => '1.1.2000'
			),
		);
		$testUsername = '63325a5c-2475-48ce-803c-ff61b6ebab82';

		$renderedOutput = $this->repoListPage->renderRepositories($testRepoData, $testUsername);

		$this->assertStringStartsWith('<div class="container"><table><caption>Github repositories for user ' . $testUsername . '</caption>',
		                              $renderedOutput,
		                              '->renderRepositories() must render proper repository list content');
		
		foreach ($testRepoData as $repo) {
			$expected = '<td>' . $repo['id'] . '</td><td>' . $repo['full_name'] . '</td><td>' . $repo['created_at'] . '</td>';
			$this->assertContains($expected,
			                      $renderedOutput,
			                      '->renderRepositories() must render proper repository list content');
			$this->assertContains($repo['description'],
			                      $renderedOutput,
			                      '->renderRepositories() must render proper repository list content');
		}

		$this->assertStringEndsWith('</table></div>',
		                            $renderedOutput,
		                            '->renderRepositories() must render proper repository list content');
	}

	public function testRenderRepositoriesWithEmptyData()
	{
		$testRepoData = array();
		$testUsername = '63325a5c-2475-48ce-803c-ff61b6ebab82';

		$renderedOutput = $this->repoListPage->renderRepositories($testRepoData, $testUsername);
		$this->assertStringStartsWith('<div class="container"><table><caption>Github repositories for user ' . $testUsername . '</caption>',
		                              $renderedOutput,
		                              '->renderRepositories() must render proper repository list content');
		$this->assertContains('<h2>No repositories has been found</h2>',
		                      $renderedOutput,
		                      '->renderRepositories() must render proper repository list content');
		$this->assertStringEndsWith('</table></div>',
		                            $renderedOutput,
		                            '->renderRepositories() must render proper repository list content');
	}

}
