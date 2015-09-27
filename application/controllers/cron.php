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
        $this->_secure();
        $this->verify();
    }
    
    protected function verify() {
        $links = Link::all(array("live = ?" => true));
        $googl = Framework\Registry::get("googl");
        foreach ($links as $link) {
            $count = 0;
            $object = $googl->analyticsFull($link->short);
            foreach ($object->analytics->day->referrers as $referer) {
                if($referer->id == "l.facebook.com") {
                    $count += $referer->count;
                }
            }
            $stat = $this->saveStats($object, $link, $count);
            $this->saveEarnings($link, $count, $stat);
        }
    }

    protected function saveStats($object, $link, $count) {
        $stat = new Stat(array(
            "link_id" => $link->id,
            "verifiedClicks" => $count,
            "shortUrlClicks" => $object->analytics->day->shortUrlClicks,
            "longUrlClicks" => $object->analytics->day->longUrlClicks,
            "referrers" => $object->analytics->day->referrers,
            "countries" => $object->analytics->day->countries,
            "browsers" => $object->analytics->day->browsers,
            "platforms" => $object->analytics->day->platforms
        ));
        $stat->save();
        return $stat;
    }
    
    protected function saveEarnings($link, $count, $stat) {
        $rpm = RPM::first(array("item_id = ?" => $link->item_id));
        $amount = $count*$rpm->value/1000;
        $earning = new Earning(array(
            "item_id" => $link->item_id,
            "amount" => $amount,
            "user_id" => $link->user_id,
            "stat_id" => $stat->id
        ));
        $earning->save();
    }

    /**
     * @protected
     */
    public function _secure() {
        if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) {
            die('access is not permitted');
        }
    }

}
