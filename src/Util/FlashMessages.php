<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL\Util;

use Delight\Cookie\Session;

/**
 * Flash messages handling
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class FlashMessages
{
	const DEFAULT_MESSAGE_PREFIX = 'Error: ';
	const MESSAGE_BAG_NAME = 'flash_messages';

	/**
	 * @var string
	 */
	private $messagePrefix = self::DEFAULT_MESSAGE_PREFIX;


	public function __construct()
	{
		$this->initializeMessageBag();
	}

	/**
	 * Initializes session array to hold messages
	 */
	private function initializeMessageBag()
	{
		if (!Session::has(self::MESSAGE_BAG_NAME)) {
			Session::set(self::MESSAGE_BAG_NAME, array());
		}
	}

	/**
	 * Set prefix for flash messages
	 *
	 * @param string $messagePrefix
	 */
	public function setMessagePrefix(string $messagePrefix)
	{
		$this->messagePrefix = $messagePrefix;
	}

	/**
	 * Add a flash message to the session data
	 *
	 * @param  string  $message
	 * @return $this|boolean
	 */
	public function add(string $message)
	{
		// Make sure a message was passed
		if (!isset($message[0])) { return false; }

		// Add the message to the session data
		$bag = Session::get(self::MESSAGE_BAG_NAME);
		$bag[] = $message;
		Session::set(self::MESSAGE_BAG_NAME, $bag);

		return $this;
	}

	/**
	 * Returns all messages from the bag formatted
	 *
	 * @return string
	 *
	 */
	public function getMessages(): string
	{
		if (!Session::has(self::MESSAGE_BAG_NAME)) { return false; }
		$output = '';
		$messageBas = (array) Session::get(self::MESSAGE_BAG_NAME);
		foreach ($messageBas as $msg) {
			$output .= $this->formatMessage($msg);
		}
		$this->clearAll();
		return $output;
	}

	/**
	 * See if there are any queued message
	 *
	 * @return bool
	 */
	public function hasMessages(): bool
	{
		return (!empty(Session::get(self::MESSAGE_BAG_NAME))) ? true : false;
	}

	/**
	 * Format a message
	 *
	 * @param  string $message
	 *
	 * @return string
	 */
	private function formatMessage(string $message): string
	{
		return '<div class="flash-msg">' .
		       $this->messagePrefix .
		       $message .
		       '</div>';
	}

	/**
	 * Clear the messages from the session
	 *
	 * @return $this
	 */
	private function clearAll()
	{
		Session::delete(self::MESSAGE_BAG_NAME);
		return $this;
	}
}