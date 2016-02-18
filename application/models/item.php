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

    public function total($user_id) {
        $c = 0;$r = 0; $a = 0;
        $stats = Stat::all(array("item_id = ?" => $this->id, "user_id = ?" => $user_id), array("click", "amount", "rpm"));
        foreach ($stats as $s) {
            $c += $s->click;
            $a += $s->amount;
            $r += $s->rpm;
        }
        return array(
            "click" => $c,
            "amount" => $a,
            "rpm" => $r/count($stats)
        );
    }
}
