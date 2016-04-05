<?php

/**
 * Clicks CRON
 * 
 * @author Hemant Mann
 */
use Framework\Registry as Registry;

class ClicksCron extends Auth {
	/**
	 * @before _secure
	 */
	public function index() {
		$this->log("Clicks Cron started");
		$cron = Registry::get("MongoDB")->cron;
		$users = User::all();

		foreach ($users as $u) {
			$this->log("User: " . $u->id);
			$links = Link::all(array("user_id = ?" => $u->id));
			$i = 0; $count = 0; $record = $cron->findOne(['user_id' => (int) $u->id]);

			$clicks = 0; $earnings = 0; $rpm = 0;
			foreach ($links as $l) {
				$result = $l->googl("twoHours");
				$clicks += $result['click'];
				$earnings += $result['earning'];
				$rpm += $result['rpm'];
				if ($result["click"] > 1) {
					++$count;
				}

				if ($i > 5) {
					sleep(1);
					$i = 0;
				}
				++$i;
			}

			$doc = [
				'clicks' => $clicks,
				'earnings' => $earnings,
				'rpm' => $rpm/$count,
				'updated' => new \MongoDate(),
				'created' => new \MongoDate()
			];
			if (isset($record)) {
				$cron->update(['_id' => $record['_id']], [
					'$set' => $doc
				]);
			} else {
				$cron->insert(array_merge($doc,[
					'user_id' => (int) $u->id
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
