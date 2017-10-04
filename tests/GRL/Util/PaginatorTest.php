<?php
/**
 * This file is part of the GithubRepoLister project
 * Copyright (c) Jan Rydrych <jan.rydrych@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\GRL\Util;

use GRL\Util\Paginator;
use PHPUnit\Framework\TestCase;

/**
 * Description
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class PaginatorTest extends TestCase
{
	public function testConstructorSetsDefaultWithNullArguments()
	{
		$paginator = new Paginator();

		$this->assertEquals(Paginator::DEFAULT_ITEMS_PER_PAGE,
		                    $paginator->getItemsPerPage(),
		                    'Constructor must set default itemPerPage value');
		$this->assertEquals(Paginator::DEFAULT_STEPS_AROUND_CURRENT_PAGE,
		                    $paginator->getStepsAroundCurrentPage(),
		                    'Constructor must set default stepsAroundCurrentPage value');
	}

	public function testConstructorSetsPropertiesWithArgumentsSet()
	{
		$paginator = new Paginator(999, 9);
		$this->assertEquals(999,
		                    $paginator->getItemsPerPage(),
		                    'Constructor must set itemPerPage value');
		$this->assertEquals(9,
		                    $paginator->getStepsAroundCurrentPage(),
		                    'Constructor must set stepsAroundCurrentPage value');
	}

	public function testGetSetBase()
	{
		$paginator = new Paginator();
		$paginator->setBase(8);
		$this->assertEquals(8, $paginator->getBase(), '->setBase() must set the value');

		$paginator->setBase(- 8);
		$this->assertEquals(- 8, $paginator->getBase(), '->setBase() must set the negative value');

		$paginator->setBase(8.8);
		$this->assertEquals(8, $paginator->getBase(), '->setBase() must set an int value');
	}

	public function testGetSetItemsPerPage()
	{
		$paginator = new Paginator(999);
		$this->assertEquals(999, $paginator->getItemsPerPage(), '->setItemsPerPage() must set the value');

		$paginator->setItemsPerPage(- 999);
		$this->assertEquals(1,
		                    $paginator->getItemsPerPage(),
		                    '->setItemsPerPage() must not set the value less than 1');

		$paginator->setItemsPerPage(9.9);
		$this->assertEquals(9, $paginator->getItemsPerPage(), '->setItemsPerPage() must set an int value');
	}

	public function testGetSetStepsAroundCurrentPage()
	{
		$paginator = new Paginator(999, 9);
		$this->assertEquals(9,
		                    $paginator->getStepsAroundCurrentPage(),
		                    '->setStepsAroundCurrentPage() must set the value');

		$paginator->setStepsAroundCurrentPage(- 9);
		$this->assertEquals(0,
		                    $paginator->getStepsAroundCurrentPage(),
		                    '->setStepsAroundCurrentPage() must not set the value less than 0');

		$paginator->setStepsAroundCurrentPage(9.9);
		$this->assertEquals(9,
		                    $paginator->getStepsAroundCurrentPage(),
		                    '->setStepsAroundCurrentPage() must set an int value');
	}

	public function testGetSetItemCount()
	{
		$paginator = new Paginator();
		$paginator->setItemCount(null);
		$this->assertNull($paginator->getItemCount(), '->setItemCount() must set the null');

		$paginator->setItemCount(false);
		$this->assertNull($paginator->getItemCount(), '->setItemCount() must set the null when false passed');

		$paginator->setItemCount(9);
		$this->assertEquals(9, $paginator->getItemCount(), '->setItemCount() must set the value');

		$paginator->setItemCount(- 9);
		$this->assertEquals(0, $paginator->getItemCount(), '->setItemCount() must not set the value less than 0');

		$paginator->setItemCount(9.9);
		$this->assertEquals(9, $paginator->getItemCount(), '->setItemCount() must set an int value');
	}

	public function testGetSetCurrentPage()
	{
		$paginator = new Paginator();
		$base = 99;
		$paginator->setBase($base);
		$paginator->setItemCount(null);

		$paginator->setCurrentPage(-9 + $base);
		$this->assertEquals($base, $paginator->getCurrentPage(), '->getCurrentPage() must return value equal to or greater than base');

		$paginator->setCurrentPage(9 + $base);
		$this->assertEquals(9 + $base, $paginator->getCurrentPage(), '->getCurrentPage() return correct value');

		$paginator->setCurrentPage(9.9 + $base);
		$this->assertEquals(9 + $base, $paginator->getCurrentPage(), '->getCurrentPage() must return correct int value');

		$paginator->setItemCount(99);
		$paginator->setItemsPerPage(9);
		$paginator->setCurrentPage(12 + $base);
		$this->assertEquals(10 + $base, $paginator->getCurrentPage(), '->getCurrentPage() must return value equal to or less than pageCount');
	}

	public function testGetFirstPage()
	{
		$paginator = new Paginator();
		$firstPage = 99;
		$paginator->setBase($firstPage);
		$this->assertEquals($firstPage, $paginator->getFirstPage(), '->getFirstPage() must return paginator base value');
	}

	public function testGetPreviousPage()
	{
		$paginator = new Paginator();
		$paginator->setItemCount(null);
		$this->assertNull($paginator->getPreviousPage(), '->getPreviousPage() must return null when itemCount is null');

		$paginator->setItemCount(99);
		$base = 10;
		$paginator->setBase($base);
		$paginator->setCurrentPage($base + 9);
		$this->assertEquals($base + 9 - 1, $paginator->getPreviousPage(), '->getPreviousPage() must return current page - 1');

		$paginator->setCurrentPage($base);
		$this->assertEquals($base, $paginator->getPreviousPage(), '->getPreviousPage() must not return value less than base');

		$paginator->setCurrentPage($base - 9);
		$this->assertEquals($base, $paginator->getPreviousPage(), '->getPreviousPage() must not return value less than base');
	}

	public function testGetNextPage()
	{
		$paginator = new Paginator();
		$paginator->setItemCount(null);
		$this->assertNull($paginator->getNextPage(), '->getNextPage() must return null when itemCount is null');

		$paginator->setItemCount(99);
		$base = 10;
		$paginator->setBase($base);
		$lastPage = $paginator->getLastPage();
		
		$paginator->setCurrentPage($base + 9);
		$this->assertEquals($base + 9 + 1, $paginator->getNextPage(), '->getNextPage() must return current page + 1');

		$paginator->setCurrentPage($lastPage);
		$this->assertEquals($lastPage, $paginator->getNextPage(), '->getNextPage() must not return value greater than last page');

		$paginator->setCurrentPage($lastPage + 9);
		$this->assertEquals($lastPage, $paginator->getNextPage(), '->getNextPage() must not return value greater than last page');
	}


	public function testGetLastPage()
	{
		$paginator = new Paginator();
		$paginator->setItemCount(null);
		$this->assertNull($paginator->getLastPage(), '->getLastPage() must return null when itemCount is null');

		$base = 10;
		$paginator->setBase($base);
		$paginator->setItemsPerPage(9);

		$paginator->setItemCount(99);
		$expectedLastPage = $base + $paginator->getPageCount() - 1;
		$this->assertEquals($expectedLastPage, $paginator->getLastPage(), '->getLastPage() must return valid last page');

		$paginator->setItemCount(9);
		$expectedLastPage = $base;
		$this->assertEquals($expectedLastPage, $paginator->getLastPage(), '->getLastPage() must return valid last page');
	}

	public function testGetPageCount()
	{
		$paginator = new Paginator();
		$paginator->setItemCount(null);
		$this->assertNull($paginator->getPageCount(), '->getPageCount() must return null when itemCount is null');

		$paginator->setItemCount(99);
		$paginator->setItemsPerPage(8);

		$expectedPageCount = (int) ceil(99 / 8);
		$this->assertEquals($expectedPageCount,
		                    $paginator->getPageCount(),
		                    '->getPageCount() must return valid page count');
	}
	

	public function testIsFirstPage()
	{
		$paginator = new Paginator();

		$paginator->setItemCount(null);
		$paginator->setBase(1);
		$paginator->setCurrentPage(10);
		$this->assertFalse($paginator->isFirstPage(), '->isFirstPage() must return false when currentPage is not first page');

		$paginator->setItemCount(null);
		$paginator->setBase(10);
		$paginator->setCurrentPage(10);
		$this->assertTrue($paginator->isFirstPage(), '->isFirstPage() must return true when currentPage is first page');

		$paginator->setItemCount(99);
		$paginator->setBase(0);
		$paginator->setCurrentPage(10);
		$this->assertFalse($paginator->isFirstPage(), '->isFirstPage() must return false when currentPage is not first page');

		$paginator->setItemCount(99);
		$paginator->setBase(9);
		$paginator->setCurrentPage(9);
		$this->assertTrue($paginator->isFirstPage(), '->isFirstPage() must return true when currentPage is first page');
	}

	public function testIsLastPage()
	{
		$paginator = new Paginator();

		$paginator->setItemCount(null);
		$this->assertFalse($paginator->isLastPage(), '->isLastPage() must return false when itemCount is null');

		$paginator->setItemCount(99);
		$paginator->setItemsPerPage(8);

		$paginator->setCurrentPage(5);
		$this->assertFalse($paginator->isLastPage(), '->isLastPage() must return false when currentPage is not last page');

		$paginator->setCurrentPage(14);
		$this->assertTrue($paginator->isLastPage(), '->isLastPage() must return true when currentPage is last page');
	}

	public function testGetOffsetWhenCurrentPageIsLessThanBase()
	{
		$paginator = new Paginator();

		$base = 10;
		$itemsPerPage = 8;
		$itemCount = 99;
		$paginator->setBase($base);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount($itemCount);

		$paginator->setCurrentPage(5);
		$expectedOffset = 0 * $itemsPerPage;
		$this->assertEquals($expectedOffset,
		                    $paginator->getOffset(),
		                    '->getOffset() must return correct absolute index of the first item on current page');
	}

	public function testGetOffsetWhenCurrentPageIsBetweenBaseAndPageCount()
	{
		$paginator = new Paginator();

		$base = 10;
		$itemsPerPage = 8;
		$itemCount = 99;
		$paginator->setBase($base);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount($itemCount);

		$paginator->setCurrentPage(15);
		$expectedOffset = (15 - $base) * $itemsPerPage;
		$this->assertEquals($expectedOffset,
		                    $paginator->getOffset(),
		                    '->getOffset() must return correct absolute index of the first item on current page');
	}

	public function testGetOffsetWhenCurrentPageIsGreaterThanPageCount()
	{
		$paginator = new Paginator();

		$base = 10;
		$itemsPerPage = 8;
		$itemCount = 99;
		$paginator->setBase($base);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount($itemCount);

		$paginator->setCurrentPage(25); // current page greater than pageCount
		$pageCount = (int) ceil($itemCount / $itemsPerPage);
		$expectedOffset = ($pageCount - 1) * $itemsPerPage;
		$this->assertEquals($expectedOffset,
		                    $paginator->getOffset(),
		                    '->getOffset() must return correct absolute index of the first item on current page');
	}

	public function testGetOffsetWhenCurrentPageIsGreaterThanPageCountAndItemCountIsNull()
	{
		$paginator = new Paginator();

		$base = 10;
		$itemsPerPage = 9;
		$paginator->setBase($base);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount(null);

		$paginator->setCurrentPage(25); // current page greater than pageCount and itemCount is infinite
		$expectedOffset = (25 - $base) * $itemsPerPage;
		$this->assertEquals($expectedOffset, $paginator->getOffset(), '->getOffset() must return correct absolute index of the first item on current page');
	}

	public function testGetCountdownOffsetWhenItemCountIsNull()
	{
		$paginator = new Paginator();

		$paginator->setItemCount(null);
		$this->assertNull($paginator->getCountdownOffset(),
		                   '->getCountdownOffset() must return null when itemCount is null');
	}

	public function testGetCountdownOffsetWhenCurrentPageIsGreaterThanPageCount()
	{
		$paginator = new Paginator();

		$base = 10;
		$itemsPerPage = 8;
		$itemCount = 99;
		$paginator->setBase($base);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount($itemCount);

		$paginator->setCurrentPage(25);
		$expectedOffset = 0 * $itemsPerPage;
		$this->assertEquals($expectedOffset, $paginator->getCountdownOffset(), '->getCountdownOffset() must return correct absolute index of the first item on countdown current page');
	}

	public function testGetCountdownOffsetWhenCurrentPageIsBetweenBaseAndPageCount()
	{
		$paginator = new Paginator();

		$base = 10;
		$itemsPerPage = 8;
		$itemCount = 99;
		$paginator->setBase($base);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount($itemCount);

		$paginator->setCurrentPage(15);
		$expectedOffset = $itemCount - (15 - $base + 1) * $itemsPerPage;
		$this->assertEquals($expectedOffset, $paginator->getCountdownOffset(), '->getCountdownOffset() must return correct absolute index of the first item on countdown current page');
	}

	public function testGetCountdownOffsetWhenCurrentPageIsLessThanBase()
	{
		$paginator = new Paginator();

		$base = 10;
		$itemsPerPage = 8;
		$itemCount = 99;
		$paginator->setBase($base);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount($itemCount);

		$paginator->setCurrentPage(5);
		$expectedOffset = $itemCount - (0 + 1) * $itemsPerPage;
		$this->assertEquals($expectedOffset, $paginator->getCountdownOffset(), '->getCountdownOffset() must return correct absolute index of the first item on countdown current page');
	}

	public function testGetItemCountForCurrentPageWhenItemCountIsNull()
	{
		$paginator = new Paginator();

		$base = 10;
		$itemsPerPage = 9;
		$paginator->setBase($base);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount(null);

		$this->assertEquals($itemsPerPage, $paginator->getItemCountForCurrentPage(), '->getItemCountForCurrentPage() must return correct item count for current page');
	}

	public function testGetItemCountForCurrentPageWhenCurrentPageIsNotLastPage()
	{
		$paginator = new Paginator();

		$base = 10;
		$itemsPerPage = 8;
		$itemCount = 99;
		$paginator->setBase($base);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount($itemCount);

		$paginator->setCurrentPage(15);
		$this->assertEquals($itemsPerPage, $paginator->getItemCountForCurrentPage(), '->getItemCountForCurrentPage() must return correct item count for current page');
	}

	public function testGetItemCountForCurrentPageWhenCurrentPageIsLastPage()
	{
		$paginator = new Paginator();

		$base = 10;
		$itemsPerPage = 8;
		$itemCount = 99;
		$paginator->setBase($base);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount($itemCount);

		$paginator->setCurrentPage(23);
		$expectedCount = $itemCount % $itemsPerPage;
		$this->assertEquals($expectedCount, $paginator->getItemCountForCurrentPage(), '->getItemCountForCurrentPage() must return correct item count for current page');
	}

	public function testGetStepsWhenOnlyOnePage()
	{
		$paginator = new Paginator(1);

		$paginator->setItemCount(1);
		$this->assertNull($paginator->getSteps(), '->getSteps() must return null when page count is less than 2');
	}

	public function testGetStepsWhenThreeStepsAround()
	{
		$paginator = new Paginator(1, 3);

		$paginator->setItemCount(99);
		$paginator->setCurrentPage(1);
		$expectedSteps = array(1, 2, 3, 4, 99);
		$this->assertSame($expectedSteps, $paginator->getSteps(), '->getSteps() must return correct steps array');

		$paginator->setCurrentPage(50);
		$expectedSteps = array(1, 47, 48, 49, 50, 51, 52, 53, 99);
		$this->assertSame($expectedSteps, $paginator->getSteps(), '->getSteps() must return correct steps array');

		$paginator->setCurrentPage(99);
		$expectedSteps = array(1, 96, 97, 98, 99);
		$this->assertSame($expectedSteps, $paginator->getSteps(), '->getSteps() must return correct steps array');
	}

	public function testGetStepsWhenOneStepAround()
	{
		$paginator = new Paginator(1, 1);

		$paginator->setItemCount(99);
		$paginator->setCurrentPage(1);
		$expectedSteps = array(1, 2, 99);
		$this->assertSame($expectedSteps, $paginator->getSteps(), '->getSteps() must return correct steps array');

		$paginator->setCurrentPage(50);
		$expectedSteps = array(1, 49, 50, 51, 99);
		$this->assertSame($expectedSteps, $paginator->getSteps(), '->getSteps() must return correct steps array');

		$paginator->setCurrentPage(99);
		$expectedSteps = array(1, 98, 99);
		$this->assertSame($expectedSteps, $paginator->getSteps(), '->getSteps() must return correct steps array');
	}
}
