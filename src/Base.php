<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL;

/**
 * Base class to provide DIC
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
abstract class Base
{
	/**
	 * @var DIC
	 */
	private $dic;

	/**
	 * Base constructor.
	 *
	 * @param DIC $dic
	 */
	public function __construct(DIC $dic)
	{
		$this->dic = $dic;
	}

	/**
	 * @return DIC
	 */
	public function getDIC(): DIC
	{
		return $this->dic;
	}
}