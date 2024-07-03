<?php
use WP_CLI\Utils;

function wpcli_get_orcaoms_spider_iptables($args, $assoc_args){

	$spider_ips = wp_cache_get('orca_oms_spiders_log_iptable', 'orca');
    if (!empty($spider_ips)) {
    	//获取最近10分钟数据
		$from = date('Y-m-d+H:i:s', strtotime('-10 min'));
		$to = date('Y-m-d+H:i:s');
		$url = "https://orcaoms.com/get_spiders_log?web_name=&content_search=&start={$from}&end={$to}";
	} else {
		//获取最近7天数据
		$from = date('Y-m-d', strtotime('-7 days'));
		$to = date('Y-m-d');
		$url = "https://orcaoms.com/get_spiders_log?web_name=&content_search=&start={$from}+00:00:00&end={$to}+23:59:59";
	}
	$result = file_get_contents($url);
	if(empty($result)) {
		WP_CLI::error( 'empty result' );
	}
	$result = json_decode($result, true);

	if($result['code'] != 200) {
		WP_CLI::error( 'response error' );
	}

	if(!empty($result['data'])){
		$ips = [];
		foreach($result['data'] as $data){
		    if(false !== preg_match('/(\d+\.\d+\.\d+\.\d+)/',$data['content'], $match)){
		        $ip = $match[1];
		        $ips[] = $ip;
		    }
		}

		if (!empty($spider_ips)) {
			//merge
			$ips = array_merge($spider_ips, $ips);
		}

		if(!empty($ips)){
			$ips = array_unique($ips);
			wp_cache_set('orca_oms_spiders_log_iptable', $ips, 'orca', 86400);
		}
	}
	
	WP_CLI::success( 'get successfully.' );
}