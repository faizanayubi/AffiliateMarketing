<?php

/**
 * Description of analytics
 *
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Finance extends Admin {

    /**
     * All earnings records of persons
     * 
     * @before _secure, changeLayout
     */
    public function records() {
        $this->seo(array("title" => "Records Finance", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $accounts = array();
        $startdate = RequestMethods::get("date", date('Y-m-d', strtotime("-7 day")));
        $enddate = RequestMethods::get("date", date('Y-m-d', strtotime("now")));
        $live = RequestMethods::get("live", 0);
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        
        $where = array(
            "live = ?" => $live,
            "created >= ?" => $startdate,
            "created <= ?" => $enddate
        );
        $earnings = Earning::all($where, array("DISTINCT user_id", "created"), "created", "asc", $limit, $page);
        $count = Earning::count($where);
        foreach ($earnings as $earning) {
            $amount = 0;
            $earns = Earning::all(array("user_id = ?" => $earning->user_id, "live = ?" => $live), array("amount"));
            foreach ($earns as $earn) {
                $amount += $earn->amount;
            }
            array_push($accounts, \Framework\ArrayMethods::toObject(array(
                "user_id" => $earning->user_id,
                "amount" => $amount,
                "created" => $earning->created
            )));
        }
        
        $view->set("accounts", $accounts);
        $view->set("startdate", $startdate);
        $view->set("enddate", $enddate);
        $view->set("count", $count);
        $view->set("page", $page);
        $view->set("limit", $limit);
        $view->set("live", $live);
    }

    /**
     * Finds the earning from a website
     * @before _secure, changeLayout
     */
    public function earnings() {
        $this->seo(array("title" => "Earnings Finance", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $startdate = RequestMethods::get("date", date('Y-m-d', strtotime("-7 day")));
        $enddate = RequestMethods::get("date", date('Y-m-d', strtotime("now")));
        $website = RequestMethods::get("website", "http://kapilsharmafc.com");

        $amount = 0;
        $where = array(
            "url LIKE ?" => "%{$website}%",
            "created >= ?" => $this->changeDate($startdate, "-1"),
            "created <= ?" => $this->changeDate($enddate, "1")
        );
        $items = Item::all($where, array("id"));

        foreach ($items as $item) {
            $earnings = Earning::all(array("item_id = ?" => $item->id), array("amount"));
            foreach ($earnings as $earning) {
                $amount += $earning->amount;
            }
        }
        
        $view->set("startdate", $startdate);
        $view->set("enddate", $enddate);
        $view->set("items", $items);
        $view->set("website", $website);
        $view->set("amount", $amount);
    }

    /**
     * Earning on a Content
     * @before _secure, changeLayout
     */
    public function content($id='') {
        $this->seo(array("title" => "Content Finance", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $item = Item::first(array("id = ?" => $id));

        $earn = 0;
        $earnings = Earning::all(array("item_id = ?" => $item->id), array("amount"));
        foreach ($earnings as $earning) {
            $earn += $earning->amount;
        }

        $links = Link::count(array("item_id = ?" => $item->id));
        $rpm = RPM::count(array("item_id = ?" => $item->id));

        $view->set("item", $item);
        $view->set("earn", $earn);
        $view->set("links", $links);
        $view->set("rpm", $rpm);
    }
    
}
