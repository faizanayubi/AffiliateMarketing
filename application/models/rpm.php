<?php

/**
 * Description of rpm
 *
 * @author Faizan Ayubi
 */
class RPM extends Shared\Model {
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
     * @type text
     * @length 50
     */
    protected $_value;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 50
     */
    protected $_country;

    public static function average($item_id) {
        $rpms = RPM::all(array("item_id = ?" => $item_id), array("value"));
        $value = 0;
        foreach ($rpms as $rpm) {
            $value += $rpm->value;
        }
        return $value/count($rpms);
    }
}
