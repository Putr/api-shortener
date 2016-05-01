<?php

namespace Model;

class Redis {

	public $client;

	function __construct($client) {
		$this->client = $client;
	}

	public function setUrl($domain, $shortUrl, $url) {
		$this->client->set(sprintf('%s_url_%s', $domain, $shortUrl), $url);
	}

	public function getUrl($domain, $shortUrl) {
		return $this->client->get(sprintf('%s_url_%s', $domain, $shortUrl));
	}

	public function setMeta($domain, $shortUrl, $meta) {
		$this->client->set(
			sprintf('%s_meta_%s', $domain, $shortUrl),
		 	json_encode($meta)
		);
	}

	public function getMeta($domain, $shortUrl) {
		$meta = $this->client->get(sprintf('%s_meta_%s', $domain, $shortUrl));

		if ($meta === null) {
			return false;
		}

		$meta = json_decode($meta, true);
		return $meta;
	}

	public function getStats($domain, $shortUrl) {
		$numAll = (integer) $this->client->get(sprintf('%s_num_%s_all', $domain, $shortUrl));
		$numToday = (integer) $this->client->get(sprintf('%s_num_%s_%s', $domain, $shortUrl, date('Ymd')));

		$today = (integer) date('Ymd');
		$numWeek = $numMonth = $numToday;

		for ($i=1; $i < 31; $i++) { 
			$thisDay = $this->client->get(sprintf('%s_num_%s_%s', $domain, $shortUrl, $today - $i));

			if ($thisDay === NULL) {
				$thisDay = 0;
			}
			if ($i < 8) {
				$numWeek += $thisDay;
			}
			$numMonth += $thisDay;

			if ($numMonth === $numAll) {
				break;
			}
		}

		return [
			'hits_today'  => $numToday,
			'hits_7days'  => $numWeek,
			'hits_30days' => $numMonth,
			'hits_all'    => $numAll
		];
	}

	public function removeRecord($domain, $shortUrl) {
		$this->client->del([
			sprintf('%s_meta_%s', $domain, $shortUrl),
			sprintf('%s_url_%s', $domain, $shortUrl),
			sprintf('%s_num_%s_all', $domain, $shortUrl)
		]);

		$keys = $this->client->keys(sprintf('%s_num_%s_*', $domain, $shortUrl));

		if (count($keys) > 0) {
			$this->client->del($keys);
		}
	}

	public function getAllRecordsForDomain($domain) {
		$keys = $this->client->keys($domain . '_url_*');

		$payload = [];
		foreach ($keys as $key) {
			preg_match_all("/(.+)_url_(.+)/u", $key, $matches);
			$domain = $matches[1][0];
			$shortUrl = $matches[2][0];
			$data = [
				'shortUrl'  => $shortUrl,
				'stats'     => $this->getStats($domain, $shortUrl),
				'targetUrl' => $this->getUrl($domain, $shortUrl)
			];

			$meta = $this->getMeta($domain, $shortUrl);
			if ($meta === false) {
				continue;
			}
			$payload[] = array_merge($data, $meta);
		}

		return $payload;
	}






}