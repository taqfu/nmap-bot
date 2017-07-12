<?php
include "nmap-bot.cfg";

$timestamp = mdate("m-d-Y-His");
$filename = getcwd() . "/scans/" . date ("m-d-Y") . "/" . $timestamp;
exec("bash -lc 'echo " . ROOT_PASSWORD . " | sudo -S nmap " . SCAN_TYPE . " -oX " . $filename  . ".xml " . IP_RANGE . "'");
parse_scan ($filename);

function parse_scan($filename){
  $host_num=1;
  $approved_mac_arr = load_approved_computers();
  $xml = simplexml_load_file($filename);
  $json = json_encode($xml);
  $scan_arr = json_decode($json, true);

  foreach ($scan_arr['host'] as $host){
    $msg= "\nHost #" . $host_num . " of " . count($scan_arr['host']) . " "
      . $host['hostnames']['hostname']['@attributes']['name'] . "\n";
    if (count($host['address'])>1){
    	foreach ($host['address'] as $address){
      	$msg.=  $address["@attributes"]['addrtype'] . ":" . $address["@attributes"]['addr'] . "\n";
      	if ($address["@attributes"]['addrtype'] == "mac"){
      		$msg.=  $address["@attributes"]['vendor'] . "\n";
      		if   (!in_array($address['@attributes']['addr'],$approved_mac_arr)){
      				push_it_real_good($msg);
      		}
      	 }
      	}
  	} else {
  		$msg.=  $host['address']['@attributes']['addrtype'] . ":" . $host['address']['@attributes']['addr'] . "\n";
  		if ($host['address']['@attributes']['addrtype'] == "mac"){
  			$msg.=  $host['@attributes']['vendor'] . "\n";
  			if   (!in_array($host['address']['@attributes']['addr'],$approved_mac_arr)){
  				push_it_real_good($msg);
  			}
  		}
  	}
    $host_num++;
  }
}
    /*
    	if (isset($host['ports']['port'])){
    		$msg.=  count($host['ports']['port']) . " ports open:\n";

    		foreach ($host['ports']['port'] as $port){
    			$msg.=  " " .  $port["@attributes"]["protocol"] . " port #"
    			  . $port["@attributes"]["portid"] . " " . $port['service']['@attributes']['name'] . "\n";
    		}
    	}
    */


function load_approved_computers(){
	$approved_mac_arr = [];
	$fp = fopen ( getcwd() . "/approved-macs.cfg", "r");
	while ($buffer = fgets($fp)){
		$approved_mac_arr[] = str_replace("\n", "", $buffer);
	}
	return $approved_mac_arr;
}

function push_it_real_good($msg){
	curl_setopt_array($ch = curl_init(), array(
    CURLOPT_URL => "https://api.pushover.net/1/messages.json",
    CURLOPT_POSTFIELDS => array(
      "token" => PUSHOVER_APP_TOKEN,
      "user" => PUSHOVER_USER_KEY,
      "message" => $msg,
    ),
    CURLOPT_SAFE_UPLOAD => true,
  	CURLOPT_RETURNTRANSFER => true,
	));
	curl_exec($ch);
	curl_close($ch);
}
