<?php

/**
 * Description of rpm
 *
 * @author Faizan Ayubi
 */
class RPM extends Shared\Model {
    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_item_id;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     */
    protected $_value;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     */
    protected $_country;
}
