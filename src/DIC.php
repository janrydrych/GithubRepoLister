<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL;

use Delight\Auth\Auth;
use GRL\Storage\DataStorageInterface;
use GRL\Util\FlashMessages;
use GRL\Util\Paginator;

/**
 * Description
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class DIC
{
	/**
	 * @var array
	 */
	private $dic = array();

	/**
	 * @var bool
	 */
	private $initialized = false;

	/**
	 * DIC constructor.
	 *
	 * @param array|null $configuration
	 */
	public function __construct(array $configuration = null)
	{
		$this->dic = $configuration ?: array();
	}

	/**
	 * Check if DIC is already initialized
	 *
	 * @return bool
	 */
	public function isInitialized()
	{
		return $this->initialized;
	}

	/**
	 * Set DIC state to initialized
	 */
	public function setInitialized()
	{
		$this->initialized = true;
	}

	/**
	 * Get specified indexed item from DIC
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function get(string $key)
	{
		return isset($this->dic[$key]) ? $this->dic[$key] : null;
	}

	/**
	 * Set specified indexed itme info DIC
	 * @param string $key
	 * @param $item
	 */
	public function set(string $key, $item)
	{
		$this->dic[$key] = $item;
	}

	/**
	 * Faster access for FlashMessages object
	 *
	 * @return FlashMessages
	 */
	public function getFlashBag(): FlashMessages
	{
		return $this->get('flashBag');
	}
}