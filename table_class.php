
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
	
	function __construct() {
			
		if (file_exists(FILENAME)) {
		
			if ($this->saved_time > (time() - $this->interval) ){
				$data = unserialize(file_get_contents(FILENAME));
		
				extract($data);
				$this->arrived = $arrived;
				$this->enroute = $enroute;
				$this->departed = $departed;
				$this->scheduled = $scheduled;
				$this->late = false;
				$this->saved_time = $saved_time;
			}
	
		
		}
		$this->refresh_data();
		$this->interval= $this->cal_refresh_interval();
	}
	
	function refresh_data() {
		$options = array(
						'trace' => true,
						'exceptions' => 0,
						'login' => USERNAME,
						'password' => APIKEY,
						);
		$client = new SoapClient('http://flightxml.flightaware.com/soap/FlightXML2/wsdl', $options);

		$params = array("airport" => AIRPORT, "howMany" => NUM_FLIGHTS_DISPLAYED, "filter" => FILTER_PARAM, "offset" => OFFSET );

		$this->arrived = $client->Arrived($params)->ArrivedResult->arrivals;

		$this->departed = $client->Departed($params)->DepartedResult->departures;

		$this->scheduled = $client->Scheduled($params)->ScheduledResult->scheduled;

		$this->enroute = $client->Enroute($params)->EnrouteResult->enroute;

		$groups = array('arrived' =>$this->arrived, 'enroute' =>$this->enroute, 'departed'=>$this->departed, 'scheduled'=>$this->scheduled, 'saved_time' => time());

		$myFile = FILENAME;

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
				<thead class = 'caption&header'>
				<tr class = 'arrival_departure_caption'><th colspan = '5' >".$caption."</th></tr>
					<tr class = 'header_rows'>
						<th class = 'ident_header' >Ident</th>
						<th class='aircraft_type_header'>Aircraft Type</th>
						<th class = 'origin_header'>".$this->direction."</th>
						<th class = 'arrival_time_header'>".$this->time_type."</th>
						<th class = 'status_header'>Status</th>
					</tr>
				<thead>";
	
		 }
	
	function print_header($caption) {
		
		echo "
			
			<thead class = 'caption&header'>
			<tr class = 'caption_first_row'><th class = 'airport_name_caption'  colspan = '5'>"."<br/>".AIRPORT_FULLNAME."<br/></tr>
			<tr class = 'empty_row'><th class = 'airport_name_caption'  colspan = '5'></tr>
			<tr class = 'arrival_departure_caption'><th colspan = '5' >".$caption."</th></tr>";
				if (DIS_TIME == '1') {
				if ($caption == "ARRIVAL" || $caption == "DEPARTURE") {
					echo "<tr class = 'current_time_row'><th colspan = '5'>".$this->date_time_convert(time())."</th></tr>";
				}
				}
				echo "
				<tr class = 'header_rows'>
					<th class = 'ident_header' >Ident</th>
					<th class='aircraft_type_header'>Aircraft Type</th>
					<th class = 'origin_header'>".$this->direction."</th>
					<th class = 'arrival_time_header'>".$this->time_type."</th>
					<th class = 'status_header'>Status</th>
				</tr>
			<thead>";
	
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
	
	
	function time_convert($epoch) 
	{
		$tomorrow = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
		$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
		if ( $epoch >= $tomorrow || $epoch < $yesterday) {
			
			return date("H:i m-d", $epoch);
		}
		return date("H:i", $epoch);
	}
	
	function date_time_convert($epoch) 
	{
		return date("Y-m-d H:i:s"); 
	}
	
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
	
	
	function range_check($min_val) {
		
		if ($min_val < MAX_REFRESH_INTERVAL) {
			if ($min_val < MIN_REFRESH_INTERVAL){
				return MIN_REFRESH_INTERVAL;
			}
			else {
				return $min_val;
			}
		}
		else {
				return MAX_REFRESH_INTERVAL;
		}
		
	}

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