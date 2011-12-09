<?php

namespace Jackalope\Query;

use PHPCR\RepositoryException;

/**
 * {@inheritDoc}
 *
 * Jackalope: Contrary to most other Jackalope classes, the Row implements
 * Iterator and not IteratorAggregate to avoid overhead when iterating over a
 * row.
 *
 * @api
 */
class Row implements \Iterator, \PHPCR\Query\RowInterface
{
    /**
     * @var \Jackalope\ObjectManager
     */
    protected $objectmanager;

    /**
     * @var \Jackalope\Factory
     */
    protected $factory;

    /**
     * Columns of this result row: array of array with fields dcr:name and
     * dcr:value
     * @var array
     */
    protected $columns = array();

    /**
     * Which column we are on when iterating over the columns
     * @var integer
     */
    protected $position = 0;

    /**
     * The score this row has
     * @var float
     */
    protected $score = array();

    /**
     * Cached list of values extracted from columns to avoid double work.
     * @var array
     * @see Row::getValues()
     */
    protected $values = array();

    /**
     * The default selector name
     * @var string
     */
    protected $defaultSelectorName;

    /**
     * Create new Row instance.
     *
     * @param object $factory an object factory implementing "get" as
     *      described in \Jackalope\Factory
     * @param ObjectManager $objectManager
     * @param array $columns array of array with fields dcr:name and dcr:value
     */
    public function __construct($factory, $objectmanager, $columns)
    {
        $this->factory = $factory;
        $this->objectmanager = $objectmanager;

        foreach ($columns as $column) {
            $selectorName = '';
            if (isset($column['dcr:selectorName'])) {
                $selectorName = $column['dcr:selectorName'];
            } else {
                $pos = strpos($column['dcr:name'], '.');
                if (false !== $pos) {
                    $selectorName = substr($column['dcr:name'], 0, $pos);
                    $column['dcr:name'] = substr($column['dcr:name'], $pos+1);
                }
            }

            if ('jcr:score' === $column['dcr:name']) {
                $this->score[$selectorName] = (float) $column['dcr:value'];
            } elseif ('jcr:primaryType' === substr($column['dcr:name'], -15)) {
                $this->defaultSelectorName = $selectorName;
            } else {
                $this->columns[] = $column;
                $this->values[$selectorName][$column['dcr:name']] = $column['dcr:value'];
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getValues()
    {
        $values = array();
        foreach ($this->values as $selectorName => $columns) {
            foreach ($columns as $key => $value) {
                $values[$selectorName.'.'.$key] = $value;
            }
        }

        return $values;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getValue($columnName)
    {
        if (false === strpos($columnName, '.')) {
            $columnName = $this->defaultSelectorName.'.'.$columnName;
        }

        $values = $this->getValues();
        if (!array_key_exists($columnName, $values)) {
            throw new \PHPCR\ItemNotFoundException("Column '$columnName' not found");
        }

        return $values[$columnName];
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getNode($selectorName = null)
    {
        return $this->objectmanager->getNode($this->getPath($selectorName));
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getPath($selectorName = null)
    {
        if (null === $selectorName) {
            $selectorName = $this->defaultSelectorName;
        }

        return $this->getValue($selectorName.'.jcr:path', $selectorName);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function getScore($selectorName = null)
    {
        if (null === $selectorName) {
            $selectorName = $this->defaultSelectorName;
        }

        if (!isset($this->score[$selectorName])) {
            throw new RepositoryException('Attempting to get the score for a non existent selector: '.$selectorName);
        }

        return $this->score[$selectorName];
    }

    /**
     * Implement Iterator
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Implement Iterator
     */
    public function current()
    {
        return $this->columns[$this->position]['dcr:value'];
    }

    /**
     * Implement Iterator
     */
    public function key()
    {
        return $this->columns[$this->position]['dcr:name'];
    }

    /**
     * Implement Iterator
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Implement Iterator
     */
    public function valid()
    {
        return isset($this->columns[$this->position]);
    }
}
