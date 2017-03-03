<?php

/*
 * This file is part of the SearchBundle for Symfony2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Mmoreram\SearchBundle\Result;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

use Mmoreram\SearchBundle\Query\Filter;

/**
 * Class Aggregation.
 */
class Aggregation implements IteratorAggregate
{
    /**
     * @var string
     *
     * Name
     */
    private $name;

    /**
     * @var Counter[]
     *
     * Counters
     */
    private $counters = [];

    /**
     * @var int
     *
     * Aggregation type
     */
    private $type;

    /**
     * @var int
     *
     * Total elements
     */
    private $totalElements;

    /**
     * @var array
     *
     * Active elements
     */
    private $activeElements;

    /**
     * @var int
     *
     * Lowest level
     */
    private $lowestLevel;

    /**
     * Aggregation constructor.
     *
     * @param string $name
     * @param int    $type
     * @param int    $totalElements
     * @param array  $activeElements
     */
    public function __construct(
        string $name,
        int $type,
        int $totalElements,
        array $activeElements
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->totalElements = $totalElements;
        $this->activeElements = array_flip($activeElements);
    }

    /**
     * Add aggregation counter.
     *
     * @param string $name
     * @param int    $counter
     * @param array  $activeElements
     */
    public function addCounter(
        string $name,
        int $counter,
        array $activeElements
    ) {
        $counter = Counter::createByActiveElements(
            $name,
            $counter,
            $activeElements
        );

        /**
         * The entry is used.
         * This block should take in account when the filter is of type
         * levels, but only levels.
         */
        if (
            $this->type & Filter::MUST_ALL_WITH_LEVELS &&
            $this->type & ~Filter::MUST_ALL &&
            $counter->isUsed()
        ) {
            $this->activeElements[$counter->getId()] = $counter;

            return;
        }

        $this->counters[$counter->getId()] = $counter;
        $this->lowestLevel = is_null($this->lowestLevel)
            ? $counter->getLevel()
            : min($this->lowestLevel, $counter->getLevel());
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get counters.
     *
     * @return Counter[]
     */
    public function getCounters(): array
    {
        return $this->counters;
    }

    /**
     * Return if the aggregation belongs to a filter.
     *
     * @return bool
     */
    public function isFilter(): bool
    {
        return $this->type === Filter::MUST_ALL;
    }

    /**
     * Aggregation has levels.
     *
     * @return bool
     */
    public function hasLevels() : bool
    {
        return (bool) ($this->type & Filter::MUST_ALL_WITH_LEVELS);
    }

    /**
     * Get counter.
     *
     * @param string $name
     *
     * @return null|Counter
     */
    public function getCounter(string $name) : ? Counter
    {
        return $this->counters[$name] ?? null;
    }

    /**
     * Get total elements.
     *
     * @return int
     */
    public function getTotalElements() : int
    {
        return $this->totalElements;
    }

    /**
     * Get active elements.
     *
     * @return array
     */
    public function getActiveElements(): array
    {
        if (empty($this->activeElements)) {
            return [];
        }

        if ($this->type === FILTER::MUST_ALL_WITH_LEVELS) {
            $value = [array_reduce(
                $this->activeElements,
                function ($carry, $counter) {
                    if (!$counter instanceof Counter) {
                        return $carry;
                    }

                    if (!$carry instanceof Counter) {
                        return $counter;
                    }

                    return $carry->getLevel() > $counter->getLevel()
                        ? $carry
                        : $counter;
                }, null)];

            return is_null($value)
                ? []
                : $value;
        }

        return $this->activeElements;
    }

    /**
     * Sort by value.
     */
    public function sortByName()
    {
        usort($this->counters, function (Counter $a, Counter $b) {
            return $a->getName() > $b->getName();
        });
    }

    /**
     * Clean results by level and remove all levels higher than the lowest.
     */
    public function cleanCountersByLevel()
    {
        foreach ($this->counters as $pos => $counter) {
            if ($counter->getLevel() !== $this->lowestLevel) {
                if ($counter->isUsed()) {
                    $this->activeElements[] = $counter;
                }
                unset($this->counters[$pos]);
            }
        }
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *                     <b>Traversable</b>
     *
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->counters);
    }
}