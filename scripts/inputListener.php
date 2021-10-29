#!/usr//bin/php
<?php

/*

* Second script, only inputListener 
* coapServer & Listener, could not multitask
* So the webapi is used instead

* Runs on all controllers
* - detects input changes (readers/buttons/sensors)
* 
*/

require_once '/maasland_app/vendor/autoload.php';
require_once '/maasland_app/www/lib/limonade.php';;
require_once '/maasland_app/www/lib/db.php';
require_once '/maasland_app/www/lib/helpers.php';
require_once '/maasland_app/www/lib/logic.slave.php';

//configure and initialize gpio 
echo configureGPIO();

if( checkIfMaster() ) {
	//initialize database connection
	$dsn = "sqlite:/maasland_app/www/db/dev.db";
	$db = new PDO($dsn);
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	option('dsn', $dsn);
	option('db_conn', $db);
	option('debug', true);

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

function callApi($input, $data) {
	global $loop;

    mylog((checkIfMaster() ? 'Master' : 'Slave' )." inputReceived:".$input." data=".$data);
    if ( checkIfMaster() ) {
        return handleInput(getMasterControllerIP(), $input, $data);
    } else {
        //tunnel through coap to the master where handleInput is called
        //return makeInputCoapCall($input."/".$data);

    	if(true) {
	    	$url = "http://".getMasterControllerIP()."/?/api/input/".$input."/".$data;
	        mylog("apiCall:".$url);

			$client = new React\HttpClient\Client( $loop );
			$request = $client->request('GET', $url);
			$request->on('response', function ( $response ) {
			    $response->on('data', function ( $data ) {
			        mylog($data);
			        return $data;
			    });
			});
			$request->end();
    	} else {
	        $url = "coap://".getMasterControllerIP()."/input/".$input."/".$data;
	        mylog("coapCall:".$url);
	        //request
	        $client = new PhpCoap\Client\Client( $loop );
	        #Er is een bug bij client-get, zelf fixen?
	        #https://github.com/cfullelove/PhpCoap/issues/5
			$client->get($url, function( $data ) {
				mylog($data);
			    return $data;
			});
		}
    }
}

$loop = React\EventLoop\Factory::create();

/*
* Listen for input changes (inputListener)
*/
//TODO class meegeven werkte niet, daarom maar de index van array
//$inputObserver = new \Calcinai\Rubberneck\Observer($loop, EpollWait::class);
$wiegandObserver = new \Calcinai\Rubberneck\Observer($loop, 0);
$wiegandObserver->onModify(function($file_name){
	//mylog("Modified:". $file_name. "\n");
	//determine the input number for this file
	$input = resolveInput($file_name);
	//find the value
	$value = getInputValue($file_name);
	//mylog("value:". $value. "\n");

	$parts = explode(':',$value);
	$nr = $parts[0];
	$keycode = $parts[1];
	$reader = $parts[2];
	mylog("Wiegand:". $reader.":".$keycode);
	$result =  callApi($reader, $keycode);
    mylog("Wiegand2:". $result);
});	
//$inputObserver = new \Calcinai\Rubberneck\Observer($loop, InotifyWait::class);
$inputObserver = new \Calcinai\Rubberneck\Observer($loop, 1);
$inputObserver->onModify(function($file_name){
	mylog("Modified:". $file_name. "\n");
	//determine the input number for this file
	$input = resolveInput($file_name);
	//find the value
	$value = getInputValue($file_name);
	//mylog("value:". $value. "\n");
	//take action if a button is pressed
	if($value == 1) { 
		mylog("Button:". $input);
		$result =  callApi($input, "");
        mylog(json_encode($result));
	}   
	//TODO sleep / prevent klapperen 
	sleep(1);
});
//Declare inputs to observe
//$observer->watch('/dev/wiegand'); 
//$observer->watch('/sys/kernel/wiegand/read'); 
//$observer->watch('/sys/class/wiegand/value'); 
//maybe adding a newline? or write at a different place. not in sys
//$observer->watch('/var/log/messages');
$wiegandObserver->watch('/dev/wiegand');
$inputObserver->watch('/sys/class/gpio/gpio170/value');
//$observer->watch('/sys/class/gpio/gpio170/value');
//$observer->watch('/sys/class/gpio/gpio68/value');

$loop->run();
