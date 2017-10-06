<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace GRL\Util;
/**
 * Paginating math.
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class Paginator
{
	const DEFAULT_ITEMS_PER_PAGE = 5;
	const DEFAULT_STEPS_AROUND_CURRENT_PAGE = 5;

    /**
     * First page (base) number
     * @var int
     */
    private $base = 1;

    /**
     * Number of items to display on one page
     * @var int
     */
    private $itemsPerPage;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * Number of items to paginate
     * @var int|null
     */
    private $itemCount;

    /**
     * Number of buttons before and after actual page button
     * @var int
     */
    private $stepsAroundCurrentPage;

    /**
    * @param int $itemsPerPage
    * @param int $stepsAroundCurrentPage
    */
    public function __construct(int $itemsPerPage = null, int $stepsAroundCurrentPage = null)
    {
	    (null !== $itemsPerPage) ? $this->setItemsPerPage($itemsPerPage) : $this->itemsPerPage = self::DEFAULT_ITEMS_PER_PAGE;
	    (null !== $stepsAroundCurrentPage) ? $this->setStepsAroundCurrentPage($stepsAroundCurrentPage) : $this->stepsAroundCurrentPage = self::DEFAULT_STEPS_AROUND_CURRENT_PAGE;
    }

    /**
     * Sets first page (base) number.
     *
     * @param int $base
     *
     * @return $this
     */
    public function setBase($base)
    {
        $this->base = (int) $base;
        return $this;
    }

    /**
     * Returns first page (base) number.
     *
     * @return int
     */
    public function getBase(): int
    {
        return $this->base;
    }

    /**
     * Sets the number of items to display on a single page.
     *
     * @param int $itemsPerPage
     *
     * @return $this
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = max(1, (int) $itemsPerPage);
        return $this;
    }

    /**
     * Returns the number of items to display on a single page.
     *
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * Sets the number of buttons around actual page button
     *
     * @param int $stepsAroundCurrentPage
     *
     * @return $this
     */
    public function setStepsAroundCurrentPage($stepsAroundCurrentPage)
    {
        $this->stepsAroundCurrentPage = max(0, (int) $stepsAroundCurrentPage);
        return $this;
    }

    /**
     * Returns the number of buttons around actual page button
     *
     * @return int
     */
    public function getStepsAroundCurrentPage(): int
    {
        return $this->stepsAroundCurrentPage;
    }

    /**
     * Sets the total number of items.
     *
     * @param  int (or null as infinity)
     *
     * @return $this
     */
    public function setItemCount($itemCount)
    {
        if ($itemCount === false || $itemCount === null) {
            $this->itemCount = null;
        } else {
            $this->itemCount = max(0, (int) $itemCount);
        }
        return $this;
    }

    /**
     * Returns the total number of items.
     *
     * @return int|null
     */
    public function getItemCount()
    {
        return $this->itemCount;
    }

    /**
     * Sets current page number.
     *
     * @param int $currentPage
     *
     * @return $this
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = (int) $currentPage;
        return $this;
    }

    /**
     * Returns current page number.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->base + $this->getPageIndex();
    }

    /**
     * Returns first page number.
     *
     * @return int
     */
    public function getFirstPage(): int
    {
        return $this->base;
    }

    /**
     * Returns previous page number.
     *
     * @return int|null
     */
    public function getPreviousPage()
    {
        if ($this->itemCount === null) {
            return null;
        } else {
            return max($this->getFirstPage(), $this->getCurrentPage() - 1);
        }
    }

    /**
     * Returns next page number.
     *
     * @return int|null
     */
    public function getNextPage()
    {
        if ($this->itemCount === null) {
            return null;
        } else {
            return min($this->getLastPage(), $this->getCurrentPage() + 1);
        }
    }

    /**
     * Returns last page number.
     *
     * @return int|null
     */
    public function getLastPage()
    {
        if ($this->itemCount === null) {
            return null;
        } else {
            return $this->base + max(0, $this->getPageCount() - 1);
        }
    }

    /**
     * Returns zero-based page number.
     *
     * @return int
     */
    protected function getPageIndex(): int
    {
        $index = max(0, $this->currentPage - $this->base);
        if ($this->itemCount === null) {
            return $index;
        } else {
            return min($index, max(0, $this->getPageCount() - 1));
        }
    }

    /**
     * Is the current page the first one?
     *
     * @return bool
     */
    public function isFirstPage(): bool
    {
        return $this->getPageIndex() === 0;
    }

    /**
     * Is the current page the last one?
     *
     * @return bool
     */
    public function isLastPage(): bool
    {
        if ($this->itemCount === null) {
            return false;
        } else {
            return $this->getPageIndex() >= $this->getPageCount() - 1;
        }
    }

    /**
     * Returns the total number of pages.
     *
     * @return int|null
     */
    public function getPageCount()
    {
        if ($this->itemCount === null) {
            return null;
        } else {
            return (int) ceil($this->itemCount / $this->itemsPerPage);
        }
    }

    /**
     * Returns the absolute index of the first item on current page.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->getPageIndex() * $this->itemsPerPage;
    }

    /**
     * Returns the absolute index of the first item on current page in countdown paging.
     *
     * @return int|null
     */
    public function getCountdownOffset()
    {
        if ($this->itemCount === null) {
            return null;
        } else {
            return max(0, $this->itemCount - ($this->getPageIndex() + 1) * $this->itemsPerPage);
        }
    }

    /**
     * Returns the number of items on current page.
     *
     * @return int
     */
    public function getItemCountForCurrentPage(): int
    {
        if ($this->itemCount === null) {
            return $this->itemsPerPage;
        } else {
            return min($this->itemsPerPage, $this->itemCount - $this->getPageIndex() * $this->itemsPerPage);
        }
    }

    /**
     * Returns array of steps
     *
     * @return array|null
     */
    public function getSteps()
    {
        if ($this->getPageCount() < 2) {
            return null;
        } else {
            $steps=range(
                max($this->getFirstPage(), $this->getCurrentPage() - $this->stepsAroundCurrentPage),
                min($this->getLastPage(), $this->getCurrentPage() + $this->stepsAroundCurrentPage)
            );

            if (!in_array($this->getFirstPage(), $steps)) {
                $steps = array_merge((array)$this->getFirstPage(), $steps);
            }
            if(!in_array($this->getLastPage(), $steps)) {
                $steps = array_merge($steps, (array)$this->getLastPage());
            }
        }
        return $steps;
    }
}