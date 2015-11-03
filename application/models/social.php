<?php

/**
 * Description of social
 *
 * @author Faizan Ayubi
 */
class Social extends Shared\Model {
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
     * @length 64
     */
    protected $_platform;
    
    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_link;
}