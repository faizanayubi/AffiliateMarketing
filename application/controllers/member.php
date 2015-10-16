<?php
/**
 * Description of auth
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Member extends Auth {
    
    /**
     * @before _secure, changeLayout
     */
    public function index() {
        $this->seo(array(
            "title" => "Dashboard",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        $links = Link::all(array("user_id = ?" => $this->user->id), array("id", "item_id", "short", "created"), "created", "desc", 5, 1);
        $earnings = $this->totalEarnings();
        $rpm_in = RPM::first(array("country = ?" => "IN"), array("value"));$view->set("rpm_in", $rpm_in);
        $rpm_us = RPM::first(array("country = ?" => "US"), array("value"));$view->set("rpm_us", $rpm_us);
        $rpm_pk = RPM::first(array("country = ?" => "PK"), array("value"));$view->set("rpm_pk", $rpm_pk);
        $rpm_au = RPM::first(array("country = ?" => "AU"), array("value"));$view->set("rpm_au", $rpm_au);
        $rpm_nw = RPM::first(array("country = ?" => "NW"), array("value"));$view->set("rpm_nw", $rpm_nw);

        $view->set("links", $links);
        $view->set("earnings", $earnings["total"]);
        $view->set("pending", $earnings["pending"]);
    }

    /**
     * @before _secure
     */
    public function clicksToday() {
        $this->JSONview();
        $view = $this->getActionView();

        $count = 0;
        $earning = 0;
        $links = Link::all(array("user_id = ?" => $this->user->id), array("short", "item_id"));
        foreach ($links as $link) {
            $stat = Link::findStats($link->short);
            $count += $stat->analytics->day->shortUrlClicks;
            if ($stat->analytics->day->shortUrlClicks != 0) {
                $rpm = RPM::first(array("item_id = ?" => $link->item_id));
                $earning += (float) ($rpm->value) * $stat->analytics->day->shortUrlClicks/1000;
            }
        }

        $view->set("earning", $earning);
        $view->set("click", $count);
    }
    
    protected function totalEarnings() {
        $total_earnings = 0;
        $pending = 0;
        $earnings = Earning::all(array("user_id = ?" => $this->user->id), array("amount", "live"));
        foreach ($earnings as $earning) {
            $total_earnings += $earning->amount;
            if($earning->live == "0") {
                $pending += $earning->amount;
            }
        }
        $earning = array(
            "total" => $total_earnings,
            "pending" => $pending
        );
        return $earning;
    }

    protected function changeDate($date, $day) {
        return date_format(date_add(date_create($date),date_interval_create_from_date_string("{$day} day")), 'Y-m-d');;
    }

    /**
     * @before _secure, changeLayout
     */
    public function mylinks() {
        $this->seo(array(
            "title" => "Stats Charts",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();

        $startdate = RequestMethods::get("startdate", date('Y-m-d', strtotime($startdate . " -7 day")));
        $enddate = RequestMethods::get("enddate", date('Y-m-d', strtotime($startdate . "now")));
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        
        $where = array(
            "user_id = ?" => $this->user->id,
            "created >= ?" => $this->changeDate($startdate, "-1"),
            "created <= ?" => $this->changeDate($enddate, "1")
        );

        $links = Link::all($where, array("id", "item_id", "short", "created"), "created", "desc", $limit, $page);
        $count = Link::count($where);
        
        $view->set("links", $links);
        $view->set("limit", $limit);
        $view->set("page", $page);
        $view->set("count", $count);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function stats($id='') {
        $this->seo(array(
            "title" => "Stats Charts",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        if (RequestMethods::get("action") == "showStats") {
            $startdate = RequestMethods::get("startdate");
            $enddate = RequestMethods::get("enddate");

            $link = Link::first(array("id = ?" => $id, "user_id = ?" => $this->user->id), array("id"));
            
            $diff = date_diff(date_create($startdate), date_create($enddate));
            for ($i = 0; $i < $diff->format("%a"); $i++) {
                $date = date('Y-m-d', strtotime($startdate . " +{$i} day"));$count = 0;
                $stats = Stat::first(array("link_id = ?" => $link->id, "created LIKE ?" => "%{$date}%"), array("shortUrlClicks"));
                foreach ($stats as $stat) {
                    $count += $stat->shortUrlClicks;
                }
                $obj[] = array('y' => $date, 'a' => $count);
            }
            
            $view->set("data", \Framework\ArrayMethods::toObject($obj));
        }
    }
    
    /**
     * Shortens the url for member
     * @before _secure, changeLayout
     */
    public function shortenURL() {
        $this->seo(array("title" => "Shorten URL", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        if (RequestMethods::get("longURL")) {
            $longURL = RequestMethods::get("longURL");
            $googl = Registry::get("googl");
            $object = $googl->shortenURL($longURL);
            $link = Link::first(array("short = ?" => $object->id));
            if (!$link) {
                $link = new Link(array(
                    "user_id" => $this->user->id,
                    "short" => $object->id,
                    "item_id" => RequestMethods::get("item"),
                    "live" => 1
                ));
                $link->save();
            }
            
            $view->set("shortURL", $object->id);
            $view->set("googl", $object);
        }
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function topearners() {
        $this->seo(array(
            "title" => "Top Earners",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        $earners = Earning::all(array("live = ?" => 0), array("user_id", "amount"), "amount", "desc", 10, 1);
        $view->set("earners", $earners);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function links() {
        $this->seo(array(
            "title" => "Links",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        $title = RequestMethods::get("title", "");
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        
        $where = array(
            "title LIKE ?" => "%{$title}%",
            "live = ?" => true,
        );
        $items = Item::all($where, array("id", "title", "image", "target", "url"), "created", "desc", $limit, $page);
        $count = Item::count($where);
        
        $view->set("limit", $limit);
        $view->set("title", $title);
        $view->set("page", $page);
        $view->set("count", $count);
        $view->set("items", $items);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function earnings() {
        $this->seo(array(
            "title" => "Earnings",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        $earnings = Earning::all(array("user_id = ?" => $this->user->id), array("item_id", "amount", "live", "created"), "id", "desc", 10, 1);
        $view->set("earnings", $earnings);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function faqs() {
        $this->seo(array(
            "title" => "FAQs",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function profile() {
        $this->seo(array(
            "title" => "Profile",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        $account = Account::first(array("user_id = ?" => $this->user->id));
        if(!$account) {
            $account = new Account();
        }
        
        if (RequestMethods::post('action') == 'saveUser') {
            $user = User::first(array("id = ?" => $this->user->id));
            $user->phone = RequestMethods::post('phone');
            $user->name = RequestMethods::post('name');
            $user->save();
            $view->set("success", true);
            $view->set("user", $user);
        }
        
        if (RequestMethods::get("action") == "saveAccount") {
            $account->user_id = $this->user->id;
            $account->name = RequestMethods::post("name");
            $account->bank = RequestMethods::post("bank");
            $account->number = RequestMethods::post("number");
            $account->ifsc = RequestMethods::post("ifsc");
            
            $account->save();
            $view->set("success", true);
        }
        
        $view->set("account", $account);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function payments() {
        $this->seo(array(
            "title" => "Payments",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        $view->set("paymens", array());
    }
    
    public function changeLayout() {
        $this->defaultLayout = "layouts/member";
        $this->setLayout();
    }
}
