<?php

/**
 * Description of meta
 *
 * @author Faizan Ayubi
 */
class Meta extends Shared\Model {
    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_user_id;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_key;
    
    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_value;
}