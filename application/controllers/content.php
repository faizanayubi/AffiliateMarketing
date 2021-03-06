<?php

/**
 * Description of content
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Content extends Publisher {

    protected $rpm = array("IN" => 135, "US" => 270, "CA" => 380, "AU" => 400, "GB" => 310, "NP" => 70, "PK" => 70, "AF" => 70, "BD" => 70, "BR" => 70, "MX" => 70, "NONE" => 105);

    /**
     * @before _secure, publisherLayout
     */
    public function index() {
        $this->seo(array("title" => "Favourite Categories", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        $query = RequestMethods::get("query", null);
        $title = RequestMethods::get("title", null);
        $cats = RequestMethods::get("category");
        strpos($cats, ",") ? $category = implode(",", $cats) : NULL;
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 12);

        isset($query) ? $where["url LIKE ?"] = "%{$query}%" : NULL;
        isset($title) ? $where["title LIKE ?"] = "%{$title}%" : NULL;
        isset($category) ? $where["category LIKE ?"] = "%{$category}%" : NULL;

        $where["live = ?"] = true;
        
        $items = Item::all($where, array("id", "title", "image", "url", "description"), "id", "desc", $limit, $page);
        $count = Item::count($where);

        $view->set("limit", $limit);
        $view->set("query", $query);
        $view->set("title", $title);
        $view->set("page", $page);
        $view->set("count", $count);
        $view->set("items", $items);
        $view->set("category", $category);
        $view->set("domains", $this->target());
    }
    
    /**
     * @before _secure, changeLayout, _admin
     */
    public function create() {
        $this->seo(array("title" => "Create Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $rpms = array();
        foreach ($this->rpm as $key => $value) {
            array_push($rpms, array(
                "country" => $key,
                "value" => $value
            ));
        }
        
        if (RequestMethods::post("action") == "content") {
            $item = new Item(array(
                "url" =>  RequestMethods::post("url"),
                "title" => RequestMethods::post("title"),
                "image" => $this->_upload("image", "images"),
                "commission" => 0,
                "category" => implode(",", RequestMethods::post("category", "")),
                "description" => RequestMethods::post("description"),
                "user_id" => $this->user->id
            ));
            $item->save();
            
            $rpm = new RPM(array(
                "item_id" => $item->id,
                "value" => json_encode(RequestMethods::post("rpm")),
            ));
            $rpm->save();

            $view->set("success", "Content Created Successfully with id : ". $item->id);
        }
        $view->set("rpms", $rpms);
    }
    
    /**
     * @before _secure, changeLayout, _admin
     */
    public function all() {
        $this->seo(array("title" => "Manage Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView(); $where = array();
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        
        $property = RequestMethods::get("property", null);
        $value = RequestMethods::get("value", null);
        $likes = array("title", "url");
        if ($property) {
            $where = array("{$property} = ?" => $value);
            if (in_array($property, $likes)) {
                $where = array("{$property} LIKE ?" => "%{$value}%");
            }
        }
        
        $contents = Item::all($where, array("id", "title", "created", "image", "url", "live"), "id", "desc", $limit, $page);
        $count = Item::count($where);

        $view->set("contents", $contents);
        $view->set("page", $page);
        $view->set("count", $count);
        $view->set("limit", $limit);
        $view->set("property", $property);
        $view->set("value", $value);
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function stats() {
        $this->seo(array("title" => "Manage Stats", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        $offset = ($page - 1) * $limit;
        
        $property = RequestMethods::get("property", "c");
        $sort = RequestMethods::get("sort", "DESC");
        $stats = array();
        $database = Registry::get("database");
        $result = $database->execute("SELECT item_id, SUM(click) c, SUM(amount) a FROM `stats` GROUP BY item_id ORDER BY {$property} {$sort} LIMIT {$offset}, {$limit}");
        for ($i=0; $i<$result->num_rows; $i++) {
            $stats[]=(object) $result->fetch_array(MYSQLI_ASSOC);
        }
        $count = Item::count(array());

        $view->set("stats", $stats);
        $view->set("page", $page);
        $view->set("count", $count);
        $view->set("limit", $limit);
        $view->set("property", $property);
        $view->set("sort", $sort);
    }
    
    /**
     * @before _secure, changeLayout, _admin
     */
    public function edit($id = NULL) {
        $this->seo(array("title" => "Edit Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $item = Item::first(array("id = ?" => $id));
        $rpm = RPM::first(array("item_id = ?" => $item->id));

        $rpms = array();
        foreach (json_decode($rpm->value, true) as $key => $value) {
            array_push($rpms, array(
                "country" => $key,
                "value" => $value
            ));
        }
        
        if (RequestMethods::post("action") == "update") {
            $item->title = RequestMethods::post("title");
            $item->url = RequestMethods::post("url");
            $item->commission = RequestMethods::post("commission");
            $item->category = implode(",", RequestMethods::post("category"));
            $item->description = RequestMethods::post("description");
            $item->live = RequestMethods::post("live", "0");
            $item->save();

            $rpm->value = json_encode(RequestMethods::post("rpm"));
            $rpm->save();

            $view->set("success", true);
            $view->set("errors", $item->getErrors());
        }
        $view->set("item", $item);
        $view->set("rpms", $rpms);
        $view->set("categories", explode(",", $item->category));
    }
    
    /**
     * @before _secure, changeLayout, _admin
     */
    public function shortenURL() {
        $this->seo(array("title" => "Shorten URL", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        if (RequestMethods::get("longURL")) {
            $longURL = RequestMethods::get("longURL");
            $googl = Registry::get("googl");
            $object = $googl->shortenURL($longURL);
            
            $facebook = new \Curl\Curl();
            $facebook->post('https://graph.facebook.com/?id='. $longURL .'&scrape=true');
            $facebook->close();
            
            $view->set("shortURL", $object->id);
            $view->set("googl", $object);
        }
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function domains() {
        $this->seo(array("title" => "All Domains", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $domains = Meta::all(array("property = ?" => "domain"));

        if (RequestMethods::get("domain")) {
            $exist = Meta::first(array("property" => "domain", "value = ?" => RequestMethods::get("domain")));
            if($exist) {
                $view->set("message", "Domain Exists");
            } else {
                $domain = new Meta(array(
                    "user_id" => $this->user->id,
                    "property" => "domain",
                    "value" => RequestMethods::get("domain")
                ));
                $domain->save();
                array_push($domains, $domain);
                $view->set("message", "Domain Added Successfully");
            }
        }

        $view->set("domains", $domains);
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function delete($id = NULL) {
        $this->noview();
        $item = Item::first(array("id = ?" => $id));

        $stats = Stat::all(array("item_id = ?" => $item->id));
        foreach ($earnings as $earning) {
            $earning->delete();
        }

        $links = Link::all(array("item_id = ?" => $item->id));
        foreach ($links as $link) {
            $stat = Stat::all(array("link_id = ?" => $link->id));
            $stat->delete();
            $link->delete();
        }

        $rpm = RPM::first(array("item_id = ?" => $item->id));
        $rpm->delete();

        $item->delete();
        self::redirect($_SERVER["HTTP_REFERER"]);        
    }

    /**
     * @before _secure, publisherLayout
     */
    public function top() {
        $this->seo(array("title" => "Top Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        $query = RequestMethods::get("query", "");
        $title = RequestMethods::get("title", "");
        $category = implode(",", RequestMethods::get("category", ""));
        
        $where = array(
            "url LIKE ?" => "%{$query}%",
            "title LIKE ?" => "%{$title}%",
            "category LIKE ?" => "%{$category}%",
            "live = ?" => true
        );
        
        $items = Item::all($where, array("id"));
        shuffle($items);
        
        $view->set("title", $title);
        $view->set("page", $page);
        $view->set("items", array_slice($items, 0, 10));
        $view->set("category", $category);
        $view->set("domains", $this->target());
    }

    /**
     * @before _secure, publisherLayout
     */
    public function viral() {
        $this->seo(array("title" => "Viral for you", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 12);
        
        $stats = Stat::all(array(), array("DISTINCT item_id"), "amount", "desc", $limit, $page);
        $count = Stat::count(array());
        
        $view->set("count", $count);
        $view->set("stats", $stats);
        $view->set("limit", $limit);
        $view->set("page", $page);
        $view->set("domains", $this->target());
    }

    public function resize($image, $width = 470, $height = 246) {
        $path = APP_PATH . "/public/assets/uploads/images";
        $cdn = CDN;$image = base64_decode($image);
        if ($image) {
            $filename = pathinfo($image, PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);

            if ($filename && $extension) {
                $thumbnail = "{$filename}-{$width}x{$height}.{$extension}";
                if (!file_exists("{$path}/{$thumbnail}")) {
                    $imagine = new \Imagine\Gd\Imagine();
                    $size = new \Imagine\Image\Box($width, $height);
                    $mode = Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
                    $imagine->open("{$path}/{$image}")->resize($size)->save("{$path}/resize/{$thumbnail}");
                    //$imagine->open("{$path}/{$image}")->thumbnail($size, $mode)->save("{$path}/resize/{$thumbnail}");
                }
                header("Location: {$cdn}uploads/images/resize/{$thumbnail}");
                exit();
            }
            header("Location: /images/{$image}");
            exit();
        } else {
            header("Location: {$cdn}img/logo.png");
            exit();
        }
    }

}