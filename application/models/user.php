<?php

/**
 * The User Model
 *
 * @author Faizan Ayubi
 */
class User extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     * @index
     * 
     * @validate required, alpha, min(3), max(32)
     * @label username
     */
    protected $_username;
    
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
     * @length 255
     * @index
     * 
     * @validate required, max(255)
     * @label email address
     */
    protected $_email;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     * 
     * @validate required, alpha, min(8), max(32)
     * @label password
     */
    protected $_password;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 20
     * 
     * @validate required, max(20)
     * @label phone number
     */
    protected $_phone;
    
    /**
    * @column
    * @readwrite
    * @type boolean
    */
    protected $_admin = false;

    /**
    * @column
    * @readwrite
    * @type text
    * @length 255
    */
    protected $_domain;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_fblink;

}
