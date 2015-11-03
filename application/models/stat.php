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
    protected $_link_id;

    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_verifiedClicks;
    
    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_shortUrlClicks;
    
    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_longUrlClicks;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_referrers;
    
    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_countries;
    
    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_browsers;
    
    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_platforms;
}
