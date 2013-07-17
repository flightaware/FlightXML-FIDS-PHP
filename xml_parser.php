<?php
require('airport_config.php');

require('table_class.php');
$options = array(
				'trace' => true,
				'exceptions' => 0,
				'login' => USERNAME,
				'password' => APIKEY,
				);
$client = new SoapClient('http://flightxml.flightaware.com/soap/FlightXML2/wsdl', $options);

$params = array("airport" => AIRPORT, "howMany" => NUM_FLIGHTS_DISPLAYED, "filter" => FILTER_PARAM, "offset" => OFFSET );

$arrived = $client->Arrived($params)->ArrivedResult->arrivals;

$departed = $client->Departed($params)->DepartedResult->departures;

$scheduled = $client->Scheduled($params)->ScheduledResult->scheduled;

$enroute = $client->Enroute($params)->EnrouteResult->enroute;

$groups = array('arrived' =>$arrived, 'enroute' =>$enroute, 'departed'=>$departed, 'scheduled'=>$scheduled);

$myFile = "flightdata.xml";

if (!$fh = fopen($myFile, 'w') )
{
	print "Cannot open file($myFile)";
	exit;
}




if (!fwrite($fh, serialize($groups)) )
{
	print "Cannot write to file($myFile)";
	exit;
}
	

file_put_contents($myFile,serialize($groups));

fclose($fh);

$tb = new table_class();

$tb->display();

echo time();



?>