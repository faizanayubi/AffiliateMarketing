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
    public function earnings() {
        $this->seo(array("title" => "Earnings Finance", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $startdate = RequestMethods::get("startdate", date('Y-m-d', strtotime("-7 day")));
        $enddate = RequestMethods::get("enddate", date('Y-m-d', strtotime("now")));
        $website = RequestMethods::get("website", "http://www.khattimithi.com");

        $amount = 0;
        $where = array(
            "url LIKE ?" => "%{$website}%",
            "created >= ?" => $this->changeDate($startdate, "-1"),
            "created <= ?" => $this->changeDate($enddate, "1")
        );
        $items = Item::all($where, array("id"));
        $count = Item::count($where);

        foreach ($items as $item) {
            $earnings = Earning::all(array("item_id = ?" => $item->id), array("amount"));
            foreach ($earnings as $earning) {
                $amount += $earning->amount;
            }
        }
        
        $view->set("startdate", $startdate);
        $view->set("enddate", $enddate);
        $view->set("items", $items);
        $view->set("count", $count);
        $view->set("website", $website);
        $view->set("amount", $amount);
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function makepayment($user_id) {
        $this->seo(array("title" => "Make Payment", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $record = Stat::first(array("user_id = ?" => $user_id), array("created"), "created", "desc");
        $latest = strftime("%Y-%m-%d", strtotime($record->created));
        $payee = User::first(array("id = ?" => $user_id), array("id", "name", "email", "phone"));
        $account = Account::first(array("user_id = ?" => $user_id));

        $database = Registry::get("database");
        $amount = $database->query()
            ->from("stats", array("SUM(amount)" => "earn"))
            ->where("user_id=?",$user_id)
            ->where("created LIKE ?", "%{$latest}%")
            ->where("live=?",0)
            ->all();

        $paid = $database->query()
            ->from("payments", array("SUM(amount)" => "earn"))
            ->where("user_id=?",$user_id)
            ->all();

        if (RequestMethods::post("action") == "payment") {
            $payment = new Payment(array(
                "user_id" => $user_id,
                "amount" => round($amount[0]["earn"] - $paid[0]["earn"], 2),
                "mode" => RequestMethods::post("mode"),
                "ref_id" => RequestMethods::post("ref_id"),
                "live" => 1
            ));

            $payment->save();

            $this->notify(array(
                "template" => "makePayment",
                "subject" => "Payments From ChocoGhar Team",
                "user" => $payee,
                "payment" => $payment,
                "account" => $account
            ));

            self::redirect("/finance/records");
        }

        $view->set("payee", $payee);
        $view->set("account", $account);
        $view->set("amount", $amount[0]["earn"]);
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
