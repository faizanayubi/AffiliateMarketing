<?php

/**
 * Scheduler Class which executes daily and perfoms the initiated job
 * 
 * @author Faizan Ayubi
 */

class CRON extends Auth {

    public function index() {
        $this->noview();
        $this->log("CRON Started");
        $this->verify();
        $this->log("CRON Ended");
    }
    
    protected function verify() {
        $counter = 0;
        $startdate = date('Y-m-d', strtotime("-7 day"));
        $enddate = date('Y-m-d', strtotime("now"));
        $where = array(
            "live = ?" => true,
            "created >= ?" => $startdate,
            "created <= ?" => $enddate
        );
        $links = Link::all($where, array("id", "short", "item_id", "user_id"));

        foreach ($links as $link) {
            $data = $link->stat();
            if ($data["click"] > 20) {
                $this->saveStats($data, $link);

                //sleep the script
                if ($counter == 10) {
                    sleep(1);
                    $counter = 0;
                }
                ++$counter;
            }
        }
    }

    protected function saveStats($data, $link) {
        $now = date('Y-m-d', strtotime("now"));
        $exist = Stat::first(array("link_id = ?" => $link->id, "created > ?" => $now));
        if(!$exist) {
            $stat = new Stat(array(
                "user_id" => $link->user_id,
                "link_id" => $link->id,
                "verifiedClicks" => $data["verified"],
                "shortUrlClicks" => $data["click"],
                "item_id" => $link->item_id,
                "amount" => $data["earning"],
                "rpm" => $data["rpm"]
            ));
            $stat->save();
            return $stat;
        }
    }
    
}
