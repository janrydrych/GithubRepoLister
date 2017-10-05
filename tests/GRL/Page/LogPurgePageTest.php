<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace Tests\GRL\Page;

require_once __DIR__ . '/../../../vendor/autoload.php';

use GRL\DIC;
use GRL\Page\LogPurgePage;
use GRL\Storage\DataStorage;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * LogPurgePage class tests
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class LogPurgePageTest extends TestCase
{
	/**
	 * @var LogPurgePage
	 */
	private $logPurgePage;

	protected function setUp()
	{
		$mockDIC = $this->createMock(DIC::class);
		$this->logPurgePage = new LogPurgePage($mockDIC);

		parent::setUp();
	}

	protected function tearDown()
	{
		unset($this->logPurgePage);

		parent::tearDown();
	}

	public function testRenderPurgeForm()
	{
		$hours = 15;
		$renderedOutput = $this->logPurgePage->renderPurgeForm($hours);
		$this->assertStringStartsWith('<div class="container"><form',
		                              $renderedOutput,
		                              '->renderPurgeForm() must render proper purge form');
		$this->assertContains('<input type="number" placeholder="Enter hours" name="hours" required value="'.$hours.'">',
		                      $renderedOutput,
		                      '->renderPurgeForm() must render proper purge form');
		$this->assertStringEndsWith('</form></div>',
		                            $renderedOutput,
		                            '->renderPurgeForm() must render proper purge form');

	}

	public function testRenderAuthenticatedContent()
	{
		$mockDIC = $this->logPurgePage->getDIC();

		$mockAuthProvider = new class
		{
			public function getUsername(): string
			{
				return 'John';
			}
		};

		$mockDataStorage = $this->createMock(DataStorage::class);
		$mockDataStorage->expects($this->exactly(3))
		                ->method('countSearchLogs')
		                ->willReturnOnConsecutiveCalls(99, 1, 0);
		$mockGetServiceMethod = function ($name) use ($mockAuthProvider, $mockDataStorage) {
			if ($name === 'authProvider') {
				return $mockAuthProvider;
			} elseif ($name === 'dataStorage') {
				return $mockDataStorage;
			}
		};
		$mockDIC->expects($this->exactly(6))
		        ->method('getService')
		        ->willReturnCallback($mockGetServiceMethod);

		$renderedOutput = $this->logPurgePage->renderAuthenticatedContent();
		$this->assertContains('Currently there are 99 records in the storage.',
		                      $renderedOutput,
		                      '->renderAuthenticatedContent() must render proper content for authenticated user');

		$renderedOutput = $this->logPurgePage->renderAuthenticatedContent();
		$this->assertContains('Currently there is 1 record in the storage.',
		                      $renderedOutput,
		                      '->renderAuthenticatedContent() must render proper content for authenticated user');

		$renderedOutput = $this->logPurgePage->renderAuthenticatedContent();
		$this->assertContains('Currently there are no records in the storage.',
		                      $renderedOutput,
		                      '->renderAuthenticatedContent() must render proper content for authenticated user');


		$this->assertStringStartsWith('<h2>Authenticated as: John</h2>',
		                              $renderedOutput,
		                              '->renderAuthenticatedContent() must render proper content for authenticated user');
		$this->assertStringEndsWith('</div>',
		                            $renderedOutput,
		                            '->renderAuthenticatedContent() must render proper content for authenticated user');
	}


	public function testRenderAuthenticatedContentWhenServicesNotAvailable()
	{
		$errorMessage = 'Service XYZ does not exist.';
		$mockDIC      = $this->logPurgePage->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getService')
		        ->willThrowException(new InvalidArgumentException($errorMessage));

		$renderedOutput = $this->logPurgePage->renderAuthenticatedContent();

		$this->assertStringStartsWith('<div class="container center">',
		                              $renderedOutput,
		                              '->renderAuthenticatedContent() must return error when service does not exist');
		$this->assertContains($errorMessage,
		                      $renderedOutput,
		                      '->renderAuthenticatedContent() must return error when service does not exist');
		$this->assertStringEndsWith('</div>',
		                            $renderedOutput,
		                            '->renderAuthenticatedContent() must return error when service does not exist');
	}


	public function testRenderAnonymousContent()
	{
		$requestVars = array(
			'username' => '276e12da-fd66-4f70-a86f-3bc43289172e',
			'password' => '276e12da-fd88-4f70-a86f-3bc48489172e'
		);
		$renderedOutput = $this->logPurgePage->renderAnonymousContent($requestVars);
		$expectedResult = $this->logPurgePage->renderLoginForm($requestVars['username']);

		$this->assertSame($expectedResult,
		                  $renderedOutput,
		                  '->renderAnonymousContent() must render proper content for anonymous user');
	}
}
