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
     * @index
     */
    protected $_short;
    
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
    protected $_user_id;

    public function googl() {
        $googl = Framework\Registry::get("googl");
        $object = $googl->analyticsFull($this->short);
        return isset($object) ? $object : NULL;
    }

    public function mongodb($date = NULL) {
        $m = new MongoClient();
        $db = $m->stats;
        $collection = $db->hits;
        $stats = array();$stat = array();
        $doc = array(
            "item_id" => $this->item_id,
            "user_id" => $this->user_id
        );

        if ($date) {
            $doc["created"] = $date;
        }
    
        $records = $collection->find($doc);
        if (isset($records)) {
            foreach ($records as $record) {
                if (isset($stats[$record['country']])) {
                    $stats[$record['country']] += $record['click'];
                } else {
                    $stats[$record['country']] = $record['click'];
                }
            }

            foreach ($stats as $key => $value) {
                array_push($stat, array(
                    "country" => $key,
                    "count" => $value
                ));
            }
            
            return $stat;
        } else{
            return 0;
        }
    }

    public function stat($date = NULL) {
        $total_click = 0;
        $earning = 0;
        $analytics = array();
        $countries = array("IN", "US", "CA", "AU","GB");
        $return = array("click" => 0, "rpm" => 0, "earning" => 0, "analytics" => 0);

        
        $results = $this->mongodb($date);
        if (is_array($results)) {

            //rpm
            $rpms = RPM::first(array("item_id = ?" => $this->item_id), array("value"));
            $rpm = json_decode($rpms->value);

            foreach ($results as $result) {
                $code = $result["country"];
                $total_click += $result["count"];
                if (in_array($code, $countries)) {
                    $earning += ($rpm->$code)*($result["count"])/1000;
                    $analytics[$code] += $result["count"];
                } else {
                    $earning += ($rpm->NONE)*($result["count"])/1000;
                    $analytics["NONE"] += $result["count"];
                }
            }

            if ($total_click > 0) {
                $return = array(
                    "click" => round($total_click),
                    "rpm" => round($earning*1000/$total_click, 2),
                    "earning" => round($earning, 2),
                    "analytics" => $analytics
                );
            }
        }
        
        return $return;
    }
}
