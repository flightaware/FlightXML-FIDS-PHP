<?php
require('airport_config.php');
require('four_table.php');
require('two_table.php');
$options = array(
				'trace' => true,
				'exceptions' => 0,
				'login' => USERNAME,
				'password' => APIKEY,
				);
$client = new SoapClient('http://flightxml.flightaware.com/soap/FlightXML2/wsdl', $options);

$params = array("airport" => AIRPORT, "howMany" => "5", "filter" => "", "offset" => "0" );

$arrived = $client->Arrived($params)->ArrivedResult->arrivals;

$departed = $client->Departed($params)->DepartedResult->departures;

$scheduled = $client->Scheduled($params)->ScheduledResult->scheduled;

$enroute = $client->Enroute($params)->EnrouteResult->enroute;

if (DISPLAY_OPTION == '4') {
	$tb = new four_table();

	$tb->display();

}
else {
	
	$tb = new two_table();

	$tb->display();


}


?>