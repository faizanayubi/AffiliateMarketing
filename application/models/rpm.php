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
     * @length 255
     */
    protected $_value;
    
    public static function average($item_id) {
        $rpm = RPM::first(array("item_id = ?" => $item_id), array("value"));
        $data = json_decode($rpm->value);

        return ($data->IN + $data->US + $data->CA + $data->AU + $data->GB + $data->NONE)/6;
    }
}
