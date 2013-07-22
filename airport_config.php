<?php
/*
Input your aiport abbreviation, like 'IAH', 'KAUS'. 
*/
define("AIRPORT", "KAUS");

/*
Input the fullname of your airport, like 'George Bush Intercontinental Airport' for 'IAH',
'Austin-Bergstrom International Airport' for 'KAUS'.
*/
define("AIRPORT_FULLNAME", "Austin-Bergstrom International Airport");

/*
Input the username you registered in your flightaware account.
*/
define("USERNAME", "username");

/*
Input your Flightaware APIKey.
If don't have one, obtain one from :
https://flightaware.com/commercial/flightxml/key
*/
define("APIKEY", "password");

/*
There're two modes of displaying options.
Display departure (scheduled, departed) & arrival (enroute, arrived) two tables;
Display scheduled, departed, arrived and enroute four tables;
Input '2' will turn the two_table mode on.
Input '4' will turn the four_table mode on.
*/
define("DISPLAY_OPTION", '2');

/*
For enroute, arrived, scheduled & departed four categories,
choose the maximum number of flights displayed for each category.
Here, the number input is '5' for each category, so there will
be at most '5*4 = 20' flights displayed on the screen.
Input integer should be at least "3";
*/
define("NUM_FLIGHTS_DISPLAYED", 5);

/*
Input 'filter' parameter.
Filter	string	can be "ga" to show only general aviation traffic, 
"airline" to only show airline traffic, 
or null/empty to show all traffic.
*/
define("FILTER_PARAM", "ga");


/*
Input your local timezone like 'CDT', 'EDT'.
*/

define('TIMEZONE', 'CDT');

/*
The data requested from server will be temporarily stored in a file until the next request.
The next request will overwrite the data. So if multi-computers in the airport display
the fids at the same time, only one computer will request data from server, the other computers
will just load data from the file.
Input any filename with suffix '.xml' like 'flightdata.xml' below;
*/
define("CACHE_FILENAME", "flightdata.xml");

/*
FAA considers a flight to be delayed when it is 15 minutes later than its scheduled time.
Here airport could adjust its delay threshhold in seconds. "900" seconds below is 15 mins.
*/
define("DELAY_THRESHHOLD", 900);
/*
The program will reload the 'xml_parser.php' to update the data displayed in fids by making 
another flightxml data request to server. The program will automatically estimate the most 
possible moment when the next change in flights will happen and refresh the data 
according to the refresh_interval calculated. But the calculation is just a best estimation,
airport should customize the range of refresh_interval to its acceptable range so that
if the estimation go beyond the range, the interval will set to the limits. Below the range is 
from 600 secs (10 mins) to 60 secs (1 min). So the fids will refresh data displayed as fast as
every minute and as late as every ten minutes.
*/
define("MAX_REFRESH_INTERVAL", 600);
define("MIN_REFRESH_INTERVAL", 60);
?>
