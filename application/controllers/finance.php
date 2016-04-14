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
     * 1 - paid, 0 - unpaid
     * 
     * @before _secure, changeLayout, _admin
     */
    public function pending() {
        $this->seo(array("title" => "Records Finance", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $database = Registry::get("database");
        $where = array();
        $live = RequestMethods::get("live", 0);
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        if (RequestMethods::get("user_id")) {
            $where = array("user_id = ?" => RequestMethods::get("user_id"));
        }
        
        $accounts = array();
        $stats = Stat::all($where, array("DISTINCT user_id"), "created", "desc", $limit, $page);
        foreach ($stats as $stat) {
            $earnings = $database->query()->from("stats", array("SUM(amount)" => "earn"))->where("user_id=?", $stat->user_id)->all();
            $payments = $database->query()->from("payments", array("SUM(amount)" => "payment"))->where("user_id=?", $stat->user_id)->all();
            $pending = $earnings[0]['earn'] - $payments[0]['payment'];
            if ($pending > 0) {
                array_push($accounts, array("user_id" => $stat->user_id, "pending" => $pending, "paid" => round($payments[0]["payment"], 2)));
            }
        }
        
        $view->set("accounts", $accounts);
        $view->set("count", count($stats));
        $view->set("page", $page);
        $view->set("limit", $limit);
        $view->set("live", $live);
    }

    /**
     * Finds the earning from a website
     * @before _secure, changeLayout, _admin
     */
    public function earnings($user_id) {
        $this->seo(array("title" => "Earnings Details", "view" => $this->getLayoutView()));
        $view = $this->getActionView(); $amount = 0;
        $datas = array();$records = array();

        $stats = Stat::all(array("user_id = ?" => $user_id), array("DISTINCT item_id"));
        foreach ($stats as $stat) {
            $item = Item::first(array("id = ?" => $stat->item_id), array("url"));
            $url = parse_url(trim($item->url));
            $database = Registry::get("database");
            $earnings = $database->query()->from("stats", array("SUM(amount)" => "earn"))->where("item_id=?",$stat->item_id)->all();
            $datas[$url["host"]] += $earnings[0]["earn"];
        }
        
        foreach ($datas as $key => $value) {
            array_push($records, array(
                "domain" => $key,
                "amount" => $value
            ));
        }
        $payments = Payment::all(array("user_id = ?" => $user_id));
        $view->set("records", $records);
        $view->set("payments", $payments);
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function makepayment($user_id) {
        $this->seo(array("title" => "Make Payment", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $datas = array();$records = array();
        $payee = User::first(array("id = ?" => $user_id), array("id", "name", "email", "phone"));
        $account = Account::first(array("user_id = ?" => $user_id));

        $database = Registry::get("database");
        $amount = $database->query()
            ->from("stats", array("SUM(amount)" => "earn"))
            ->where("user_id=?",$user_id)
            ->where("live=?",0)
            ->all();

        $paid = $database->query()
            ->from("payments", array("SUM(amount)" => "earn"))
            ->where("user_id=?",$user_id)
            ->all();

        if (RequestMethods::post("action") == "payment") {
            $payment = new Payment(array(
                "user_id" => $user_id,
                "amount" => RequestMethods::post("amount"),
                "mode" => RequestMethods::post("mode"),
                "ref_id" => RequestMethods::post("ref_id"),
                "website" => RequestMethods::post("website"),
                "live" => 1
            ));
            $payment->save();

            $this->notify(array(
                "template" => "makePayment",
                "subject" => "Payments From EarnBugs Team",
                "user" => $payee,
                "payment" => $payment,
                "account" => $account
            ));

            self::redirect("/finance/pending");
        }

        $stats = Stat::all(array("user_id = ?" => $user_id), array("DISTINCT item_id"));
        foreach ($stats as $stat) {
            $item = Item::first(array("id = ?" => $stat->item_id), array("url"));
            $url = parse_url(trim($item->url));
            $earnings = $database->query()->from("stats", array("SUM(amount)" => "earn"))->where("item_id=?",$stat->item_id)->all();
            $datas[$url["host"]] += $earnings[0]["earn"];
        }
        
        foreach ($datas as $key => $value) {
            array_push($records, array(
                "domain" => $key,
                "amount" => $value
            ));
        }
        $payments = Payment::all(array("user_id = ?" => $user_id));
        $view->set("records", $records);
        $view->set("payments", $payments);

        $view->set("payee", $payee);
        $view->set("account", $account);
        $view->set("amount", $amount[0]["earn"] - $paid[0]["earn"]);
    }

    /**
     * Earning on a Content
     * @before _secure, changeLayout, _admin
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

    /**
     * @before _secure, changeLayout, _admin
     */
    public function payments() {
        $this->seo(array("title" => "Payments", "view" => $this->getLayoutView()));

        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        $user_id = RequestMethods::get("user_id");
        if (!empty($user_id)) {
            $where = array(
                "user_id = ?" => $user_id
            );
        } else{
            $where = array();
        }

        $view = $this->getActionView();
        $payments = Payment::all($where, array("*"), "created", "desc", $limit, $page);
        $count = Payment::count($where);

        $view->set("payments", $payments);
        $view->set("limit", $limit);
        $view->set("page", $page);
        $view->set("count", $count);
        $view->set("user_id", $user_id);
    }
    
}
