<?php

/**
 * Scheduler Class which executes daily and perfoms the initiated job
 * 
 * @author Faizan Ayubi
 */

class CRON extends Auth {

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->willRenderLayoutView = false;
        $this->willRenderActionView = false;
    }

    public function index() {
        $this->verify();
    }
    
    protected function verify() {
        $startdate = date('Y-m-d', strtotime("-10 day"));
        $enddate = date('Y-m-d', strtotime("now"));
        $where = array(
            "live = ?" => true,
            "created >= ?" => $startdate,
            "created <= ?" => $enddate
        );
        $links = Link::all($where, array("id", "short", "item_id", "user_id"));
        $total = Link::count($where);

        $counter = 0;
        $googl = Framework\Registry::get("googl");
        foreach ($links as $link) {
            $object = $googl->analyticsFull($link->short);
            $count = $object->analytics->day->shortUrlClicks;
            //minimum count for earning
            if ($count > 50) {
                $item = Item::first(array("id = ?" => $link->item_id), array("commission"));
                $stat = $this->saveStats($object, $link, $count);
                $this->saveEarnings($link, $count, $stat, $object, $item);
            }

            //sleep the script
            if ($counter == 100) {
                sleep(30);
                $counter = 0;
            }
            ++$counter;
        }
    }

    protected function saveStats($object, $link, $count) {
        $stat = new Stat(array(
            "link_id" => $link->id,
            "verifiedClicks" => $count,
            "shortUrlClicks" => $object->analytics->day->shortUrlClicks,
            "longUrlClicks" => $object->analytics->day->longUrlClicks,
            "referrers" => serialize($object->analytics->day->referrers),
            "countries" => serialize($object->analytics->day->countries),
            "browsers" => serialize($object->analytics->day->browsers),
            "platforms" => serialize($object->analytics->day->platforms)
        ));
        $stat->save();
        return $stat;
    }
    
    protected function saveEarnings($link, $count, $stat, $object, $item) {
        $countries = $object->analytics->allTime->countries;
        $rpms = RPM::all(array("item_id = ?" => $link->item_id), array("value", "country"));
        $revenue = 0;
        
        foreach ($rpms as $rpm) {
            foreach ($countries as $country) {
                if(strtoupper($rpm->country) == $country->id) {
                    $revenue += ($rpm->value)*($country->count)/1000;
                    $country_count += $country->count;
                }
            }
            if ($rpm->country == "NONE") {
                $revenue += ($count - $country_count)*$correct*($rpm->value)/1000;
            }
        }

        $avgrpm = round(($revenue*1000)/($count), 2);
        $revenue = ($revenue * (100 - ($item->commission)))/100;
        $earning = new Earning(array(
            "item_id" => $link->item_id,
            "link_id" => $link->id,
            "amount" => $revenue,
            "user_id" => $link->user_id,
            "stat_id" => $stat->id,
            "rpm" => $avgrpm
        ));
        $earning->save();
    }

}
