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

    public function cleanand($subject) {
        return str_replace(array("&", "="), array("", ""), $subject);
    }

    public function encode($user_id, $username) {
        $e = "id=".$this->id."&title=".$this->cleanand($this->title)."&description=".$this->cleanand($this->description)."&image=".$this->cleanand($this->image)."&url=".$this->cleanand($this->url)."&username=".$this->cleanand($username)."&user_id=".$user_id."&time=".time();
        return base64_encode($e);
    }
}
