<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace Tests\GRL\Page;

require_once __DIR__ . '/../../../vendor/autoload.php';

use GRL\DIC;
use GRL\Page\SearchLogPage;
use GRL\Util\Paginator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * SearchLogPage class tests
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class SearchLogPageTest extends TestCase
{
	/**
	 * @var SearchLogPage
	 */
	private $searchLogPage;

	protected function setUp()
	{
		$mockDIC = $this->createMock(DIC::class);
		$this->searchLogPage = new SearchLogPage($mockDIC);

		parent::setUp();
	}

	protected function tearDown()
	{
		unset($this->searchLogPage);

		parent::tearDown();
	}

	public function testRenderSearchLogs()
	{
		$testLogsData = array(
			array(
				'datetime' => '1.1.1999',
				'uri' => 'https://search-uri-1',
				'ip_address' => '1.1.1.1'
			),
			array(
				'datetime' => '1.1.2000',
				'uri' => 'https://search-uri-2',
				'ip_address' => '1.1.1.2'
			),
		);

		$paginator    = new Paginator(2);
		$paginator->setItemCount(5);

		$mockDIC = $this->searchLogPage->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getService')
		        ->with('paginator')
		        ->willReturn($paginator);

		$renderedOutput = $this->searchLogPage->renderSearchLogs($testLogsData);

		$this->assertStringStartsWith('<div class="container"><table><caption>Search logs',
		                              $renderedOutput,
		                              '->testRenderSearchLogs() must render proper content');
		$this->assertContains(' - page ' . $paginator->getCurrentPage() . ' - items ',
		                      $renderedOutput,
		                      '->testRenderSearchLogs() must render proper content');
		foreach ($testLogsData as $log) {
			$expected = '<td>' . $log['datetime'] . '</td><td>' . $log['uri'] . '</td><td>' . $log['ip_address'] . '</td>';
			$this->assertContains($expected,
			                      $renderedOutput,
			                      '->testRenderSearchLogs() must render proper content');
		}
		$this->assertStringEndsWith('</table></div>',
		                            $renderedOutput,
		                            '->testRenderSearchLogs() must render proper content');
	}

	public function testRenderSearchLogsWhenServiceNotAvailable()
	{
		$errorMessage = 'Service XYZ does not exist.';
		$mockDIC      = $this->searchLogPage->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getService')
		        ->willThrowException(new InvalidArgumentException($errorMessage));

		$renderedOutput = $this->searchLogPage->renderSearchLogs(array());

		$this->assertStringStartsWith('<div class="container center">',
		                              $renderedOutput,
		                              '->renderSearchLogs() must return error when service does not exist');
		$this->assertContains($errorMessage,
		                      $renderedOutput,
		                      '->renderSearchLogs() must return error when service does not exist');
		$this->assertStringEndsWith('</div>',
		                            $renderedOutput,
		                            '->renderSearchLogs() must return error when service does not exist');
	}

	public function testRenderSearchLogsWithEmptyData()
	{
		$testLogsData = array();
		$paginator    = new Paginator(2);
		$paginator->setItemCount(count($testLogsData));

		$mockDIC = $this->searchLogPage->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getService')
		        ->with('paginator')
		        ->willReturn($paginator);

		$renderedOutput = $this->searchLogPage->renderSearchLogs($testLogsData);
		$this->assertStringStartsWith('<div class="container"><table><caption>Search logs',
		                              $renderedOutput,
		                              '->renderRepositories() must render proper repository list content');
		$this->assertContains('No logs has been found',
		                      $renderedOutput,
		                      '->renderRepositories() must render proper repository list content');
		$this->assertStringEndsWith('</table></div>',
		                            $renderedOutput,
		                            '->renderRepositories() must render proper repository list content');
	}
}
