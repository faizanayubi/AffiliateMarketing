<?php

/**
 * Description of category
 *
 * @author Faizan Ayubi
 */
class Category extends Shared\Model {
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
     * @index
     */
    protected $_category;
    
    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_item_id;
}