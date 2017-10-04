<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace Tests\GRL;

require_once __DIR__ . '/../../vendor/autoload.php';

use GRL\Base;
use GRL\DIC;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Base class
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class BaseTest extends TestCase
{
	public function testGetDICReturnsInstanceOfDIC()
	{
		$dicMock = $this->getMockBuilder(DIC::class)
		                  ->disableOriginalConstructor()
		                  ->getMock();

		$baseMock = $this->getMockBuilder(Base::class)
		             ->setConstructorArgs(array($dicMock))
		             ->getMockForAbstractClass();

		$this->assertSame($dicMock, $baseMock->getDIC(), '->getDIC() must return instance of GRL\DIC class');
	}
}
