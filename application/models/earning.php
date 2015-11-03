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
     * @index
     */
    protected $_item_id;

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
     * @type text
     * @length 255
     */
    protected $_amount;
    
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
     * @type integer
     * @index
     */
    protected $_stat_id;

    /**
     * @column
     * @readwrite
     * @type decimal
     * @lenght 10,3
     */
    protected $_rpm;

    public static function total($link_id) {
        $total = 0;
        $stats = Stat::all(array("link_id = ?" => $link_id), array("id"));
        foreach ($stats as $stat) {
            $earning = Earning::first(array("stat_id = ?" => $stat->id), array("amount"));
            $total += $earning->amount;
        }
        return $total;
    }
}
