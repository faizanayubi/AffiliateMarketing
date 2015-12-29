<?php

/**
 * Description of stat
 *
 * @author Faizan Ayubi
 */
class Stat extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_link_id;

    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_click;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_item_id;

    
    /**
     * @column
     * @readwrite
     * @type decimal
     * @lenght 10,2
     */
    protected $_amount;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @lenght 10,2
     */
    protected $_rpm;
}
