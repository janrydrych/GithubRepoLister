<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace Tests\GRL\Page;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../vendor/delight-im/auth/src/Exceptions.php';

use Delight\Auth\InvalidPasswordException;
use Delight\Auth\UnknownUsernameException;
use Exception;
use GRL\DIC;
use GRL\Page\Page;
use GRL\Util\FlashMessages;
use GRL\Util\Paginator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Page class tests
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class PageTest extends TestCase
{
	/**
	 * @var Page
	 */
	private $page;

	protected function setUp()
	{
		$mockDIC = $this->createMock(DIC::class);

		$this->page = $this->getMockBuilder(Page::class)
		             ->setConstructorArgs(array($mockDIC))
		             ->getMockForAbstractClass();

		parent::setUp();
	}

	protected function tearDown()
	{
		unset($this->page);

		parent::tearDown();
	}

	public function testGetSetHtmlContent()
	{
		$testContent = 'lorem ipsum';
		$this->page->setHtmlContent($testContent);
		$this->assertEquals($testContent, $this->page->getHtmlContent(), '->setHtmlContent() must set correct value');
	}

	public function testAddHtmlContent()
	{
		$testContent = 'lorem ipsum';
		$this->page->setHtmlContent($testContent);
		$this->page->addHtmlContent($testContent);

		$this->assertEquals($testContent . $testContent, $this->page->getHtmlContent(), '->addHtmlContent() must add value to already set content');
	}

	public function testRenderView()
	{
		$testContent = '<div>276e12da-fd66-4f70-a86f-3bc43289172e</div>';
		$testFlashMessage = 'c34b286e-9546-4cc4-9f4c-947b67cc6ffd';

		$mockFlashBag = $this->createMock(FlashMessages::class);
		$mockFlashBag->expects($this->exactly(1))
		             ->method('hasMessages')
		             ->willReturn(true);
		$mockFlashBag->expects($this->exactly(1))
		             ->method('getMessages')
		             ->willReturn('<div class="flash-msg">'.$testFlashMessage.'</div>');

		$mockDIC = $this->page->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getFlashBag')
		        ->willReturn($mockFlashBag);

		$this->page->setHtmlContent($testContent);
		ob_start();
		$this->page->renderView();
		$renderedOutput = ob_get_clean();
		$this->assertStringStartsWith('<html><head>', $renderedOutput, '->renderView() must render proper page content');
		$this->assertContains($testContent, $renderedOutput, '->renderView() must render proper page content');
		$this->assertContains($testFlashMessage, $renderedOutput, '->renderView() must render proper page content');
		$this->assertStringEndsWith('</body></html>', $renderedOutput, '->renderView() must render proper page content');
	}

	public function testRenderLoginForm()
	{
		$testUser = '276e12da-fd66-4f70-a86f-3bc43289172e';
		$renderedOutput = $this->page->renderLoginForm($testUser);
		$this->assertStringStartsWith('<div class="container"><form',
		                              $renderedOutput,
		                              '->renderLoginForm() must render proper login form');
		$this->assertContains('<input type="text" placeholder="Enter Username" name="username" required value="'.$testUser.'">',
		                      $renderedOutput,
		                      '->renderLoginForm() must render proper login form');
		$this->assertStringEndsWith('</form></div>',
		                            $renderedOutput,
		                            '->renderLoginForm() must render proper login form');
	}

	public function testRenderPagination()
	{
		$mockPaginator = $this->createMock(Paginator::class);
		$testSteps = array(1, 2, 3, 4);
		$testStepsCount = count($testSteps);
		$mockPaginator->expects($this->exactly($testStepsCount + 1))
		              ->method('getSteps')
		              ->willReturn($testSteps);
		$mockPaginator->expects($this->exactly($testStepsCount))
		              ->method('getFirstPage')
		              ->willReturn($testSteps[0]);
		$mockPaginator->expects($this->exactly($testStepsCount))
		              ->method('getCurrentPage')
		              ->willReturn($testSteps[0]);

		$mockDIC = $this->page->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getService')
		        ->with('paginator')
		        ->willReturn($mockPaginator);

		$testUrl = '276e12da-fd66-4f70-a86f-3bc43289172e';
		$renderedOutput = $this->page->renderPagination($testUrl);
		$this->assertStringStartsWith('<div class="container center"><div class="pagination">', $renderedOutput, '->renderPagination() must render proper pagination');
		$this->assertContains('<a href="' . $testUrl . '?page=2">2</a>',
		                      $renderedOutput,
		                      '->renderPagination() must render proper pagination');

		$this->assertStringEndsWith('</div></div>', $renderedOutput, '->renderPagination() must render proper pagination');
	}

	public function testRenderPaginationWhenNoPaginatorAvailable()
	{
		$errorMessage = 'Service XYZ does not exist.';
		$mockDIC = $this->page->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getService')
		        ->willThrowException(new InvalidArgumentException($errorMessage));

		$renderedOutput = $this->page->renderPagination('');

		$this->assertStringStartsWith('<div class="container center">', $renderedOutput, '->renderPagination() must return error when paginator service does not exist');
		$this->assertContains($errorMessage, $renderedOutput, '->renderPagination() must return error when paginator service does not exist');
		$this->assertStringEndsWith('</div>', $renderedOutput, '->renderPagination() must return error when paginator service does not exist');
	}

	public function testIsAnyArgumentEmpty()
	{
		$testArg1 = 0;
		$testArg2 = 'a';
		$testArg3 = 0.8;
		$testArg4 = '0';
		$this->assertFalse($this->page->isAnyArgumentEmpty($testArg1), '->isAnyArgumentEmpty() must return false for a non empty argument');
		$this->assertFalse($this->page->isAnyArgumentEmpty($testArg1, $testArg2, $testArg3, $testArg4), '->isAnyArgumentEmpty() must return false for multiple non empty arguments');

		$testArg1 = '';
		$testArg2 = ' ';
		$testArg3 = null;
		$this->assertTrue($this->page->isAnyArgumentEmpty($testArg1), '->isAnyArgumentEmpty() must return true for an empty argument');
		$this->assertTrue($this->page->isAnyArgumentEmpty($testArg1, $testArg2, $testArg3), '->isAnyArgumentEmpty() must return true for multiple non empty arguments');
	}

	public function testAuthenticate()
	{
		$mockAuthProvider = new class {
			public function loginWithUsername($username, $password) {
				if (empty($username) || empty($password)) {
					return false;
				} else {
					return true;
				}
			}
		};

		$mockDIC = $this->page->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getService')
		        ->with($this->equalTo('authProvider'))
		        ->willReturn($mockAuthProvider);

		$this->assertFalse($this->page->authenticate(null, null), '->authenticate() must return false for empty username and password');
		$this->assertTrue($this->page->authenticate('john', 'doe'), '->authenticate() must return true if valid credentials provided');
	}

	public function testAuthenticateReturnsFalseWhenUnknownUsername()
	{
		$mockAuthProvider = new class {
			public function loginWithUsername()
			{
				throw new UnknownUsernameException();
			}
		};

		$mockDIC = $this->page->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getService')
		        ->with($this->equalTo('authProvider'))
		        ->willReturn($mockAuthProvider);

		$this->assertFalse($this->page->authenticate('dummy', 'dummy'), '->authenticate() must return false when unknown username provided');
	}

	public function testAuthenticateReturnsFalseWhenInvalidPassword()
	{
		$mockAuthProvider = new class {
			public function loginWithUsername()
			{
				throw new InvalidPasswordException();
			}
		};

		$mockDIC = $this->page->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getService')
		        ->with($this->equalTo('authProvider'))
		        ->willReturn($mockAuthProvider);

		$this->assertFalse($this->page->authenticate('dummy', 'dummy'), '->authenticate() must return false when invalid password provided');
	}

	public function testAuthenticateReturnsFalseWhenUnknownError()
	{
		$mockAuthProvider = new class {
			public function loginWithUsername()
			{
				throw new Exception();
			}
		};

		$mockDIC = $this->page->getDIC();
		$mockDIC->expects($this->exactly(1))
		        ->method('getService')
		        ->with($this->equalTo('authProvider'))
		        ->willReturn($mockAuthProvider);

		$this->assertFalse($this->page->authenticate('dummy', 'dummy'), '->authenticate() must return false when unknown authentication error occurs');
	}
}
