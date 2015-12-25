<?php

/**
 * Description of item
 *
 * @author Faizan Ayubi
 */
class Item extends Shared\Model {
    
    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_url;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     * @index
     */
    protected $_title;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_image;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @length 4,2
     */
    protected $_commission;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_category;
    
    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_description;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 255
     */
    protected $_user_id;

    public function encode($username, $user_id) {
        return base64_encode("id={$this->id}&title={$this->title}&description={$this->description}&image={$this->image}&url={$this->url}&username={$username}&user_id={$user_id}");
    }
}
