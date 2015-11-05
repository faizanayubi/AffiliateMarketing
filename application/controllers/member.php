<?php
/**
 * Description of auth
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Member extends Admin {
    
    /**
     * @before _secure, memberLayout
     */
    public function index() {
        $this->seo(array(
            "title" => "Dashboard",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        $links = Link::all(array("user_id = ?" => $this->user->id), array("id", "item_id", "short"), "created", "desc", 5, 1);
        $stat = $this->quickStats();
        
        $view->set("links", $links);
        $view->set("averagerpm", ($stat["earning_total"]*1000)/($stat["clicks"]));
        $view->set("clicks", $stat["clicks"]);
        $view->set("earnings", $stat["earning_total"]);
        $view->set("pending", $stat["earning_pending"]);
    }

    protected function quickStats() {
        $total_earnings = 0;$total_clicks = 0;$pending = 0;

        $earnings = Earning::all(array("user_id = ?" => $this->user->id), array("amount", "live", "stat_id"));
        foreach ($earnings as $earning) {
            $stat = Stat::first(array("id = ?" => $earning->stat_id), array("shortUrlClicks"));
            $total_clicks += $stat->shortUrlClicks;

            $total_earnings += $earning->amount;
            if($earning->live == "0") {
                $pending += $earning->amount;
            }
        }
        $earning = array(
            "earning_total" => $total_earnings,
            "earning_pending" => $pending,
            "clicks" => $total_clicks
        );
        return $earning;
    }

    protected function totalClicks() {
        $total_clicks = 0;
        $links = Link::all(array("user_id = ?" => $this->user->id), array("id"));
        foreach ($links as $link) {
            $stat = Stat::first(array("link_id = ?" => $link->id), array("shortUrlClicks"));
            $total_clicks += $stat->shortUrlClicks;
        }
        return $total_clicks;
    }

    /**
     * @before _secure, memberLayout
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
        $view->set("total", Link::count(array("user_id = ?" => $this->user->id)));
    }
    
    /**
     * @before _secure, memberLayout
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
     * @before _secure, memberLayout
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
     * @before _secure, memberLayout
     */
    public function topearners() {
        $this->seo(array(
            "title" => "Top Earners",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();

        $where = array(
            "live = ?" => 0,
            "created >= ?" => date('Y-m-d', strtotime($startdate . "-1 day"))
        );
        $stats = Stat::all($where, array("verifiedClicks", "link_id"), "verifiedClicks", "desc", 10, 1);
        $view->set("stats", $stats);
    }
    
    /**
     * @before _secure, memberLayout
     */
    public function links() {
        $this->seo(array(
            "title" => "Links",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        $title = RequestMethods::get("title", "");
        $domain = RequestMethods::get("domain");
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 9);

        $where = array(
            "title LIKE ?" => "%{$title}%",
            "url LIKE ?" => "%{$domain}%",
            "live = ?" => true,
        );
        
        $items = Item::all($where, array("id", "title", "image", "target", "url", "description"), "created", "desc", $limit, $page);
        $count = Item::count($where);

        $session = Registry::get("session");

        $view->set("limit", $limit);
        $view->set("title", $title);
        $view->set("page", $page);
        $view->set("count", $count);
        $view->set("items", $items);
        $view->set("domain", $domain);
        $view->set("domains", array("filmycity.in", "filmymagic.com", "viraltabloid.in", "kapilsharmafc.com"));
    }
    
    /**
     * @before _secure, memberLayout
     */
    public function earnings() {
        $this->seo(array("title" => "Earnings", "view" => $this->getLayoutView()));

        $startdate = RequestMethods::get("startdate", date('Y-m-d', strtotime("-7 day")));
        $enddate = RequestMethods::get("enddate", date('Y-m-d', strtotime("now")));
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        
        $where = array(
            "user_id = ?" => $this->user->id,
            "created >= ?" => $this->changeDate($startdate, "-1"),
            "created <= ?" => $this->changeDate($enddate, "1")
        );

        $view = $this->getActionView();
        $earnings = Earning::all($where, array("link_id", "stat_id", "rpm", "amount", "live", "created", "id"), "created", "desc", $limit, $page);
        $count = Earning::count($where);

        $view->set("earnings", $earnings);
        $view->set("limit", $limit);
        $view->set("page", $page);
        $view->set("count", $count);
        $view->set("total", Earning::count(array("user_id = ?" => $this->user->id)));
    }
    
    /**
     * @before _secure, memberLayout
     */
    public function faqs() {
        $this->seo(array(
            "title" => "FAQs",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
    
    /**
     * @before _secure, memberLayout
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
            $user->username = RequestMethods::post('username');
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
     * @before _secure, memberLayout
     */
    public function payments() {
        $this->seo(array(
            "title" => "Payments",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        $view->set("paymens", array());
    }

    /**
     * @before _secure, _admin
     */
    public function delete($user_id) {
        $this->noview();
        $earnings = Earning::first(array("user_id = ?" => $user_id));
        foreach ($earnings as $earning) {
            $earning->delete();
        }

        $links = Link::all(array("user_id = ?" => $user_id));
        foreach ($links as $link) {
            $stat = Stat::first(array("link_id = ?" => $link->id));
            if ($stat) {
                $stat->delete();
            }
            $link->delete();
        }
        
        $platforms = Platform::all(array("user_id = ?" => $user_id));
        foreach ($platforms as $platform) {
            $platform->delete();
        }

        $social = Social::first(array("user_id = ?" => $user_id));
        if ($social) {
            $social->delete();
        }

        $account = Account::first(array("user_id = ?" => $user_id));
        if ($account) {
            $account->delete();
        }

        self::redirect($_SERVER["HTTP_REFERER"]);
    }
    
    public function memberLayout() {
        $this->defaultLayout = "layouts/member";
        $this->setLayout();
    }
}
