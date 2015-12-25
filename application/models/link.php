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

    public function mongodb($doc) {
        $m = new MongoClient();
        $db = $m->stats;
        $collection = $db->hits;
        $stats = array();$stat = array();
    
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

    public function stat($duration = "allTime", $mongodb = false) {
        $domain_click = 0;
        $country_click = 0;
        $earning = 0;
        $avgrpm = array();
        $verified = 0;
        $code = "";
        $country_code = array("IN", "US", "CA", "AU","GB");
        $return = array("click" => 0, "rpm" => 0, "earning" => 0, "verified" => 0);
        
        $stat = $this->googl($this->short);
        if(is_object($stat)) {
            $googl = $stat->analytics->$duration;
            $total_click = $googl->shortUrlClicks;

            if ($total_click) {
                $referrers = $googl->referrers;
                foreach ($referrers as $referer) {
                    if ($referer->id == 'chocoghar.com') {
                        $domain_click = $referer->count;
                    }
                }
                $total_click -= $domain_click;

                //commision
                $meta = Meta::first(array("property = ?" => "commision"), array("value"));
                $commision = 1 - ($meta->value)/100;

                $countries = isset($googl->countries) ? $googl->countries : NULL;
                $rpms = RPM::first(array("item_id = ?" => $this->item_id), array("value"));
                $rpm = json_decode($rpms->value);
                if ($countries) {
                    foreach ($countries as $country) {
                        if (in_array($country->id, $country_code)) {
                            $code = $country->id;
                            $e = ($rpm->$code)*($country->count)*($commision)/1000;
                            $earning += $e;
                            $c = $country->count;
                            $country_click += $c;
                            array_push($avgrpm, ($e*1000/$c));
                        }
                    }
                }

                if($total_click > $country_click) {
                    $earning += ($rpm->NONE)*($total_click - $country_click)*($commision)/1000;
                }

                if (count($avgrpm) > 0) {
                    $frpm = array_sum($avgrpm) / count($avgrpm);
                } else {
                    $frpm = $earning*1000/$total_click;
                }

                $return = array(
                    "click" => round($total_click*$commision),
                    "rpm" => round($frpm, 2),
                    "earning" => round($earning, 2),
                    "verified" => $verified
                );
            }
        }
        return $return;
    }
}
