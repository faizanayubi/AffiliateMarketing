<?php

/**
 * Description of link
 *
 * @author Faizan Ayubi
 */
class Link extends Shared\Model {
    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_short;
    
    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_item_id;
    
    /**
     * @column
     * @readwrite
     * @type integer
     */
    protected $_user_id;

    public static function findStats($shortURL) {
        $googl = Framework\Registry::get("googl");
        $object = $googl->analyticsFull($shortURL);
        return $object;
    }
}
