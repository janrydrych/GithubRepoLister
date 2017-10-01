<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL;

use GRL\Configuration\Configuration;
use GRL\Configuration\Services;
use \InvalidArgumentException;
use GRL\Util\FlashMessages;
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
	private $parameters;

	/**
	 * @var array
	 */
	private $services = array();

	/**
	 * @var bool
	 */
	private $initialized = false;

	/**
	 * DIC constructor.
	 *
	 * @param Configuration $configuration
	 * @param Services $services
	 */
	public function __construct(Configuration $configuration = null, Services $services = null)
	{
		if (isset($configuration)) {
			$this->initializeDIC($configuration, $services);
			$this->setInitialized();
		}
	}

	/**
	 * Initialize DIC
	 *
	 * @param Configuration $configuration
	 * @param Services $services
	 */
	public function initializeDIC(Configuration $configuration = null, Services $services = null)
	{
		if ($this->isInitialized()) { return; }
		if (isset($configuration)) { $configuration->toDIC($this); }
		if (isset($configuration, $services)) { $services->toDIC($this); }
	}

	/**
	 * Initialize DIC
	 */
	public function setInitialized()
	{
		$this->initialized = true;
	}

	/**
	 * Check if DIC is already initialized
	 *
	 * @return bool
	 */
	public function isInitialized(): bool
	{
		return $this->initialized;
	}

	/**
	 * Get specified indexed item from DIC
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get(string $key)
	{
		return isset($this->parameters[$key]) ? $this->parameters[$key] : null;
	}

	/**
	 * Set specified indexed item into DIC
	 * @param string $key
	 * @param mixed $item
	 */
	public function set(string $key, $item)
	{
		$this->parameters[$key] = $item;
	}

	/**
	 * Get specified indexed service from DIC
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getService(string $name)
	{
		return isset($this->services[ $name]) ? $this->services[ $name] : null;
	}

	/**
	 * Check if service exists
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	private function hasService(string $name): bool
	{
		return isset($this->services[$name]);
	}

	/**
	 * Set specified indexed service into DIC
	 *
	 * @param string $name
	 * @param $service
	 *
	 * @throws InvalidArgumentException
	 */
	public function addService(string $name, $service)
	{
		if ($this->hasService($name)) {
			throw new InvalidArgumentException('Service ' . $name . ' already exists.');
		} elseif (!is_object($service)) {
			throw new InvalidArgumentException(sprintf('Service %s must be an object, %s given.', $name, gettype($service)));
		}
		$this->services[ $name] = $service;
	}

	/**
	 * Remove specified indexed service from DIC
	 *
	 * @param string $name
	 */
	public function removeService(string $name)
	{
		unset($this->services[$name]);
	}

	/**
	 * Faster access for FlashMessages object
	 *
	 * @return FlashMessages|null
	 */
	public function getFlashBag()
	{
		return $this->getService('flashBag');
	}
}