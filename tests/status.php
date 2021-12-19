#!/usr//bin/php
<?php

/*
* Show status of all outputs
*/

require_once '/maasland_app/vendor/autoload.php';
require_once '/maasland_app/www/lib/limonade.php';;
require_once '/maasland_app/www/lib/db.php';
require_once '/maasland_app/www/lib/helpers.php';
require_once '/maasland_app/www/lib/logic.slave.php';

echo "\nMaster=". getGPIO(GVAR::$GPIO_MASTER)." => ".(checkIfMaster() ? "Master" : "Slave");
echo "\nVoltage=". getGPIO(GVAR::$OUT12V_PIN);
echo "\nBuzzer=". getGPIO(GVAR::$BUZZER_PIN);
echo "\nRunning led=". getGPIO(GVAR::$RUNNING_LED);
echo "\nButton1=". getGPIO(GVAR::$GPIO_BUTTON1);
echo "\nButton2=". getGPIO(GVAR::$GPIO_BUTTON2);
echo "\nDoorstatus1=". getGPIO(GVAR::$GPIO_DOORSTATUS1);
echo "\nDoorstatus2=". getGPIO(GVAR::$GPIO_DOORSTATUS2);
echo "\nDoor1=". getGPIO(GVAR::$GPIO_DOOR1);
echo "\nDoor2=". getGPIO(GVAR::$GPIO_DOOR2);
echo "\nFirmware=". getGPIO(GVAR::$GPIO_FIRMWARE)." => ";

if(checkIfFactoryReset()) {
	echo "doFactoryReset ";
	try {
		doFactoryReset();
	} catch (Exception $e) {
	    echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	
	echo "done";
} else {
	echo "do nothing";
}
echo "\n";