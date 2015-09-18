<?php

/**
 * Description of platform
 *
 * @author Faizan Ayubi
 */
class Platform extends Shared\Model{
    
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
     * @length 100
     * 
     * @validate required, min(3), max(32)
     * @label name
     */
    protected $_name;
    
    /**
     * @column
     * @readwrite
     * @type text
     * 
     * @validate required, alpha, min(3), max(32)
     * @label link
     */
    protected $_link;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_image;
}
