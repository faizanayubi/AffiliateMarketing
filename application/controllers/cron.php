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
        $now = strftime("%Y-%m-%d", strtotime('now'));
        $startdate = date('Y-m-d', strtotime("-15 day"));
        $enddate = date('Y-m-d', strtotime("now"));
        $where = array(
            "live = ?" => true,
            "created >= ?" => $startdate,
            "created <= ?" => $enddate
        );
        $links = Link::all($where, array("id", "short", "item_id", "user_id"));

        foreach ($links as $link) {
            $data = $link->stat($now);
            if ($data["click"] > 10) {
                $this->saveStats($data, $link);

                //sleep the script
                sleep(1);
            }
        }
    }

    protected function saveStats($data, $link) {
        $stat = Stat::first(array("link_id = ?" => $link->id));
        if(!$stat) {
            $stat = new Stat(array(
                "user_id" => $link->user_id,
                "link_id" => $link->id,
                "item_id" => $link->item_id,
                "click" => $data["click"],
                "amount" => $data["earning"],
                "rpm" => $data["rpm"]
            ));
        } else {
            $stat->click = $data["click"];
            $stat->amount = $data["earning"];
            $stat->rpm = $data["rpm"];
        }
        $stat->save();
    }
    
}
