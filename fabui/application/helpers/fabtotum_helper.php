<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
if ( !function_exists('createDefaultSettings'))
{
	/**
	 * 
	 * Create /var/lib/fabui/settings/default_settings.json with default data
	 * 
	 * 
	 */
	function createDefaultSettings()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		$settings = array(
			'color'         	 => array('r'=>255, 'g'=>255, 'b'=>255),
			'safety'        	 => array('door'=>1, 'collision_warning'=>1),
			'switch'        	 => 0,
			'feeder'        	 => array('disengage_offset'=> 2, 'show' => true),
			'milling'       	 => array('layer_offset' => 12),
			'customized_actions' => array('bothy' => 'none', 'bothz' => 'none'),
			'api'                => array('keys' => array()),
			'zprobe'        	 => array('enable'=> 0, 'zmax'=> 241.5),
			'z_max_offset'       => 241.5,
			'settings_type' 	 => 'default',
			'hardware'     	 	 => array(
				'head'   => 'printing_head_v2', 
				'feeder' => 'built_in_feeder', 
				'camera' => array('version' => 'camera_v1', 'available' => false),
				'bed'    => array('enable'=> true)
			),
			'print'         	 => array('pre_heating' => array('nozzle' => 150, 'bed'=>50), 'calibration' => 'homing'),
			'stored_position'	 => array(),
			'custom'             => array(
				'overrides' => '',
				'invert_x_endstop_logic' =>false
			),
			'filament' 			 => array('type'=>'pla', 'inserted' => false)
		);
		write_file($CI->config->item('settings'), json_encode($settings, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
	}	
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadSettings'))
{
	/**
	 * 
	 * 
	 *  Load settings configuration
	 *  @return settings configuration
	 * 
	 */
	function loadSettings()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$settings = json_decode(file_get_contents($CI->config->item('settings')), true);
		return $settings;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadGCodeInfo'))
{
	/**
	 * 
	 * 
	 *  Load GCode descriptions
	 *  @return GCode info
	 * 
	 */
	function loadGCodeInfo()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$info = json_decode(file_get_contents($CI->config->item('settings_path') . 'gcodes.json'), true);
		//~ $info = json_decode(file_get_contents('/usr/share/fabui/settings/gcodes.json'), true);
		return $info;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('saveSettings'))
{
	/**
	 * 
	 * @param $data => data to save
	 * @param $type => wich settings to save
	 * 
	 * 
	 */
	function saveSettings($data)
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$result = write_file($CI->config->item('settings'), json_encode($data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
		
		xmlrpcReloadConfig();
		
		return $result;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('savePosition'))
{
	/**
	 * 
	 * @param $x, $y, $z => head position
	 * @param $task_type => task type to save the position to
	 * 
	 * 
	 */
	function savePosition($x, $y, $z, $task_type)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$data = loadSettings();
		
		if( !array_key_exists('stored_position', $data) )
		{
			$data['stored_position'] = array();
		}
		else
		{
			if( !array_key_exists($task_type,$data['stored_position']) )
			{
				$old_x = $data['stored_position']['x'];
				$old_y = $data['stored_position']['y'];
				$old_z = $data['stored_position']['z'];
				
				// Preserve previous values if they have not been set
				if($x == 'undefined' and $old_x != 'x')
					$x = $old_x;
				if($y == 'undefined' and $old_y != 'y')
					$y = $old_y;
				if($z == 'undefined' and $old_z != 'z')
					$z = $old_z;
			}
			
		}
		
		$data['stored_position'][$task_type] = array('x' => $x, 'y' => $y, 'z' => $z);
		
		saveSettings($data);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadPosition'))
{
	function loadPosition($task_type = '')
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$data = loadSettings();
		
		if( !array_key_exists('stored_position', $data) )
			return array("x" => "undefined", "y" => "undefined", "z" => "undefined");
			
		if($task_type == '')
			return $data['stored_position'];
			
		if( array_key_exists($task_type, $data['stored_position']) )
			return $data['stored_position'][$task_type];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getCameraVersion'))
{
	/**
	 * 
	 * 
	 *  Get Camera version
	 *  @return v1 or v2
	 * 
	 */
	function getCameraVersion()
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		return $CI->config->item('camera_version');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadHeads'))
{
	/**
	 *
	 *
	 *  Load settings configuration
	 *  @return settings configuration
	 *
	 */
	function loadHeads()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		#$settings = json_decode(file_get_contents($CI->config->item($type.'_settings')), true);
		$heads_dir = $CI->config->item('heads');
		
		$heads_files = array();
		foreach (glob($heads_dir.'/*.json') as $zip) {
			$info = get_file_info($zip);
			$heads_files [] = $info['name'];
		}
		$heads = array();
		$constants = get_defined_constants(true);
		$json_errors = array();
		foreach ($constants["json"] as $name => $value) {
			if (!strncmp($name, "JSON_ERROR_", 11)) {
				$json_errors[$value] = $name;
			}
		}
		foreach($heads_files as $head)
		{
			$head_file = $heads_dir . '/' . $head;
			$key = basename($head_file, '.json');
			$content = file_get_contents($head_file);
			// UTF-8 safety
			$content = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($content));
			$heads[$key] = json_decode($content , true);
		}

		return $heads;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('loadFeeders'))
{
	/**
	 *
	 *
	 *  Load settings configuration
	 *  @return settings configuration
	 *
	 */
	function loadFeeders()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		#$settings = json_decode(file_get_contents($CI->config->item($type.'_settings')), true);
		$feeders_dir = $CI->config->item('feeders');
		
		$feeders_files = array();
		foreach (glob($feeders_dir.'/*.json') as $json) {
			$info = get_file_info($json);
			$feeders_files [] = $info['name'];
		}
		$feeders = array();
		$constants = get_defined_constants(true);
		$json_errors = array();
		foreach ($constants["json"] as $name => $value) {
			if (!strncmp($name, "JSON_ERROR_", 11)) {
				$json_errors[$value] = $name;
			}
		}
		foreach($feeders_files as $feeder)
		{
			$feeder_file = $feeders_dir . '/' . $feeder;
			$key = basename($feeder_file, '.json');
			$content = file_get_contents($feeder_file);
			// UTF-8 safety
			$content = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($content));
			$feeders[$key] = json_decode($content , true);
		}
		
		if( canHeadSupport('feeder') )
		{
			$head = getInstalledHeadInfo();
			$_data = loadSettings();
			$key = $_data['hardware']['head'];
			$info = $head['feeder'];
			$info['name'] = $head['name'];
			$info['description'] = $head['description'];
			$info['link'] = $head['link'];
			$fw_id = (int)$head['fw_id'];
			if($fw_id){
				$info['factory'] = 1;
			}
			$feeders[$key] = $info;
		}

		return $feeders;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('removeHeadInfo'))
{
	function removeHeadInfo($head_filename)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$heads_dir = $CI->config->item('heads');
		
		$fn = $heads_dir.'/'.$head_filename.'.json';
		
		if( file_exists($fn) )
		{
			unlink($fn);
			return true;
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('removeFeederInfo'))
{
	function removeFeederInfo($feeder_filename)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$feeders_dir = $CI->config->item('feeders');
		
		$fn = $feeders_dir.'/'.$feeder_filename.'.json';
		
		if( file_exists($fn) )
		{
			unlink($fn);
			return true;
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('saveHeadInfo'))
{
	function saveHeadInfo($info, $head_name)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$heads_dir = $CI->config->item('heads');
		
		$fn = $heads_dir.'/'.$head_name.'.json';
		
		$content = json_encode($info, JSON_PRETTY_PRINT);
		return file_put_contents($fn, $content) > 0;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('saveFeederInfo'))
{
	function saveFeederInfo($info, $feeder_name)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$feeders_dir = $CI->config->item('feeders');
		$heads_dir = $CI->config->item('heads');
		
		$fn = $feeders_dir.'/'.$feeder_name.'.json';
		
		if( isFeederInHead($feeder_name) )
		{
			$heads = loadHeads();
			
			unset($info['name']);
			unset($info['description']);
			unset($info['link']);
			
			$heads[$feeder_name]['feeder'] = $info;
			
			saveHeadInfo($heads[$feeder_name], $feeder_name);
		}
		else
		{
			$content = json_encode($info, JSON_PRETTY_PRINT);
			return file_put_contents($fn, $content) > 0;
		}
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('saveInfoToInstalledHead'))
{
	function saveInfoToInstalledHead($info)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$heads_dir = $CI->config->item('heads');
		$_data = loadSettings();
		$head_name = $_data['hardware']['head'];
		return saveHeadInfo($info, $head_name);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('saveInfoToInstalledFeeder'))
{
	function saveInfoToInstalledFeeder($info)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$_data = loadSettings();
		$feeder_name = $_data['hardware']['feeder'];
		return saveFeederInfo($info, $feeder_name);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getInstalledHeadInfo'))
{
	/**
	 * Load installed head information
	 */	
	function getInstalledHeadInfo()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$heads_dir = $CI->config->item('heads');
		
		$_data = loadSettings();
		
		$head_filename =  $heads_dir .'/'. $_data['hardware']['head'] . '.json';
		$info = json_decode(file_get_contents($head_filename), true);
		$fw_id = intval($info['fw_id']);
		if( $fw_id < 100 )
		{
			$info['image_src'] = '/assets/img/head/' . $_data['hardware']['head'] . '.png';
		}
		else
		{
			// @TODO: support for custom images
			$info['image_src'] = '/assets/img/head/head_shape.png';
		}
		
		if(!isset($info['nozzle_offset']))
			$info['nozzle_offset'] = 0;
		
		return $info;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getInstalledFeederInfo'))
{
	/**
	 * Load installed feeder information
	 */	
	function getInstalledFeederInfo()
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		$_data = loadSettings();
		$feeder_name = $_data['hardware']['feeder'];
		
		$feeders = loadFeeders();
		return $feeders[$feeder_name];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('restoreHeadFactorySettings'))
{
	/**
	 * restore factory settings for head
	 */
	function restoreHeadFactorySettings($head_file_name)
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$heads_dir        = $CI->config->item('heads');
		$factory_dir      = $CI->config->item('fabui_path').'heads/';
		if(file_exists($factory_dir.$head_file_name.'.json')){
			$factory_settings = json_decode(file_get_contents($factory_dir.$head_file_name.'.json'), true);
			return saveHeadInfo($factory_settings, $head_file_name);
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('restoreFeederFactorySettings'))
{
	/**
	 * restore factory settings for head
	 */
	function restoreFeederFactorySettings($feeder_file_name)
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		$heads_dir        = $CI->config->item('feeders');
		$factory_dir      = $CI->config->item('fabui_path').'feeders/';
		if(file_exists($factory_dir.$feeder_file_name.'.json')){
			$factory_settings = json_decode(file_get_contents($factory_dir.$feeder_file_name.'.json'), true);
			return saveFeederInfo($factory_settings, $feeder_file_name);
		}
		return false;
	}
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('safetyCheck'))
{
	/**
	 * Check whether the installed head supports a specific feature and bed is flipped to heater side
	 * @param feature Feature to look for (print, mill, laser...)
	 * @param heated_bed true|false
	 */
	function safetyCheck($feature, $heated_bed)
	{
		
		$head_in_place = isHeadInPlace();
		$bed_enabled   = isBedEnabled();
		
		$result = array(
			'head_is_ok'    => false,
			'head_info'     => getInstalledHeadInfo(),
			'head_in_place' => $head_in_place,
			'bed_enabled'   => $bed_enabled
		);
		
		$result['head_is_ok'] = $result['all_is_ok'] = canHeadSupport($feature) && $head_in_place;
		
		if($bed_enabled){
			$bed_in_place  = isBedInPlace();
			$result['bed_in_place'] = $bed_in_place;
			$result['bed_is_ok'] = $heated_bed == $bed_in_place;
			$result['all_is_ok']  = $result['head_is_ok'] && $result['bed_is_ok'];
		}
		
		/*
		$result = array(	
			'head_is_ok'    => false,
			'head_info'     => getInstalledHeadInfo(),
			'head_in_place' => $head_in_place,
			'bed_is_ok'     => false,
			'bed_in_place'  => $bed_in_place,
			'bed_enabled'   => isBedEnabled()
		);*/
		
		return $result;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('canHeadSupport'))
{
	/**
	 * Check whether the installed head supports a specific feature
	 * @param feature Feature to look for (print, mill, laser...)
	 */
	function canHeadSupport($feature)
	{
		$data = getInstalledHeadInfo();
		
		if(isset($data['capabilities']))
		{
			return in_array($feature, $data['capabilities']);
		}
		
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isFeederInHead'))
{
	/**
	 * Check whether the feeder is part of the head.
	 */
	function isFeederInHead($feeder_name)
	{
		$heads = loadHeads();
		return array_key_exists($feeder_name, $heads);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isHeadInPlace'))
{
	function isHeadInPlace()
	{
		/**
		 * check if head in properly inserted
		 * @return boolean
		 * _0 is for check timestamp with index 0
		 * more details JogFactory -> buildCommands 
		 */
		$timestamp = time().rand();
		$reply = doGCode(array('M745'), $timestamp);
		if( isset($reply['commands'][$timestamp.'_0']))
		{
			/*
			foreach($reply['commands'] as $value)
			{
				//~ $join = join('-', $value['reply']);
				//~ return $join == "TRIGGERED-ok";
				return $value['reply'][0] == "TRIGGERED";
			}*/
			return $reply['commands'][$timestamp.'_0']['reply'][0] == "TRIGGERED";
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isBedinPlace'))
{
	/**
	 * check if bed is inserted
	 * @return boolean
	 * _0 is for check timestamp with index 0
	 * more details JogFactory -> buildCommands
	 */
	function isBedInPlace()
	{
		$timestamp = time().rand();
		$reply     = doGCode(array('M744'), $timestamp);
		
		if( isset($reply['commands'][$timestamp.'_0']))
		{
			/*
			foreach($reply['commands'] as $value)
			{
				//~ $join = join('-', $value['reply']);
				//~ return $join == "TRIGGERED-ok";
				return $value['reply'][0] == "TRIGGERED";
			}*/
			return $reply['commands'][$timestamp.'_0']['reply'][0] == "TRIGGERED";
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('isBedEnabled'))
{
	/***
	 * return if the bed is enabled
	 * default is true and always it will be
	 */
	function isBedEnabled()
	{
		$data = loadSettings();
		if(!isset($data['hardware']['bed'])) return true;
		return $data['hardware']['bed']['enable'];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('canFeederBeEngaged'))
{
	function canFeederBeEngaged()
	{
		$_data = loadSettings();
		$settings_type = $_data['settings_type'];
		if (isset($_data['settings_type']) && $_data['settings_type'] == 'custom') {
			$_data = loadSettings( "custom" );
		}
		return $_data['feeder']['show'];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('doCommandLine'))
{
	/**
	 * Execute script from command line
	 * @param bin Script filename
	 * @param scriptPath Directory the script is located in
	 * @param args Arguments
	 * @param background Run script in the background an return control
	 */
	function doCommandLine($bin, $scriptPath, $args = '', $background = false)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper('utility_helper');
		
		$command = $bin.' '.$scriptPath.' ';
		
		if(is_array($args) && $args != ''){
			if(is_array($args)){
				foreach($args as $key => $value){
					// if key exists and is not an array's index	
					if(array_key_exists($key, $args) && $key != '' && !is_int($key)){
						$command .= $key.' ';
					}
					if($value != '') {
						if(is_int($value)) $command .= ' '.$value.' ';
						else $command .= ' "'.$value.'" ';
					}
				}
			}else{
				foreach($args as $arg){
					$command .= $arg.' ';
				}
			}
		}
		else
		{
			$command .= $args;
		}
		
		shell_exec("echo '".$command."' > /tmp/fabui/doCommandLine");
		
		/* Note to myself: DO NOT PLAY WITH THESE COMMANDS !!!!! */
		if($background) 
			$command .= ' &> /tmp/fabui/doCommandLine.log &';
		else
			$command .= ' | tee /tmp/fabui/doCommandLine.log';
		/* Note to myself: DO NOT PLAY WITH THESE COMMANDS !!!!! */
		
		log_message('debug', $command);
		return shell_exec($command);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('sendToXmlrpcServer'))
{
	function sendToXmlrpcServer($method, $data = array())
	{
		$CI =& get_instance(); //init ci instance
		$CI->config->load('fabtotum');
		$CI->load->library('xmlrpc');
		
		$CI->xmlrpc->server('127.0.0.1/FABUI', $CI->config->item('xmlrpc_port'));
		$CI->xmlrpc->method($method);
		$CI->xmlrpc->timeout(120*5);
		
		if(!is_array($data)) $data = array($data);
		
		$CI->xmlrpc->request( $data );
		
		$response = false;
		$reply    = '';
		$message = '';
		
		$trace = '';
		
		if ( !$CI->xmlrpc->send_request())
		{
			$reply    = $CI->xmlrpc->display_error();
			$response = False;
			$message    = 'request had an error: '.$CI->xmlrpc->display_error();
		}
		else
		{	
			if(is_array($CI->xmlrpc->display_response())){
				$reply = $CI->xmlrpc->display_response();
				$response = True;
			}else {
				$tmp = json_decode( $CI->xmlrpc->display_response(), true );
				$trace = $CI->xmlrpc->display_response();
				if(json_last_error()){
					$reply = $CI->xmlrpc->display_response();
					$response = 'error';
					$message = json_last_error_msg();
				}else{
					if($tmp['response'] == 'success')
					{
						$response = True;
					}
					$response = $tmp['response'];
					$reply   = $tmp['reply'];
					$message = $tmp['message'];
				}
			}
		}
		return array('response' => $response, 'reply' => $reply, 'message' => $message);
		
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('doMacro'))
{
	function doMacro($macroName, $traceFile = '', $extrArgs = '')
	{	
		$CI =& get_instance(); //init ci instance
		
		if($traceFile == '' or $traceFile == null){
			
			$CI->config->load('fabtotum');
			$traceFile = $CI->config->item('trace');
		}
		
		if( !is_array($extrArgs) )
		{
			$extrArgs = array($extrArgs);
		}
		
		$CI->load->helper('language_helper');
		$language_code = getCurrentLanguage().'.UTF-8';
		
		$data = array( array($macroName, 'string'),
				array($extrArgs, 'array'),
				array(true, 'boolean'),
				array($language_code, 'string')
		);
		
		log_message('debug', "do_macro: ".$macroName);
		$serverResponse = sendToXmlrpcServer('do_macro', $data);
		
		$serverResponse['lang'] = $language_code ;
		
		if($serverResponse['response'] != 'success'){
			$serverResponse['trace'] = $serverResponse['message'];
		}else{
			$serverResponse['trace'] = file_get_contents($traceFile);
		}
		return $serverResponse;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('doGCode'))
{
	/**
	 * @param $gcodes GCode command or an array of GCode commands
	 * Send gcode commands using JogFactory
	 */
	function doGCode($gcodes, $timestamp = '')
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->load->library('JogFactory', '', 'jogFactory');
		$CI->jogFactory->sendCommands( $gcodes, $timestamp);
		return $CI->jogFactory->response();
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('resetController'))
{
	/**
	 * Reset controller board
	 */
	function resetController()
	{
		return sendToXmlrpcServer('do_reset');
		//writeToCommandFile('!reset');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('emergency'))
{
	/**
	 * Stop all running tasks and scripts and restart main scripts
	 */
	function emergency()
	{
		doCommandLine('sudo /etc/init.d/fabui', 'emergency');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('restart'))
{
	/**
	 * Restart main daemon's scripts
	 */
	function restart()
	{
		doCommandLine('sudo /etc/init.d/fabui', 'restart');
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('stopServices'))
{
	/**
	 * Stop main daemons
	 */
	function stopServices()
	{
		doCommandLine('sudo /etc/init.d/fabui', 'stop');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startServices'))
{
	/**
	 * Start main daemons
	 */
	function startServices()
	{
		doCommandLine('sudo /etc/init.d/fabui', 'start');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('readInitialTemperatures')) 
{
	/**
	 * @param $file
	 * @param $numLines
	 * get the initial temperatures of an additive file
	 */
	function readInitialTemperatures($file, $numLines = 500){
		
		if(file_exists($file)){
			//$re = "\"M(\d+)\sS([+|-]*[0-9]*.[0-9]*)\""; //regular expression to catch temperatures
			$extruderGCodes = array('M109');
			$bedGCodes      = array('M190');
			$extruderTemp = 1;
			$bedTemp      = 1;
			//read first $numLines lines of the file
			$lines = explode(PHP_EOL, doCommandLine('head', '"'.$file.'"', array('-n' => $numLines)));
			foreach($lines as $line){
				$tags = explode(' ', $line);
				if( in_array($tags[0], $extruderGCodes) )
				{
					foreach($tags as $tag)
					{
						if($tag[0] == 'S')
						{
							$extruderTemp = intval(substr($tag,1));
							break;
						}
					}
				}
				if( in_array($tags[0], $bedGCodes) )
				{
					foreach($tags as $tag)
					{
						if($tag[0] == 'S')
						{
							$bedTemp = intval(substr($tag,1));
							break;
						}
					}
				}
				if($bedTemp > 1 && $extruderTemp > 1) break;
			}
			return array('extruder' => intval($extruderTemp), 'bed' => intval($bedTemp));
		}else 
			return false;
	}
	
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('checkManufactoring'))
{
	/**
	 * @param (string) $file_path
	 * @param (int) $numLines, number of lines to read
	 * retunr what type of manufactoring file is
	 */
	function checkManufactoring($filePath, $numLines = 100)
	{
		$args = array(
			'-f' => $filePath,
			'-n' => $numLines
		);
		return trim(startPyScript('check_manufactoring.py', $args, false, true));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('writeToCommandFile'))
{
	/**
	 * write to command to command file
	 */
	function writeToCommandFile($command)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$CI->load->helper('file');
		write_file($CI->config->item('command'), $command);
		log_message('debug', "Command: ".$command);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('abort'))
{
	/**
	 * abort task
	 */
	function abort()
	{
		return sendToXmlrpcServer('do_abort');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('xmlrpcReloadConfig'))
{
	/**
	 * Reload XML-RPC server configuration
	 */
	function xmlrpcReloadConfig()
	{
		return sendToXmlrpcServer('reload_config');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('pause'))
{
	/**
	 * pause task
	 */
	function pause()
	{
		return sendToXmlrpcServer('do_pause');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('resume'))
{
	/**
	 * resume task
	 */
	function resume()
	{
		return sendToXmlrpcServer('do_resume');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('flowRate'))
{
	/**
	 * resume task
	 */
	function flowRate($value)
	{
		return sendToXmlrpcServer('set_flow_rate', $value);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('speed'))
{
	/**
	 * set speed override
	 */
	function speed($value)
	{
		return sendToXmlrpcServer('set_speed', array($value));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('zHeight'))
{
	/**
	 * set z height override
	 */
	function zHeight($value)
	{
		//$sign = substr($value, 0,1);
		//$value = str_replace($sign, '' , $value);
		//$command = $sign == '-' ? '!z_minus' : '!z_plus';
		//return sendToXmlrpcServer('set_z_modify', $sign.$value);
		return sendToXmlrpcServer('set_z_modify', $value);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('fan'))
{
	/**
	 * set fan override
	 */
	function fan($value, $percent = true)
	{
		if($percent){
			$value = (($value/100)*255);
		}
		return sendToXmlrpcServer('set_fan', $value);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('rpm'))
{
	/**
	 * set rpm override
	 */
	function rpm($value)
	{
		return sendToXmlrpcServer('set_rpm', array($value));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('pwm_laser'))
{
	/**
	 * set pwm override
	 */
	function pwm_laser($value)
	{
		return sendToXmlrpcServer('set_laser', array($value));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('pwm'))
{
	/**
	 * set pwm override
	 */
	function pwm($value)
	{
		return sendToXmlrpcServer('set_pwm', array($value));
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('clearJogResponse'))
{
	/**
	 * send command to clear jog response
	 */
	function clearJogResponse()
	{
		//writeToCommandFile('!jog_clear');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startPyScript'))
{
	/**
	 * start python task
	 */
	function startPyScript($script, $params = '', $background = true, $sudo = false)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$extPath = $CI->config->item('ext_path');
		// TODO: check trailing /
		$cmd = 'python';
		if($sudo)
			$cmd = 'sudo ' . $cmd;
		return doCommandLine($cmd, $extPath.'py/'.$script, $params, $background);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('startBashScript'))
{
	/**
	 * start bash script
	 */
	function startBashScript($script, $params = '', $background = true, $sudo = false)
	{
		$CI =& get_instance();
		$CI->config->load('fabtotum');
		$extPath = $CI->config->item('ext_path');
		// TODO: check trailing /
		$cmd = 'sh';
		if($sudo)
			$cmd = 'sudo ' . $cmd;
		return doCommandLine($cmd, $extPath.'bash/'.$script, $params, $background);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('resetTaskMonitor'))
{
	/**
	 * reset task monitor values
	 */
	function resetTaskMonitor($resetArray = array())
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		//it must exists, but if not
		if(!file_exists($CI->config->item('task_monitor'))) write_file($CI->config->item('task_monitor'), json_encode(array()));
		
		$monitor = json_decode(file_get_contents($CI->config->item('task_monitor')), true); 
		
		if(!is_array($monitor)) $monitor = array();
		
		//override keys value
		$default_monitor['override']['z_override'] = 0;
		$default_monitor['override']['laser']      = 0;
		$default_monitor['override']['speed']      = 100;
		$default_monitor['override']['flow_rate']  = 100;
		$default_monitor['override']['fan']        = 0;
		$default_monitor['override']['rpm']        = 0;
		//task keys value
		$default_monitor['task']['status']         = '';
		$default_monitor['task']['pid']            = '';
		$default_monitor['task']['completed_time'] = 0;
		$default_monitor['task']['estimated_time'] = 0;
		$default_monitor['task']['started_time']   = 0;
		$default_monitor['task']['duration']       = 0;
		$default_monitor['task']['controller']     = '';
		$default_monitor['task']['id']             = '';
		$default_monitor['task']['type']           = '';
		$default_monitor['task']['percent']        = 0;
		
		$monitor = array_replace_recursive ($monitor, $default_monitor, $resetArray);
		write_file($CI->config->item('task_monitor'), json_encode($monitor));
	} 
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('resetTrace'))
{
	/**
	 * reset trace file
	 */
	function resetTrace($content = '')
	{
		$CI =& get_instance();
		$CI->load->helper('file');
		$CI->config->load('fabtotum');
		
		write_file($CI->config->item('trace'), $content);
	} 
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('setRecovery'))
{
	function setRecovery($mode)
	{
		return startBashScript('recovery.sh', $mode, false, true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('firmwareInfo'))
{
	function firmwareInfo()
	{
		$tmp   = doMacro('version');
		if($tmp['response'] == true){
			return $tmp['reply'];
		}
		return false;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('bootFilesInfo'))
{
	function bootFilesInfo()
	{
		$version = 'v20170223';
		
		if(file_exists('/mnt/live/mnt/boot/version'))
		{
			$version = trim(file_get_contents('/mnt/live/mnt/boot/version'));
		}
		
		return $version;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('termsAndConditions'))
{
	/**
	 * 
	 */
	function termsAndConditions()
	{
		$CI =& get_instance();
		$CI->load->helper('language_helper');
		return $CI->load->view('layout/conditions/'.getCurrentLanguage(), null, true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getSerialNumber'))
{
	/**
	 * return printer serial number
	 * you can find it in the back of the unit
	 * @todo
	 */
	function getSerialNumber()
	{ 
		$CI =& get_instance();
		$CI->load->model('Configuration', 'configuration');
		return $CI->configuration->load('serial_number');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getUnitName'))
{
	/**
	 * return unit name
	 * you can find it in the back of the unit
	 * @todo
	 */
	function getUnitName()
	{
		$CI =& get_instance();
		$CI->load->model('Configuration', 'configuration');
		return $CI->configuration->load('unit_name');
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getFilamentDescription'))
{
	/**
	 * 
	 * @todo
	 */
	function getFilamentDescription($filament)
	{
		$CI =& get_instance();
		$CI->load->helper('language_helper');
		return $CI->load->view('layout/filaments/'.$filament.'/'.getCurrentLanguage(), null, true);
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getFilament'))
{
	/**
	 *
	 * @todo
	 */
	function getFilament($filament)
	{
		$CI =& get_instance();
		$CI->config->load('filaments');
		$filaments = $CI->config->item('filaments');
		return $filaments[$filament];
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('setFilament'))
{
	/**
	 *
	 * @todo
	 */
	function setFilament($filament, $inserted)
	{
		$settings = loadSettings();
		$temp = array(
			'type' => $filament,	
			'inserted' => $inserted
		);
		$settings['filament'] = $temp;
		saveSettings($settings);
		
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getTours'))
{
	/**
	 *
	 * @todo
	 */
	function getTours()
	{
		$tours = array();
		
		$dir    = '/var/www/assets/js/tours/';
		$tour_js_files = scandir($dir);

		foreach($tour_js_files as $tour_file)
		{
			$path_info = pathinfo($tour_file);
			if( $path_info['extension'] == 'js' )
			{
				//echo '<script type="text/javascript" src="/assets/js/tours/'.$tour_file.'?v='.FABUI_VERSION.'"></script>'.PHP_EOL;
				
				//var id = $(this).val().split("_").pop().join("_");
				
				$tour_id = $path_info['filename'];
				
				//$tour_id = preg_replace('/^[0-9]+_/', '', $tour_id);
				
				array_push($tours, $tour_id);
			}
		}
		
		return $tours;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('setSecure'))
{
	/**
	 * 
	 */
	function setSecure($mode = true){
		$CI =& get_instance();
		$CI->load->config('fabtotum');
		$CI->load->helper('file');
		$notify = json_decode( file_get_contents( $CI->config->item('notify_file') ), true);
		$notify['last_event']['seen'] = true;
		write_file($CI->config->item('notify_file'), json_encode($notify, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
		return true;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('getAvailableColors'))
{
	/**
	 * 
	 */
	function getAvailableColors()
	{
		return array(
			'white' => _("White"),
			"red"   => _("Red"),
			"black" => _("Black")
		);
	}
	
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!function_exists('colors_menu'))
{
	/**
	 * 
	 */
	function colors_menu($class = '', $name = 'colors', $attributes = '')
	{
		$colors = getAvailableColors();
		$html = '<select class="'.$class.'" name="'.$name.'" '._stringify_attributes($attributes).'>';
		foreach($colors as $key => $label){
			$html .= '<option value="'.$key.'">'.$label.'</option>';
		}
		$html .= '</select>';
		return $html;
	}
}
?>
