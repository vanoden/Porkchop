<?PHP
	#######################################################
	### functions.php									###
	### Standard Functions for the Root Seven			###
	### Technologies Data Tracking Module.				###
	### A. Caravello 11/28/2005							###
	#######################################################

	# Function to Get Information About Events
	function get_events()
	{
		# Global Variables
		global $company_id;
		global $customer_id;
		
		# Get Event Info
		$get_events_query = "
			SELECT	e.event_id,
					e.name,
					e.location,
					date_format(e.date_start,'%c/%e/%Y') date_start,
					s.name status,
					e.units,
					e.alarm_min,
					e.alarm_max,
					e.alarm_total,
					e.first_name,
					e.last_name,
					e.city,
					unix_timestamp(e.datestart) time_offset
			FROM	(monitor.events e,
					cart.customers c)
			LEFT OUTER JOIN
					monitor.event_statii s
			ON		s.status_id = e.status_id
			WHERE	c.organization_id = e.organization_id
			AND		c.customer_id = '$customer_id'
			AND		c.company_id = '$company_id'
			";

		# Execute Query
		$events = mysql_query($get_events_query) or record_error(mysql_errno(),mysql_error(),$get_events_query);

		# Return Results
		return $events;
	}

	# Function to Get Information About a Specific Event
	function get_event($event_id)
	{
		# Global Variables
		global $company_id;
		global $customer_id;
		
		# Get Event Info
		$get_event_query = "
			SELECT	e.event_id,
					e.name,
					e.location,
					date_format(e.date_start,'%c/%e/%Y') date_start,
					s.name status,
					e.units,
					e.alarm_min,
					e.alarm_max,
					e.alarm_total,
					e.custom_1,
					e.custom_2,
					e.custom_3,
					e.custom_4
			FROM	(monitor.events e,
					cart.customers c)
			LEFT OUTER JOIN
					monitor.event_statii s
			ON		e.status_id = s.status_id
			WHERE	e.event_id = '$event_id'
			AND		c.organization_id = e.organization_id
			AND		c.customer_id = '$customer_id'
			AND		c.company_id = '$company_id'
			";

		# Execute Query
		$get_event = mysql_query($get_event_query) or record_error(mysql_errno(),mysql_error(),$get_event_query);

		# Return Results
		return mysql_fetch_array($get_event);
	}

	# Function to Get Event Statii
	function get_event_statii()
	{
		$get_statii_query = "
			SELECT	status_id,
					name
			FROM	monitor.event_statii
			ORDER BY status_id
			";

		# Execute Query
		return exec_query_handle($get_statii_query);
	}

	# Function to Get Event Monitors
	function get_event_monitors($event_id)
	{
		$get_emonitors_query = "
			SELECT	m.monitor_id,
					m.label,
					em.location
			FROM	monitor.monitors m,
					monitor.event_monitors em,
					monitor.events e
			WHERE	m.monitor_id = em.monitor_id
			AND		em.event_id = '$event_id'
			";

		# Execute Query
		$event_monitors = mysql_query($get_emonitors_query) or record_error(mysql_errno(),mysql_error(),$get_emonitors_query);

		# Return Results
		return $event_monitors;
	}

	# Function to Get All Monitors
	function get_monitors()
	{
		# Global Variables
		global $customer_id;

		# Prepare Query
		$get_monitors_query = "
			SELECT	m.monitor_id,
					p.sku label,
					m.serial_number
			FROM	(monitor.monitors m,
					cart.customers c)
			LEFT OUTER JOIN
					product.products p
			ON		m.product_id = p.product_id
			WHERE	m.organization_id = c.organization_id
			AND		c.customer_id = '$customer_id'
			";

		# Execute Query
		$monitors = mysql_query($get_monitors_query) or record_error(mysql_errno(),mysql_error(),$get_monitors_query);

		# Return Results
		return $monitors;
	}

	# Function to Get Specified Monitor
	function get_monitor($monitor_id)
	{
		# Global Variables
		global $customer_id;

		# Prepare Query
		$get_monitor_query = "
			SELECT	m.monitor_id,
					m.label,
					m.points,
					m.serial_number
			FROM	monitor.monitors m,
					cart.customers c
			WHERE	m.organization_id = c.organization_id
			AND		c.customer_id = '$customer_id'
			AND		m.monitor_id = '$monitor_id'
			";

		# Execute Query
		$get_monitor = mysql_query($get_monitor_query) or record_error(mysql_errno(),mysql_error(),$get_monitor_query);

		# Return Results
		return mysql_fetch_array($get_monitor);
	}

	function last_calibrated($monitor_id)
	{
		$get_calibration_query = "
			SELECT	date_format(max(date_request),'%c/%e/%Y')
			FROM	from	monitor.calibrations
			WHERE	monitor_id = '$monitor_id'
		";
		list($last_date) = exec_query_row($get_calibration_query);

		return $last_date;
	}

	# Function to Get Last Known Location of Monitor
	function find_monitor($monitor_id)
	{
		# Global Variables
		global $customer_id;
		
		# Prepare Query
		$get_event_query = "
			SELECT	e.location,
					em.location
			FROM	monitor.monitors m,
					cart.customers c,
					monitor.event_monitors em,
					monitor.events e
			WHERE	m.monitor_id = '$monitor_id'
			AND		m.organization_id = c.organization_id
			AND		em.monitor_id = m.monitor_id
			AND		e.event_id = em.event_id
			AND		c.customer_id = '$customer_id'
			AND		e.organization_id = c.organization_id
			ORDER BY e.date_start DESC
			LIMIT 1
			";
		
		# Execute Query
		$get_event = mysql_query($get_event_query) or record_error(mysql_errno(),mysql_error(),$get_event_query);
		
		# Fetch Results
		list($event,$monitor) = mysql_fetch_row($get_event);
		
		# Return Results
		return $event." - ".$monitor;
	}
	
	# Function to Get Data Points
	function get_data_points($event_id,$monitor_id)
	{
		# Global Vars
		global $company_id;
		
		# Prepare Query
		$get_points_query = "
			SELECT	d.date_point,
					d.value
			FROM	monitor.data d,
					monitor.monitors m
			WHERE	d.event_id = '$event_id'
			AND		m.monitor_id = '$monitor_id'
			AND		m.company_id = '$company_id'
			AND		d.monitor_id = m.monitor_id
			";
		
		# Execute Query
		$points = mysql_query($get_points_query) or record_error(mysql_errno(),mysql_error(),$get_points_query);
		
		# Return Results
		return $points;
	}
?>
