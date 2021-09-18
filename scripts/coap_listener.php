#!/usr//bin/php
<?php

// recieves coap request from the master controller
// 1 or 2 opens door
// 3 or 4 sound alarm

require_once '/maasland_app/vendor/autoload.php';
require_once '/maasland_app/www/lib/limonade.php';;
require_once '/maasland_app/www/lib/db.php';
require_once '/maasland_app/www/lib/helpers.php';
require_once '/maasland_app/www/lib/logic.slave.php';

//initialize database connection
$dsn = "sqlite:/maasland_app/www/db/dev.db";
$db = new PDO($dsn);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
option('dsn', $dsn);
option('db_conn', $db);
option('debug', true);

echo setupGPIOInputs();

if( checkIfMaster() ) {
	require_once '/maasland_app/www/lib/logic.door.php';
	//load models for used db methods
	require_once '/maasland_app/www/lib/model.report.php';
	require_once '/maasland_app/www/lib/model.user.php';
	require_once '/maasland_app/www/lib/model.settings.php';
	require_once '/maasland_app/www/lib/model.door.php';
	require_once '/maasland_app/www/lib/model.controller.php';
	require_once '/maasland_app/www/lib/model.timezone.php';
	require_once '/maasland_app/www/lib/model.rule.php';
	echo "Extra Master requirements loaded\n";

	//anounce as master server
	$r = mdnsPublish();
	mylog("mdnsPublish return=".json_encode($r));
}

//get ip
$ifconfig = shell_exec('/sbin/ifconfig eth0');
preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
$thisControllerIp = $match[1];
mylog("thisControllerIp=".$thisControllerIp."\n");

//start coap server
$loop = React\EventLoop\Factory::create();
$server = new PhpCoap\Server\Server( $loop );
$server->receive( 5683, $thisControllerIp);

//get master ip
$masterControllerIp = $thisControllerIp;
if( !checkIfMaster() ) {
	$result = mdnsBrowse("_master._sub._maasland._udp");
	mylog(json_encode($result)."\n");
	$masterControllerIp = $result[0][7];
}
mylog("masterControllerIp=".$masterControllerIp."\n");


/*
* coap-client -m get coap://192.168.178.137/status/170-169 = print button status 
* coap-client -m get coap://192.168.178.137/input/1/1234 
* coap-client -m get coap://192.168.178.137/output/2/1 = Door 2 for open
* coap-client -m get coap://192.168.178.137/output/1/0 = Door 1 for close
* TODO-coap-client -m get coap://192.168.178.137/output/1/2|on|off/2-10-66-79 = Door 1 for 2 seconds
* coap-client -m get coap://192.168.178.137/activate/1/2 = Door 1 for 2 seconds
* coap-client -m get coap://192.168.178.137/activate/2/5/2-10-66-79 = Door 2 all leds and buzzer for 5 seconds
*
* TODO user auth?
* coap-client -m get -u maaslnd -k WGH coap://slave/activate/2/5/2-10-66-79
*/

/*
* Helper to read options from the request (ReactPHP specific)
* to prevent errors, check if an option exist before reading the value
*/	
function readOption($option, $i) {
	if (array_key_exists($i, $option)) {
		return $option[$i] ? $option[$i]->getValue() : null;
	}
	return null;
}

/*
* Listen for input changes 
*/
$observer = new \Calcinai\Rubberneck\Observer($loop);

$observer->onModify(function($file_name){
	mylog("Modified:". $file_name);
	$input = resolveInput($file_name);
	$value = getInputValue($file_name);
	//check if this is a wiegand reader
	if($input == 1) {
		$lastline = exec("tail -n1 /var/log/messages");
		if(strpos($lastline,"keycode") > 0){
			//TODO can't go searching in log, change wiegand driver!
			$parts = explode(' ',$lastline);
			$keycode = explode('=',$parts[8])[1];
			$reader = explode('=',$parts[9])[1];
			mylog(json_encode($parts));
			mylog("Modified:". $keycode.":".$reader);
			$result =  inputReceived($reader, $keycode);
		}
	}
	//take action if a button is pressed
	if($value == 1) { 	
		$result =  inputReceived($input, "");
		mylog(json_encode($result));
	}    
});
//$observer->watch('/sys/kernel/wiegand/read'); werkt niet, dan maar via messages...
//maybe adding a newline? or write at a different place. not in sys
$observer->watch('/var/log/messages');
$observer->watch('/sys/class/gpio/gpio170/value');
//$observer->watch('/sys/class/gpio/gpio170/value');
//$observer->watch('/sys/class/gpio/gpio68/value');

/*
* Run server / listener
* TODO integrate with match_listener
*/	
$server->on( 'request', function( $req, $res, $handler ) use ($loop){
	$o = $req->GetOptions();
	$type = readOption($o,0);
	$result = 'dummy';
	if($type == 'input') { //ON MASTER
		$from = $handler->getPeerHost();
		$input = readOption($o,1);
		$data = readOption($o,2);
		mylog("coapServer: ".checkIfMaster()." input=".$input." data=".$data);
		//$result = checkIfMaster() ? handleInput($from, $input, $data) : "can only be called on master";
		$result =  inputReceived($input, $data);
	/*
	* TODO split up?
	* Above requests will only happen on master
	* Following request will happen on slave and master (match_listener/coap_lister)
	*/	
	} elseif($type == 'output') {
		$from = $handler->getPeerHost(); //will allways be master
		$door_id = readOption($o,1);
		$state = readOption($o,2);
		$gpios = explode("-", readOption($o,3));
		mylog("coapListener: Open door ".$door_id." state=".$state."s gpios=".json_encode($gpios));
		$result = operateOutput($door_id, $state, $gpios);
	} elseif($type == 'activate') { 
		$from = $handler->getPeerHost(); //will allways be master
		$door_id = readOption($o,1);
		$duration = readOption($o,2);
		$gpios = explode("-", readOption($o,3));
		mylog("coapListener: Open door ".$door_id." for ".$duration."s gpios=".json_encode($gpios));
		$result = activateOutput($door_id, $duration, $gpios);
	} elseif($type == 'status') { 
		$from = $handler->getPeerHost(); //will allways be master
		$gpios = explode("-", readOption($o,1));
		mylog("coapListener: Status gpios=".json_encode($gpios));
		$result = array();
		foreach ($gpios as $gpio) {
        	$result[] = array($gpio => getGPIO($gpio));
    	}
    } elseif($type == 'devices') { //debug helper
		$result = readOption($o,1);
    } elseif($type == 'data') { //debug helper
		$result = readOption($o,1);		
	} else {
		$result = "invalid request";
	}
	$res->setPayload( json_encode( $result ) );
	$handler->send( $res );
});

$loop->run();
