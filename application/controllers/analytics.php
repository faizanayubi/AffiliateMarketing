<?php

/**
 * Description of analytics
 *
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;
use \Curl\Curl;
use ClusterPoint\DB as DB;

class Analytics extends Admin {
    
    /**
     * @before _secure, changeLayout, _admin
     */
    public function googl() {
        $this->seo(array("title" => "shortURL Analytics", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        if (RequestMethods::get("shortURL")) {
            $shortURL = RequestMethods::get("shortURL");
            $googl = Registry::get("googl");
            $object = $googl->analyticsFull($shortURL);
            $link = Link::first(array("short = ?" => $shortURL), array("item_id", "user_id"));
            if ($link) {
                $view->set("verified", $link->clusterpoint());
            }

            $longUrl = explode("?item=", $object->longUrl);
            if($longUrl) {
                $str = base64_decode($longUrl[1]);
                $datas = explode("&", $str);
                foreach ($datas as $data) {
                    $property = explode("=", $data);
                    $item[$property[0]] = $property[1];
                }
            }

            $view->set("shortURL", $shortURL);
            $view->set("googl", $object);
            $view->set("item", $item);
        }
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function content($id='') {
        $this->seo(array("title" => "Content Analytics", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $item = Item::first(array("id = ?" => $id));

        $earn = 0;
        $stats = Stat::all(array("item_id = ?" => $item->id), array("amount"));
        foreach ($stats as $stat) {
            $earn += $stat->amount;
        }

        $links = Link::count(array("item_id = ?" => $item->id));
        $rpm = RPM::count(array("item_id = ?" => $item->id));

        $view->set("item", $item);
        $view->set("earn", $earn);
        $view->set("links", $links);
        $view->set("rpm", $rpm);
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function urlDebugger() {
        $this->seo(array("title" => "URL Debugger", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $url = RequestMethods::get("urld", "http://likesbazar.in/");
        $metas = get_meta_tags($url);

        $facebook = new Curl();
        $facebook->get('https://api.facebook.com/method/links.getStats', array(
            'format' => 'json',
            'urls' => $url
        ));
        $facebook->setOpt(CURLOPT_ENCODING , 'gzip');
        $facebook->close();

        $twitter = new Curl();
        $twitter->get('https://cdn.api.twitter.com/1/urls/count.json', array(
            'url' => $url
        ));
        $twitter->setOpt(CURLOPT_ENCODING , 'gzip');
        $twitter->close();

        $view->set("url", $url);
        $view->set("metas", $metas);
        $view->set("facebook", array_values($facebook->response)[0]);
        $view->set("twitter", $twitter->response);
    }

    /**
     * @before _secure
     */
    public function link($duration = "allTime") {
        $this->JSONview();
        $view = $this->getActionView();

        $shortURL = RequestMethods::get("shortURL");
        $link = Link::first(array("short = ?" => $shortURL), array("item_id", "short", "user_id"));
        $result = $link->stat($duration);
        
        $view->set("earning", $result["earning"]);
        $view->set("click", $result["click"]);
        $view->set("rpm", $result["rpm"]);
        $view->set("verified", $result["verified"]);
    }

    /**
     * @before _secure
     */
    public function realtime($duration = "allTime") {
        $this->JSONview();
        $view = $this->getActionView();

        $earnings = 0;
        $clicks = 0;
        $verified = 0;
        $links = Link::all(array("user_id = ?" => $this->user->id, "created >= ?" => date('Y-m-d', strtotime("-3 day"))), array("short", "item_id", "user_id"));
        foreach ($links as $link) {
            $result = $link->stat($duration);
            if ($result) {
                $clicks += $result["click"];
                $earnings += $result["earning"];
                $verified += $result["verified"];
            }
            $result = 0;
        }

        $view->set("avgrpm", round(($earnings*1000)/($clicks), 2));
        $view->set("earnings", $earnings);
        $view->set("clicks", $clicks);
        $view->set("verified", $verified);
    }

    /**
     * @before _secure, changeLayout
     */
    public function logs($action = "", $name = "") {
        $this->seo(array("title" => "Activity Logs", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        if ($action == "unlink") {
            $file = APP_PATH ."/logs/". $name . ".txt";
            @unlink($file);
            self::redirect("/analytics/logs");
        }

        $logs = array();
        $path = APP_PATH . "/logs";
        $iterator = new DirectoryIterator($path);

        foreach ($iterator as $item) {
            if (!$item->isDot()) {
                array_push($logs, $item->getFilename());
            }
        }

        $view->set("logs", $logs);
    }

    protected function today() {
        $today = strftime("%Y-%m-%d", strtotime('now'));
        $m = new MongoClient();
        $db = $m->stats;
        $collection = $db->hits;
        $stats = array();$stat = array();

        $records = $collection->find(array('user_id' => $this->user->id, 'created' => $today));
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
    
}
