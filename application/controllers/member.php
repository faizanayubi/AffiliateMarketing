<?php
/**
 * Description of auth
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;
use ClusterPoint\DB as DB;

class Member extends Analytics {
    
    /**
     * @before _secure, memberLayout
     */
    public function index() {
        $this->seo(array(
            "title" => "Dashboard",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        $news = Meta::first(array("property = ?" => "news", "live = ?" => 1));
        $yesterday = strftime("%Y-%m-%d", strtotime('-1 day'));
        $yrpm = array();
        $trpm = array();
        
        $database = Registry::get("database");
        $paid = $database->query()->from("payments", array("SUM(amount)" => "earn"))->where("user_id=?", $this->user->id)->all();

        $links = Link::all(array("user_id = ?" => $this->user->id), array("id", "item_id", "short"), "created", "desc", 5, 1);
        $totalEarning = 0; $totalClicks = 0; $yesterdayEarning = 0; $yesterdayClicks = 0;
        $stats = Stat::all(array("user_id = ?" => $this->user->id), array("DISTINCT link_id"), "created", "desc");
        foreach ($stats as $stat) {
            $stat = Stat::first(array("link_id = ?" => $stat->link_id), array("amount", "shortUrlClicks", "created", "rpm"), "created", "desc");
            $created = Framework\StringMethods::only_date($stat->created);
            if ($created == $yesterday) {
                $yesterdayEarning += $stat->amount;
                $yesterdayClicks += $stat->shortUrlClicks;
                array_push($yrpm, $stat->rpm);
            }
            $totalEarning += $stat->amount;
            $totalClicks += $stat->shortUrlClicks;
            array_push($trpm, $stat->rpm);
        }
        
        $view->set("totalEarning", round($totalEarning, 2));
        $view->set("totalClicks", round($totalClicks, 2));
        $view->set("totalRPM", round((array_sum($trpm) / count($trpm)), 2));
        $view->set("yesterdayEarning", round($yesterdayEarning, 2));
        $view->set("yesterdayClicks", round($yesterdayClicks, 2));
        $view->set("yesterdayRPM", round((array_sum($yrpm) / count($yrpm)), 2));
        $view->set("paid", round($paid[0]["earn"], 2));
        $view->set("links", $links);
        $view->set("news", $news);
        $view->set("today", $this->today());
        $view->set("domain", substr($this->target()[array_rand($this->target())], 7));
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
        $this->JSONview();
        $view = $this->getActionView();
        
        if($this->user->domain) {
            $longURL = $this->user->domain . '?item=' . RequestMethods::get("hash");
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
        }
        $view->set("shortURL", $object->id);
        $view->set("googl", $object);
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
        $today = strftime("%Y-%m-%d", strtotime('now'));
        $database = Registry::get("database");
        $result = $database->execute("SELECT user_id, SUM(shortUrlClicks) as click FROM stats GROUP BY user_id ORDER BY click DESC LIMIT 10");
        $earners = array();
        for ($i = 0; $i < $result->num_rows; $i++) {
            $data = $result->fetch_array(MYSQLI_ASSOC);
            $earners[] = $data;
        }

        $m = new MongoClient();
        $db = $m->stats;
        $collection = $db->hits;
        $stats = array();$stat = array();

        $records = $collection->find(array('created' => $today));
        $records->sort(array("click" => -1));
        $records->limit(10);
        if (isset($records)) {
            foreach ($records as $record) {
                if (isset($stats[$record['user_id']])) {
                    $stats[$record['user_id']] += $record['click'];
                } else {
                    $stats[$record['user_id']] = $record['click'];
                }
            }

            foreach ($stats as $key => $value) {
                array_push($stat, array(
                    "user_id" => $key,
                    "count" => $value
                ));
            }
            
            $view->set("today", $stat);
        }

        $view->set("earners", $earners);
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
        
        $view = $this->getActionView();
        $stats = Stat::all(array("user_id = ?" => $this->user->id), array("DISTINCT link_id"), "created", "desc", $limit, $page);
        $count = count($stats);

        $view->set("stats", $stats);
        $view->set("limit", $limit);
        $view->set("page", $page);
        $view->set("count", $count);
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
        
        if (RequestMethods::post('action') == 'saveUser') {
            $user = User::first(array("id = ?" => $this->user->id));
            $view->set("message", "Saved <strong>Successfully!</strong>");

            $user->phone = RequestMethods::post('phone', $user->phone);
            $user->name = RequestMethods::post('name', $user->name);
            $user->username = RequestMethods::post('username', $user->username);
            if(empty($user->domain)) {
                $domain = "http://".RequestMethods::post('domain').RequestMethods::post("target");
                $exist = User::first(array("domain = ?" => $domain), array("id"));
                if($exist) {
                    $view->set("message", "Domain Name Exists, try another");
                } else{
                    $user->domain = $domain;
                }
            }

            $user->save();
            $view->set("user", $user);
        }
        
        if (RequestMethods::post("action") == "saveAccount") {
            $account = new Account(array(
                "user_id" => $this->user->id,
                "name" => RequestMethods::post("name"),
                "bank" => RequestMethods::post("bank"),
                "number" => RequestMethods::post("number"),
                "ifsc" => RequestMethods::post("ifsc"),
                "paypal" => RequestMethods::post("paypal", "")
            ));
            
            $account->save();
            $view->set("message", "Saved <strong>Successfully!</strong>");
        }
        
        $view->set("account", $account);
        $view->set("domains", $this->target());
    }
    
    /**
     * @before _secure, memberLayout
     */
    public function payments() {
        $this->seo(array(
            "title" => "Payments",
            "view" => $this->getLayoutView()
        ));

        $payments = Payment::all(array("user_id = ?" => $this->user->id));

        $view = $this->getActionView();
        $view->set("payments", $payments);
    }

    /**
     * @before _secure, memberLayout
     */
    public function platforms() {
        $this->seo(array(
            "title" => "Platforms",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();

        if (RequestMethods::post("action") == "addPlatform") {
            $platform = new Platform(array(
                "user_id" => $this->user->id,
                "name" => "FACEBOOK_PAGE",
                "link" =>  RequestMethods::post("link"),
                "image" => $this->_upload("fbadmin", "images")
            ));
            $platform->save();
            $view->set("message", "Your Platform has been added successfully");
        }

        $platforms = Platform::all(array("user_id = ?" => $this->user->id));
        $view->set("platforms", $platforms);
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function news() {
        $this->seo(array("title" => "Member News", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        if (RequestMethods::post("news")) {
            $news = new Meta(array(
                "user_id" => $this->user->id,
                "property" => "news",
                "value" => RequestMethods::post("news")
            ));
            $news->save();
            $view->set("message", "News Saved Successfully");
        }
        
        $allnews = Meta::all(array("property = ?" => "news"));
            
        $view->set("allnews", $allnews);
    }

    /**
     * @before _secure, _admin
     */
    public function delete($user_id) {
        $this->noview();
        $stats = Stat::first(array("user_id = ?" => $user_id));
        foreach ($stats as $stat) {
            $stat->delete();
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

        $account = Account::first(array("user_id = ?" => $user_id));
        if ($account) {
            $account->delete();
        }

        $user = User::first(array("id = ?" => $user_id));
        if ($user) {
            $user->delete();
        }
        
        self::redirect($_SERVER["HTTP_REFERER"]);
    }

    protected function target() {
        $session = Registry::get("session");
        $domains = $session->get("domains");

        $alias = array();
        foreach ($domains as $domain) {
            array_push($alias, $domain->value);
        }
        
        return $alias;
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function all() {
        $this->seo(array("title" => "New User Platforms", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        
        $startdate = RequestMethods::get("startdate", date('Y-m-d', strtotime("-7 day")));
        $enddate = RequestMethods::get("enddate", date('Y-m-d', strtotime("now")));
        $id = RequestMethods::get("id", "");

        if (empty($id)) {
            $where = array(
                "created >= ?" => $this->changeDate($startdate, "-1"),
                "created <= ?" => $this->changeDate($enddate, "1")
            );
        } else {
            $where = array(
                "id = ?" => $id
            );
        }
        $users = User::all($where, array("id","name", "created", "live"), "live", "asc", $limit, $page);
        $count = User::count($where);

        $view->set("users", $users);
        $view->set("id", $id);
        $view->set("startdate", $startdate);
        $view->set("enddate", $enddate);
        $view->set("page", $page);
        $view->set("count", $count);
        $view->set("limit", $limit);
    }

    public function memberLayout() {
        $this->defaultLayout = "layouts/member";
        $this->setLayout();
    }
}
