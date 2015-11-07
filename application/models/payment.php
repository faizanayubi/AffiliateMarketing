<?php

/**
 * The payment Model
 *
 * @author Faizan Ayubi
 */
class Payment extends Shared\Model {

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
     * @type text
     * @length 255
     */
    protected $_amount;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 64
     */
    protected $_mode;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 64
     */
    protected $_ref_id;

    /**
    * @column
    * @readwrite
    * @type boolean
    */
    protected $_requested = false;

}
