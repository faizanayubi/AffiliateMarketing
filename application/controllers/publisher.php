<?php
/**
 * Description of publisher
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Publisher extends Analytics {

    /**
     * @before _secure, publisherLayout
     */
    public function index() {
        $this->seo(array("title" => "Dashboard","view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $news = Meta::first(array("property = ?" => "news", "live = ?" => 1));
        $yesterday = strftime("%Y-%m-%d", strtotime('-1 day'));
        
        $database = Registry::get("database");
        $paid = $database->query()->from("payments", array("SUM(amount)" => "earn"))->where("user_id=?", $this->user->id)->all();
        $links = Link::all(array("user_id = ?" => $this->user->id), array("id", "item_id", "short"), "created", "desc", 5, 1);
        $total = $database->query()->from("stats", array("SUM(amount)" => "earn", "SUM(click)" => "click"))->where("user_id=?", $this->user->id)->all();
    
        $view->set("total", $total);
        $view->set("paid", round($paid[0]["earn"], 2));
        $view->set("links", $links);
        $view->set("news", $news);
        $view->set("domain", substr($this->target()[array_rand($this->target())], 7));
    }

    /**
     * @before _secure, publisherLayout
     */
    public function mylinks() {
        $this->seo(array("title" => "Stats Charts", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);

        $links = Link::all(array("user_id = ?" => $this->user->id), array("id", "item_id", "short", "created"), "created", "desc", $limit, $page);
        $count = Link::count(array("user_id = ?" => $this->user->id));

        $view->set("links", $links);
        $view->set("limit", $limit);
        $view->set("page", $page);
        $view->set("count", $count);
    }
    
    /**
     * Shortens the url for publishers
     * @before _secure, publisherLayout
     */
    public function shortenURL() {
        $this->JSONview();
        $view = $this->getActionView();
        $link = new Link(array(
            "user_id" => $this->user->id,
            "short" => "",
            "item_id" => RequestMethods::get("item"),
            "live" => 1
        ));
        $link->save();
        
        $item = Item::first(array("id = ?" => RequestMethods::get("item")), array("url", "title", "image", "description"));
        $m = Registry::get("MongoDB")->urls;
        $doc = array(
            "link_id" => $link->id,
            "item_id" => RequestMethods::get("item"),
            "user_id" => $this->user->id,
            "url" => $item->url,
            "title" => $item->title,
            "image" => $item->image,
            "description" => $item->description,
            "created" => date('Y-m-d', strtotime("now"))
        );
        $m->insert($doc);

        $longURL = RequestMethods::get("domain") . '/' . base64_encode($link->id);
        $googl = Registry::get("googl");
        $object = $googl->shortenURL($longURL);

        $link->short = $object->id;
        $link->save();

        $view->set("shortURL", $object->id);
    }
    
    /**
     * @before _secure, publisherLayout
     */
    public function topearners() {
        $this->seo(array("title" => "Top Earners", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $today = strftime("%Y-%m-%d", strtotime('now'));
        
        $m = new Mongo();
        $db = $m->stats;
        $collection = $db->clicks;
        $stats = array();$stat = array();

        $cursor = $collection->find(array('created' => $today));
        if ($cursor) {
            foreach ($cursor as $key => $record) {
                if ($stats[$record['user_id']]) {
                    $stats[$record['user_id']] += $record['click'];
                } else {
                    $stats[$record['user_id']] = $record['click'];
                }
            }

            $stats = $this->array_sort($stats, 'click', SORT_DESC);
            $count = 0;
            foreach ($stats as $key => $value) {
                array_push($stat, array(
                    "user_id" => $key,
                    "count" => $value
                ));
                if ($count > 10) {
                    break;
                }
                $count++;
            }
            $view->set("today", $stat);
        }

        $view->set("earners", $earners);
    }
    
    /**
     * @before _secure, publisherLayout
     */
    public function earnings() {
        $this->seo(array("title" => "Earnings", "view" => $this->getLayoutView()));

        $startdate = RequestMethods::get("startdate", date('Y-m-d', strtotime("-6 Day")));
        $enddate = RequestMethods::get("enddate", date('Y-m-d', strtotime("now")));
        
        $view = $this->getActionView();
        $stats = Stat::all(array("user_id = ?" => $this->user->id, "created >= ?" => $startdate, "created <= ?" => $enddate), array("link_id"), "created", "desc");

        $view->set("stats", $stats);
        $view->set("count", count($stats));
    }
    
    /**
     * @before _secure, publisherLayout
     */
    public function profile() {
        $this->seo(array("title" => "Profile", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $account = Account::first(array("user_id = ?" => $this->user->id));
        
        if (RequestMethods::post('action') == 'saveUser') {
            $user = User::first(array("id = ?" => $this->user->id));
            $view->set("message", "Saved <strong>Successfully!</strong>");

            $user->phone = RequestMethods::post('phone', $user->phone);
            $user->name = RequestMethods::post('name', $user->name);
            $user->username = RequestMethods::post('username', $user->username);
            if(!$user->domain) {
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
     * @before _secure, publisherLayout
     */
    public function payments() {
        $this->seo(array("title" => "Payments", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $payments = Payment::all(array("user_id = ?" => $this->user->id));
        $view->set("payments", $payments);
    }

    /**
     * @before _secure, publisherLayout
     */
    public function platforms() {
        $this->seo(array("title" => "Platforms", "view" => $this->getLayoutView()));
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

    public function publisherLayout() {
        $this->defaultLayout = "layouts/publisher";
        $this->setLayout();
    }
    
    /**
     * @before _secure, changeLayout, _admin
     */
    public function settings() {
        $this->seo(array("title" => "Settings", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $login = Meta::first(array("property = ?" => "login"), array("id", "value"));
        $commision = Meta::first(array("property = ?" => "commision"));

        if (RequestMethods::post("commision")) {
            $commision->value = RequestMethods::post("commision");
            $commision->save();
        }

        $view->set("login", $login);
        $view->set("commision", $commision);
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function fraud() {
        $this->seo(array("title" => "Fraud Links", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
    }
    /**
     * @before _secure, publisherLayout
     */
    public function fnq() {
        $this->seo(array("title" => "Frequently Asked Questions", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $today = strftime("%Y-%m-%d", strtotime('now'));
    }
}