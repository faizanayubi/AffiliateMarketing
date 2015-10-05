<?php
/**
 * Description of earning
 *
 * @author Faizan Ayubi
 */
class Earning extends \Shared\Model {
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
     * @length 255
     */
    protected $_amount;
    
    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_user_id;
    
    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_stat_id;

    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_rpm_id;
}
