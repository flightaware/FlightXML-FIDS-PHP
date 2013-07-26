
<?php
date_default_timezone_set(timezone_name_from_abbr(TIMEZONE));
class table_class
{


	public $interval; 
	public $direction;
	public $time_type;
	public $arrival;
	public $departure;
	public $arrived;
	public $departed;
	public $scheduled;
	public $enroute;
	public $late;
	public $saved_time;
	
	/*
		The construction will check :
		If current time is before the next refresh point, renew 
		the parameters from the cached file.
		If the current time has passed the supposed next refresh time, 
		renew all the parameters by requesting the updated data from server.
		
	*/
	function __construct() {
		
		if (file_exists(CACHE_FILENAME)) {
			$data = unserialize(file_get_contents(CACHE_FILENAME));
			$this->saved_time = $data[4];
			$this->interval = $data[5];
			if ($this->saved_time > (time() - $this->interval) ){
				$this->arrived = $data[0];
				$this->enroute = $data[1];
				$this->departed = $data[2];
				$this->scheduled = $data[3];
				$this->late = false;
				
			}
			else {
				$this->refresh_data();
				
				$this->interval= $this->cal_refresh_interval();	
			}
	
		
		}
		else {
			$this->refresh_data();
			$this->interval= $this->cal_refresh_interval();
		}
	}
	
	/*
		Refresh_data will send flightxml request to the server, obtain flights information, pass
		the information, update the relevant parameters and store the data to the cached file.
	*/
	
	function refresh_data() {
		$options = array(
						'trace' => true,
						'exceptions' => 0,
						'login' => USERNAME,
						'password' => APIKEY,
						);
		$client = new SoapClient('http://flightxml.flightaware.com/soap/FlightXML2/wsdl', $options);

		$params = array("airport" => AIRPORT, "howMany" => NUM_FLIGHTS_DISPLAYED, "filter" => FILTER_PARAM, "offset" => 0 );

		$this->arrived = $client->Arrived($params)->ArrivedResult->arrivals;

		$this->departed = $client->Departed($params)->DepartedResult->departures;

		$this->scheduled = $client->Scheduled($params)->ScheduledResult->scheduled;

		$this->enroute = $client->Enroute($params)->EnrouteResult->enroute;
		
		$this->saved_time = time();
		
		$this->interval= $this->cal_refresh_interval();
		
	

		$groups = array($this->arrived, $this->enroute, $this->departed, $this->scheduled, $this->saved_time, $this->interval);

		$myFile = CACHE_FILENAME;

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
	}
	
	/*
		Display will call gen_table function and generate 
		tables according to the mode set in the configuration
		file.
		If category departed and scheduled are going to be displayed 
		together as departure, arrived and enroute are going to be
		displayed together as arrival, then display function will
		sort the take off/land in time of the merged group in ascending order.
	*/
	
	function display()
	{
		
		
		if (DISPLAY_OPTION == '2') {
			
			$this->arrival = array_merge($this->arrived, $this->enroute);
		
			usort($this->arrival, array("table_class", "cmpAE"));
		
			$this->departure = array_merge($this->departed, $this->scheduled);
			usort($this->departure, array("table_class", "cmpDS"));
			$groups =  array($this->arrival, $this->departure);
		}
		else
		{
			$groups = array($this->arrived, $this->enroute, $this->departed, $this->scheduled);
		}
		
		echo "<table class = 'table'>";
		
		foreach ($groups as $group) {
		
		
			$this->gen_table($group);
		
		}
		
		echo "</table>";
		
		
	}
	
	/*
		Comparison function for arrival which will be used by usort function 
		in the above display function to sort the land in time;
	*/
	static function cmpAE($flightA, $flightE) {
		if (array_key_exists("actualarrivaltime", $flightA)) {
			if (array_key_exists("actualarrivaltime", $flightE)) {
				$a = $flightA->actualarrivaltime;
				$e = $flightE->actualarrivaltime;
			}
			else{
				$a = $flightA->actualarrivaltime;
				$e = $flightE->estimatedarrivaltime;
			}
		}
		else {
			if (array_key_exists("actualarrivaltime", $flightE)) {
				$a = $flightA->estimatedarrivaltime;
				$e = $flightE->actualarrivaltime;
			}
			
			else{
				$a = $flightA->estimatedarrivaltime;
				$e = $flightE->estimatedarrivaltime;
			}
		}
	
		
		
		if ( $a == $e) {
			return 0;
		}
		
		return ($a < $e) ? -1 : 1;
	}
	
	/*
		Comparison function for departure which will be used by usort function 
		in the above display function to sort the take off time;
	*/
	static function cmpDS($flightD, $flightS) {
	
		if (array_key_exists("actualdeparturetime", $flightD)) {
			if (array_key_exists("actualdeparturetime", $flightS)) {
				$d = $flightD->actualdeparturetime;
				$s = $flightS->actualdeparturetime;
			}
			else{
				$d = $flightD->actualdeparturetime;
				$s = $flightS->filed_departuretime;
			}
		}
		else {
			if (array_key_exists("actualdeparturetime", $flightS)) {
				$d = $flightD->filed_departuretime;
				$s = $flightS->actualdeparturetime;
			}
			
			else{
				$d = $flightD->filed_departuretime;
				$s = $flightS->filed_departuretime;
			}
		}
	
		if ( $d == $s) {
			return 0;
		}
		
		return ($d < $s) ? -1 : 1;
	}
	/*
		Below are gentable and its help functions that will generate html tables to display the fids.
	*/
	function gen_table($group)
	{
	
		if ($group == $this->arrived || $group == $this->enroute || $group == $this->arrival) {
			$this->time_type = "Arrival Time";
			$this->direction = "Origin";
		}
		else{
			$this->time_type = "Departure Time";
			$this->direction = "Destination";
		}
		
	
			switch($group) {

			case $this->departed:
				
				$caption = "DEPARTED";
				
				$this->print_simple_header($caption);
				
				$this->gen_departed_content($group);
				
				break;
	
			case $this->arrived:
				
				$caption = "ARRIVED";
				
				$this->print_header($caption);
			
				$this->gen_arrived_content($group);
			
				break;	
			case $this->scheduled:
			
				$caption = "SCHEDULED";
				
				$this->print_simple_header($caption);
			
				$this->gen_scheduled_content($group);
				break;
			case $this->enroute:
			
				$caption = "ENROUTE";
				
				$this->print_simple_header($caption);
				$this->gen_enroute_content($group);
				break;
			case $this->departure:
				$caption = "DEPARTURE";
				
				$this->print_header($caption);
				
				$this->gen_departure_content($group);
				break;
				
			case $this->arrival:
				$caption = "ARRIVAL";
				
				$this->print_header($caption);
				
				$this->gen_arrival_content($group);
			
				break;	
				break;
		}			
	
		
	}
	
	
		function print_simple_header($caption) {
		
			echo "
				<thead class = 'caption_header'>
				<tr class = 'arrival_departure_caption'><th colspan = '5' >".$caption."</th></tr>
					<tr class = 'header_rows'>
						<th class = 'ident_header' >Ident</th>
						<th class='aircraft_type_header'>Aircraft Type</th>
						<th class = 'origin_header'>".$this->direction."</th>
						<th class = 'arrival_time_header'>".$this->time_type."</th>
						<th class = 'status_header'>Status</th>
					</tr>
				</thead>";
	
		 }
	
	function print_header($caption) {
		
		echo "
			
			<thead class = 'caption_header'>
			<tr class = 'caption_first_row'><th class = 'airport_name_caption'  colspan = '5'>"."<br/>".AIRPORT_FULLNAME."<br/></tr>
			<tr class = 'empty_row'><th class = 'airport_name_caption'  colspan = '5'></th></tr>
			<tr class = 'arrival_departure_caption'><th colspan = '5' >".$caption."</th></tr>
				<tr class = 'header_rows'>
					<th class = 'ident_header' >Ident</th>
					<th class='aircraft_type_header'>Aircraft Type</th>
					<th class = 'origin_header'>".$this->direction."</th>
					<th class = 'arrival_time_header'>".$this->time_type."</th>
					<th class = 'status_header'>Status</th>
				</tr>
			</thead>";
	
	}
	
	
	function gen_arrived_content($group) 
	{	
		$status = "Arrived";
		foreach ($group as $flight) {
			
			$this->print_arrived_content($flight, $status);	

		}
	}
	
	function gen_arrival_content($group) 
	{	
	
		foreach ($group as $flight) {
			
			if (array_key_exists('actualarrivaltime', $flight)) {
				$status = "Arrived";
				$this->print_arrived_content($flight, $status);	
			}
			else {
				$status = "Enroute";
				$this->print_enroute_content($flight, $status);	
			}

		}
	}
	
	function gen_departure_content($group) 
	{	
	
		foreach ($group as $flight) {
			
			if (array_key_exists('actualdeparturetime', $flight)) {
				$status = "Departed";
				$this->print_departed_content($flight, $status);	
			}
			else {
				$status = "Scheduled";
				$this->print_scheduled_content($flight, $status);	
			}

		}
	}
	
	function gen_departed_content($group) 
	{
		$status = "Departed";
		foreach ($group as $flight) {
			
			$this->print_departed_content($flight, $status);	

		}
	}
	
	
	function gen_scheduled_content($group) 
	{
		$status = "Scheduled";
		foreach ($group as $flight) {
			
			$this->print_scheduled_content($flight, $status);	

		}
	}
	
	function gen_enroute_content($group) 
	{
		$status = "Enroute";
		foreach ($group as $flight) {
			
			$this->print_enroute_content($flight, $status);	

		}
	}
	
	function print_arrived_content($flight, $status) {
		
		
		echo "<tr class = 'content_rows'>
			
			<td class = 'ident'>".$flight->ident."</td>
			<td class = 'aircrafttype'>".$flight->aircrafttype."</td>
			<td class= 'origin'>".$flight->origin."</td>
			<td class = 'arrival_departure_time'>".$this->time_convert($flight->actualarrivaltime)."</td>
			<td class = 'status'>".$status."</td>
	
			</tr>";
	
	}
	
	function print_departed_content($flight, $status) {
		
		
		echo "<tr class = 'content_rows'>
	
			<td class = 'ident'>".$flight->ident."</td>
			<td class = 'aircrafttype'>".$flight->aircrafttype."</td>
			<td class= 'origin'>".$flight->destination."</td>
			<td class = 'arrival_departure_time'>".$this->time_convert($flight->actualdeparturetime)."</td>
			<td class = 'status'>".$status."</td>
	
			</tr>";
	
	}
	
		function print_scheduled_content($flight, $status) {
			
			if ($flight->filed_departuretime < (time() - DELAY_THRESHHOLD)) {
				$status = "Delayed";
			}
		
			echo "<tr class = 'content_rows'>
	
				<td class = 'ident'>".$flight->ident."</td>
				<td class = 'aircrafttype'>".$flight->aircrafttype."</td>
				<td class= 'origin'>".$flight->destination."</td>
				<td class = 'arrival_departure_time'>".$this->time_convert($flight->filed_departuretime)."</td>
				<td class = 'status'>".$status."</td>
	
				</tr>";
	
	}
	
	function print_enroute_content($flight, $status) {
	
		if ($flight-> actualdeparturetime == '0') {
			if ($flight->filed_departuretime < (time() - DELAY_THRESHHOLD)) {
				$status = "Delayed";
			}
			
			}
			else if ($flight->estimatedarrivaltime < (time() - DELAY_THRESHHOLD)){
			$status = "Delayed";
			}
			else{
				$status = "Scheduled";
				}
		
		echo "<tr class = 'content_rows'>
	
			<td class = 'ident'>".$flight->ident."</td>
			<td class = 'aircrafttype'>".$flight->aircrafttype."</td>
			<td class= 'origin'>".$flight->origin."</td>
			<td class = 'arrival_departure_time'>".$this->time_convert($flight->estimatedarrivaltime)."</td>
			<td class = 'status'>".$status."</td>
	
			</tr>";
	}
	
	/*
		This will convert the epoch time to local time;
	*/
	function time_convert($epoch) 
	{
		$tomorrow = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
		$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
		if ( $epoch >= $tomorrow || $epoch < $yesterday) {
			
			return date("H:i m-d", $epoch);
		}
		return date("H:i", $epoch);
	}
	
	/*
		Calculate the next refresh inteval;
	*/
	
	function cal_refresh_interval() {
		$next_arr_delay = $this->cal_next_arrival_delay();
		$next_dep_delay = $this->cal_next_departure_delay();
		$next_dep = $this->cal_next_departure();
		$next_arr = $this->cal_next_arrival();
		$groups = array($next_arr_delay, $next_dep_delay, $next_dep, $next_arr);
		foreach ($groups as $key => $group) {
			if ($group == null) {
				unset($groups[$key]);
			}
		}
		
		return $this->range_check(min($groups));
	}
	
	/*
		Help function for cal_refresh_interval to make 
		sure the estimated interval of change is within
		the customized range.
	*/
	function range_check($estimate_val) {
		
		if ($estimate_val < MAX_REFRESH_INTERVAL) {
			if ($estimate_val < MIN_REFRESH_INTERVAL){
				return MIN_REFRESH_INTERVAL;
			}
			else {
				return $estimate_val;
			}
		}
		else {
				return MAX_REFRESH_INTERVAL;
		}
		
	}
/*
	Help function for cal_refresh_interval that
	will estimate the next change from scheduled
	to departed.
*/
function cal_next_departure() {

	$min = mktime(0, 0, 0, date("m")  , date("d")+10, date("Y"));
	$all_delay = true;
	foreach ($this->scheduled as $flight) {
		if ($flight->filed_departuretime > time()){
			$all_delay = false;
			if ($flight->filed_departuretime < $min) {
				$min = $flight->filed_departuretime;
			}
		}
		
	}
	if ($all_delay == true) {
		return null;
	}
	else {
	 return ($min - time());
	}
}
/*
	Help function for cal_refresh_interval that
	will estimate the next change from enroute
	to arrived.
*/
function cal_next_arrival() {

	$min = mktime(0, 0, 0, date("m")  , date("d")+10, date("Y"));
	$all_delay = true;
	foreach ($this->enroute as $flight) {
		if ($flight->estimatedarrivaltime > time()){
			
			$all_delay = false;
			
			if ($flight->estimatedarrivaltime < $min) {
				$min = $flight->estimatedarrivaltime;
			}
		}
		
	}
	
	if ($all_delay == true) {
		return null;
	}
	else {
	 return ($min - time());
	}
}

/*
	Help function for cal_refresh_interval that
	will estimate the next change from enroute
	to delayed
*/
function cal_next_arrival_delay() {
	$min = mktime(0, 0, 0, date("m")  , date("d")+10, date("Y"));
	$all_pass = true;
	foreach ($this->enroute as $flight) {
		if ($flight->estimatedarrivaltime < time()){
			$all_pass = false;
			$delay_period = time() - $flight->estimatedarrivaltime;
			if ($delay_period < DELAY_THRESHHOLD){
				$diff = DELAY_THRESHHOLD - $delay_period;
			}
			else{
				continue;
			}
			
			if ($diff < $min) {
				$min = $diff;
			}
		}
	}
	if ($all_pass == true) {
		return null;
	}
	else {
	 return $min;
	}

}

/*
	Help function for cal_refresh_interval that
	will estimate the next change from scheduled
	to delayed
*/
function cal_next_departure_delay() {
	
	$min = mktime(0,0,0, date("m"), date("d")+10, date("Y"));
	$all_pass = true;
	foreach ($this->scheduled as $flight) {
		if ($flight->filed_departuretime < time()) {
			$all_pass = false;
			$delay_period = time() - $flight->filed_departuretime;
			if ($delay_period < DELAY_THRESHHOLD){
				$diff = DELAY_THRESHHOLD - $delay_period;
			}
			else{
				continue;
			}
			if ($diff < $min) {
				
				$min = $diff;
				
			}
		}
	}
	if ($all_pass == true) {
		return null;
	}
	else {
	
	 return $min;
	}
}

	
}


?>