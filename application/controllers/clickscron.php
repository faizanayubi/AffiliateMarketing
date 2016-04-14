<?php

/**
 * Clicks CRON
 * 
 * @author Hemant Mann
 */
use Framework\Registry as Registry;

class ClicksCron extends Auth {
	public function __construct($options = []) {
		parent::__construct($options);
		$this->noview();
	}

	/**
	 * @before _secure
	 */
	public function index() {
		$this->log("Clicks Cron started");
		$cron = Registry::get("MongoDB")->cron;
		$urls = Registry::get("MongoDB")->urls;
		$users = User::all();

		foreach ($users as $u) {
			$this->log("User: " . $u->id);
			$links = $urls->find(['user_id' => $u->id], ['link_id']);
			$i = 0; $count = 0; $record = $cron->findOne(['user_id' => (int) $u->id]);

			$clicks = 0; $earnings = 0; $rpm = 0;
			foreach ($links as $l) {
				$link = Link::first(["id = ?" => $l['link_id']]);
				$result = $link->googl("twoHours");
				$clicks += $result['click'];
				$earnings += $result['earning'];
				$rpm += $result['rpm'];
				if ($result["click"] > 1) {
					++$count;
				}

				if ($i > 3) {
					sleep(1);
					$i = 0;
				}
				++$i;
			}

			$doc = [
				'clicks' => $clicks,
				'earnings' => $earnings,
				'rpm' => $rpm/$count,
				'updated' => new \MongoDate()
			];
			if (isset($record)) {
				$last = date('Y-m-d', $record['created']->sec);
				$today = date('Y-m-d');

				$interval = date_diff(date_create($today), date_create($last));
				if ((int) $interval->format('%a') >= 1) {
					$doc['clicks'] = 0;
					$doc['earnings'] = 0;	
				} else {
					$doc['clicks'] = (int) $record['clicks'] + $clicks;
					$doc['earnings'] = $record['earnings'] + $earnings;	
				}

				$cron->update(['_id' => $record['_id']], [
					'$set' => $doc
				]);
			} else {
				$cron->insert(array_merge($doc, [
					'user_id' => (int) $u->id,
					'created' => new \MongoDate()
				]));
			}
		}
		$this->log("Clicks Cron ended");
	}

	protected function log($message = "") {
        $logfile = APP_PATH . "/logs/" . date("Y-m-d") . ".txt";
        $new = file_exists($logfile) ? false : true;
        if ($handle = fopen($logfile, 'a')) {
            $timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
            $content = "[{$timestamp}] {$message}\n";
            fwrite($handle, $content);
            fclose($handle);
            if ($new) {
                chmod($logfile, 0777);
            }
        }
    }

	/**
     * @protected
     */
    public function _secure() {
        if (php_sapi_name() !== 'cli') {
            self::redirect("/404");
        }
    }
}
