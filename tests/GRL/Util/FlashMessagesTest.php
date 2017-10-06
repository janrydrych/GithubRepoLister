<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace Tests\GRL\Util;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Delight\Cookie\Session;
use GRL\Util\FlashMessages;
use PHPUnit\Framework\TestCase;

/**
 * Description
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class FlashMessagesTest extends TestCase
{
	private $flashBag;

	protected function setUp()
	{
		$this->flashBag = new FlashMessages();
	}

	public function testConstructorInitializesMessageBag()
	{
		$this->assertTrue(Session::has(FlashMessages::MESSAGE_BAG_NAME), 'Constructor must initialize messageBag in the session');
	}

	public function testSetMessagePrefix()
	{
		$message = 'Lorem ipsum';
		$messagePrefix = 'New Prefix: ';
		$this->flashBag->setMessagePrefix($messagePrefix);
		$this->flashBag->add($message);

		$expectedMessage = '<div class="flash-msg">' . $messagePrefix . $message . '</div>';
		$this->assertEquals($expectedMessage, $this->flashBag->getMessages(), '->setPrefix() must set message prefix');
	}

	public function testAddHasGetMessages()
	{
		$message = 'Lorem ipsum';

		$this->assertFalse($this->flashBag->hasMessages(), 'Flashbag should not contain any messages by default');
		$this->flashBag->add($message);
		$this->assertTrue($this->flashBag->hasMessages(), 'Flashbag must contain a message when ->add() processed');

		$expectedResult = '<div class="flash-msg">' . FlashMessages::DEFAULT_MESSAGE_PREFIX . $message . '</div>';
		$this->assertEquals($expectedResult, $this->flashBag->getMessages(), '->setGetMessages() must get formatted message from flashBag');
	}
}
