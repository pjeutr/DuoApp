#!/usr/bin/php
<?php

/*
* coapServer & Listener
*
* Runs on all controllers
* - Detects input changes (readers/buttons/sensors) and sends them to the Master
* - Listen for commands from the master (activate/output/status)
*/

/* 
* Outgoing calls to master
*/
function callApi($input, $data) {
	global $loop;

	if(false) {
    	$url = "http://".getMasterControllerIP()."/?/api/input/".$input."/".$data;
        mylog("apiCall:".$url);

		$client = new React\HttpClient\Client( $loop );
		$request = $client->request('GET', $url);
		$request->on('response', function ( $response ) {
		    $response->on('data', function ( $data ) {
		    	mylog("apiCall return=".json_encode($data));
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
			mylog("coapCall return=".json_encode($data));
		    return $data;
		});
	}
       
}

/*
* Listen for input changes (inputListener)
*/
//TODO class meegeven werkte niet, daarom maar de index van array
//$inputObserver = new \Calcinai\Rubberneck\Observer($loop, EpollWait::class);
$wiegandObserver = new \Pjeutr\PhpNotify\Observer($loop, 0);
$wiegandObserver->onModify(function($file_name){
	//find the value
	$value = getInputValue($file_name);
	$parts = explode(':',$value);
	$nr = $parts[0];
	$keycode = $parts[1];
	$reader = $parts[2];
	mylog("Wiegand:". $reader.":".$keycode);
	$result = callApi($reader, $keycode);
    mylog(json_encode($result));
});	

//$inputObserver = new \Calcinai\Rubberneck\Observer($loop, InotifyWait::class);
$inputObserver = new \Pjeutr\PhpNotify\Observer($loop, 1);
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
	//sleep(1);
});

//listen voor gpio inputs
global $inputArray;
foreach ($inputArray as $value) {
	mylog("inputObserver init:". $value ." \n");
    $inputObserver->watch($value);
}
//$inputObserver->watch('/sys/class/gpio/gpio170/value');

//listen voor wiegand readers
$wiegandObserver->watch('/dev/wiegand');


/*
* Run coapListener
*/
$server = new PhpCoap\Server\Server( $loop );
$server->receive( 5683, '0.0.0.0' );

$server->on( 'request', function( $req, $res, $handler ) {
	$o = $req->GetOptions();
	$type = readOption($o,0);
	$result = 'dummy';

	switch ($type) {
	    case 'input':
	        $result = $type.": is not available on a Slave controller!";
	        break;
	    case 'output':
			$from = $handler->getPeerHost(); //will allways be master
			$door_id = readOption($o,1);
			$state = readOption($o,2);
			$gpios = explode("-", readOption($o,3));
			mylog("coapListener: Open door ".$door_id." state=".$state."s gpios=".json_encode($gpios));
			$result = operateOutput($door_id, $state, $gpios);
			break;
	    case 'activate':
			$from = $handler->getPeerHost(); //will allways be master
			$door_id = readOption($o,1);
			$duration = readOption($o,2);
			$gpios = explode("-", readOption($o,3));
			mylog("coapListener: Activate door ".$door_id." for ".$duration."s gpios=".json_encode($gpios));
			$result = activateOutput($door_id, $duration, $gpios);
			break;
	    case 'status':
			$from = $handler->getPeerHost(); //will allways be master
			$gpios = explode("-", readOption($o,1));
			mylog("coapListener: Status gpios=".json_encode($gpios));
			$result = array();
			foreach ($gpios as $gpio) {
	        	$result[] = array($gpio => getGPIO($gpio));
	    	}
	        break;
	    default:
	    	$result = "invalid request";
	}

	$res->setPayload( json_encode( $result ) );
	$handler->send( $res );
});

