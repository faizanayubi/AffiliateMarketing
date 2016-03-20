<?php

/**
 * Description of analytics
 *
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;
use \Curl\Curl;

class Analytics extends Admin {

    /**
     * @before _secure, changeLayout, _admin
     */
    public function googl() {
        $this->seo(array("title" => "shortURL Analytics", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        if (RequestMethods::get("shortURL")) {
            $shortURL = RequestMethods::get("shortURL");
            $link = Link::first(array("short = ?" => $shortURL), array("short", "item_id"));
            $result = $link->googl();
            
            $view->set("earning", $result["earning"]);
            $view->set("click", $result["click"]);
            $view->set("rpm", $result["rpm"]);
            $view->set("analytics", $result["analytics"]);
        }
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function content($id='') {
        $this->seo(array("title" => "Content Analytics", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $item = Item::first(array("id = ?" => $id));
        $total = $database->query()->from("stats", array("SUM(amount)" => "earn", "SUM(click)" => "click"))->where("item_id=?", $item->id)->all();

        $view->set("item", $item);
        $view->set("total", $total);
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function urlDebugger() {
        $this->seo(array("title" => "URL Debugger", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $url = RequestMethods::get("urld", "http://earnbugs.in/");
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
    public function link($date = NULL) {
        $this->JSONview();
        $view = $this->getActionView();

        $link_id = RequestMethods::get("link");
        $link = Link::first(array("id = ?" => $link_id), array("item_id", "id"));
        $result = $link->stat($date);
        
        $view->set("earning", $result["earning"]);
        $view->set("click", $result["click"]);
        $view->set("rpm", $result["rpm"]);
        $view->set("analytics", $result["analytics"]);
        $view->set("link", $link);
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
                if (substr($item->getFilename(), 0, 1) != ".") {
                    array_push($logs, $item->getFilename());
                }
            }
        }
        arsort($logs);
        $view->set("logs", $logs);
    }

    /**
     * @before _secure, changeLayout
     */
    public function clicks() {
        $this->seo(array("title" => "Clicks Stats", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $now = strftime("%Y-%m-%d", strtotime('now'));
        $view->set("date", $now);
    }

    /**
     * Today Stats of user
     * @return array earnings, clicks, rpm, analytics
     * @before _secure
     */
    public function stats($created = NULL, $auth = 1, $user_id = NULL, $item_id = NULL) {
        $this->seo(array("title" => "Stats", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $total_click = 0;$earning = 0;$analytics = array();$query = array();
        $rpm = array("IN" => 135, "US" => 270, "CA" => 380, "AU" => 400, "GB" => 310, "NP" => 70, "PK" => 70, "AF" => 70, "BD" => 70, "BR" => 70, "MX" => 70, "NONE" => 105);
        $return = array("click" => 0, "rpm" => 0, "earning" => 0, "analytics" => array());

        is_null($created) ? NULL : $query['created'] = $created;
        is_null($item_id) ? NULL : $query['item_id'] = $item_id;
        if ($auth) {
            $query['user_id'] = (is_null($user_id) ? $this->user->id : $user_id);
        }

        $connection = new Mongo();
        $db = $connection->stats;
        $collection = $db->clicks;

        $cursor = $collection->find($query);
        foreach ($cursor as $id => $result) {
            $code = $result["country"];
            $total_click += $result["click"];
            if (array_key_exists($code, $rpm)) {
                $earning += ($rpm[$code])*($result["click"])/1000;
            } else {
                $earning += ($rpm["NONE"])*($result["click"])/1000;
            }
            $analytics[$code] += $result["click"];
            
        }

        if ($total_click > 0) {
            $return = array(
                "click" => round($total_click),
                "rpm" => round($earning*1000/$total_click, 2),
                "earning" => round($earning, 2),
                "analytics" => $analytics
            );
        }

        $view->set("stats", $return);
    }

    protected function array_sort($array, $on, $order=SORT_ASC) {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                break;
                case SORT_DESC:
                    arsort($sortable_array);
                break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }    
}
