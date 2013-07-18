
<?php
date_default_timezone_set(timezone_name_from_abbr(TIMEZONE));
class table_class
{


	
	public $direction;
	public $time_type;
	public $arrival;
	public $departure;
	public $arrived;
	public $departed;
	public $scheduled;
	public $enroute;
	
	function __construct() {
	
		$data = unserialize(file_get_contents('flightdata.xml'));
		
		extract($data);
		$this->arrived = $arrived;
		$this->enroute = $enroute;
		$this->departed = $departed;
		$this->scheduled = $scheduled;
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
			
			if ($flight->filed_departuretime < time()) {
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
			if ($flight->filed_departuretime < time()) {
				$status = "Delayed";
			}
			else{
				$status = "Scheduled";
				}
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
		 
		if ( $epoch >= (time()- time()%86400 + 86400) || $epoch < (time()- time()%86400)) {
			/*
			$dt = new DateTime("@$epoch");
			return ($dt->format('H:i M/d'));
			*/
			return date("H:i m-d", $epoch);
		}
		return date("H:i", $epoch);
	}
	
	function date_time_convert($epoch) 
	{
		return date("Y-m-d H:i:s"); 
	}
	
}


?>