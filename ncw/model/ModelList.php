<?php
/**
 * Contains the ModelList class.
 *
 * PHP Version 5.2
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * LICENSE: All rights reserved, particularly the rights for copying
 * and publishing as well as translating. No part of this software is
 * allowed to be copied or published with out the acceptance of Netzcraftwerk UG
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 1997-2008 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version   SVN: $Id$
 * @link      http://www.netzcraftwerk.com
 * @since     File available since Release 0.1
 * @modby     $LastChangedBy$
 * @lastmod   $LastChangedDate$
 */
/**
 * The ModelList class.
 *
 * @category  Netzcraftwerk
 * @package   Ncw
 * @author    Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright 2007-2009 Netzcraftwerk UG
 * @license   http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link      http://www.netzcraftwerk.com
 */
class Ncw_ModelList extends Ncw_Object implements IteratorAggregate, Countable, ArrayAccess
{

    /**
     * The added models
     *
     * @var Array
     */
    protected $models = array();

    /**
     * Cast the model list to an array.
     *
     * @return Array
     */
    public function toArray ()
    {
        return $this->models;
    }

    /**
     * Add a model to the list
     *
     * @param Ncw_Model $model the model to add
     *
     * @return void
     */
    public function addModel (Ncw_DataModel $model)
    {
        $this->models[] = $model;
    }

    /**
     * ArrayAccess offsetSet method
     *
     * @param mixed $offset the offset
     * @param mixed $value  the value to set
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet ($offset, $value)
    {
        if (true === isset($offset)) {
            $this->models[$offset] = $value;
        } else {
            $this->models[] = $value;
        }
    }

    /**
     * ArrayAccess offsetExists method
     *
     * @param mixed $offset the offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetExists ($offset)
    {
        return isset($this->models[$offset]);
    }

    /**
     * ArrayAccess offsetUnset method
     *
     * @param mixed $offset the offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset ($offset)
    {
        unset($this->models[$offset]);
    }

    /**
     * ArrayAccess offsetGet method
     *
     * @param mixed $offset the offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet ($offset)
    {
        return isset($this->models[$offset]) ? $this->models[$offset] : null;
    }

    /**
     * Return the Iterater object
     *
     * @return ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->models);
    }

    /**
     * Count the models in this list.
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count ()
    {
        return count($this->models);
    }
}
?>
