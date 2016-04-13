<?php
require_once '/var/www/lib/config.php';
require_once '/var/www/lib/database.php';
require_once '/var/www/lib/utilities.php';
//require_once '/var/www/lib/log4php/Logger.php';

/** GET ARGS FROM COMMAND LINE */
$_task_id = $argv[1];
$_type = $argv[2];
$_status = isset($argv[3]) && $argv[3] != '' ? $argv[3] : 'performed';

switch($_type) {

	case 'print' :
		finalize_print($_task_id, $_status);
		break;
	case 'mill' :
		finalize_mill($_task_id, $_status);
		break;
	case 'slice' :
		finalize_slice($_task_id, $_status);
		break;
	case 'mesh' :
		finalize_mesh($_task_id, $_status);
		break;
	case 'self_test' :
		finalize_self_test($_task_id, $_status);
		break;
	case 'update_fw' :
		finalize_update_fw($_task_id, $_status);
		break;
	case 'update_software' :
		finalize_update_sw($_task_id, $_status);
		break;
	case 'scan_r' :
	case 'scan_p' :
	case 'scan_s' :
	case 'scan_pg' :
	case 'scan' :
		finalize_scan($_task_id, $_type, $_status);
		break;
	default :
		finalize_general($_task_id, $_type, $_status);
}

//$log->info('=====================================================');

/** UPDATE TASK ON DB
 *
 * @param $tid: TASK ID
 * @param $status - TASK STATUS (STOPPED - PERFORMED)
 *
 ***/
function update_task($tid, $status, $attributes = '') {
	//global $log;

	//LOAD DB
	$db = new Database();

	$_data_update = array();
	$_data_update['status'] = $status;
	$_data_update['finish_date'] = 'now()';

	if ($attributes != '') {

		$json_attributes = json_decode($attributes, true);

		if (isset($json_attributes['monitor']) && $json_attributes['monitor'] != '' && file_exists($json_attributes['monitor'])) {
			$json_attributes['monitor'] = json_decode(file_get_contents($json_attributes['monitor']), true);
		}

		$_data_update['attributes'] = json_encode($json_attributes);
	}

	$db -> update('sys_tasks', array('column' => 'id', 'value' => $tid, 'sign' => '='), $_data_update);
	$db -> close();
	shell_exec('sudo php ' . SCRIPT_PATH . '/notifications.php &');

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/** FINALIZE PRINT TASK
 *
 * @param $tid - TASK ID
 * @param $status - TASK STATUS (STOPPED - PERFORMED)
 *
 **/
function finalize_print($tid, $status) {

	//LOAD DB
	$db = new Database();
	//GET TASK
	$task = $db -> query('select * from sys_tasks where id=' . $tid);
	$task = $task[0];

	//GET TASK ATTRIBUTES
	$attributes = json_decode(file_get_contents($task['attributes']), TRUE);

	$print_type = $attributes['print_type'];

	$reset = false;

	//CHECK IF TASK WAS ALREARDY FINALIZED
	if ($task['status'] == 'stopped' || $task['status'] == 'performed') {
		return;
	}

	if ($status == 'stopped') {
		//echo "STOPPING PROCESS...".$attributes['pid'].PHP_EOL;
		//shell_exec('sudo kill ' . $attributes['pid']);
	}

	//if ($status == 'stopped' && $print_type == 'additive') {

	//IF % PROGRESS IS < 0.5 FOR SECURITY REASON I RESET THE BOARD CONTROLLER
	$monitor = json_decode(file_get_contents($attributes['monitor']), TRUE);
	$percent = $monitor['print']['stats']['percent'];

	if ($percent < 0.2) {

		$_command = 'sudo python ' . PYTHON_PATH . 'force_reset.py';
		shell_exec($_command);
		$reset = true;
		//$log->info('Task #'.$tid.' reset controller');
	}

	//}
	$pid = intval(trim(str_replace('\n', '', shell_exec('cat /run/create.pid'))));
	shell_exec('kill -9 ' . $pid);

	//UPDATE TASK
	update_task($tid, $status, file_get_contents($task['attributes']));

	$_macro_end_print_response = TEMP_PATH . 'macro_response';
	$_macro_end_print_trace = TEMP_PATH . 'macro_trace';

	$end_macro = 'end_print_additive';

	write_file($_macro_end_print_trace, '', 'w');
	chmod($_macro_end_print_trace, 0777);

	write_file($_macro_end_print_response, '', 'w');
	chmod($_macro_end_print_response, 0777);

	//EXEC END MACRO
	echo 'MACRO: sudo python ' . PYTHON_PATH . 'gmacro.py ' . $end_macro . ' ' . $_macro_end_print_trace . ' ' . $_macro_end_print_response . ' 1 > /dev/null &' . PHP_EOL;
	shell_exec('sudo python ' . PYTHON_PATH . 'gmacro.py ' . $end_macro . ' ' . $_macro_end_print_trace . ' ' . $_macro_end_print_response . ' 1 > /dev/null &');

	sleep(2);

	// SEND MAIL
	if (isset($attributes['mail']) && $attributes['mail'] == true && $status == 'performed') {

		$user = $db -> query('select * from sys_user where id=' . $task['user']);
		$user = $user[0];

		send_mail($attributes, $user);

	}

	$db -> close();

	//REMOVE ALL TEMPORARY FILES
	//shell_exec('sudo rm -rf ' . $attributes['folder']);
	//unlock();

	if ($reset) {
		sleep(2);
		include '/var/www/fabui/script/boot.php';
	}

	//$log->info('Task #'.$tid.' end finalizing');

}

function finalize_mill($tid, $status) {

	//LOAD DB
	$db = new Database();
	//GET TASK
	$task = $db -> query('select * from sys_tasks where id=' . $tid);
	$task = $task[0];

	//CHECK IF TASK WAS ALREARDY FINALIZED
	if ($task['status'] == 'stopped' || $task['status'] == 'performed') {
		return;
	}

	//GET TASK ATTRIBUTES
	$attributes = json_decode(file_get_contents($task['attributes']), TRUE);

	if ($status == 'stopped') {
		echo "STOPPING PROCESS..." . $attributes['pid'] . PHP_EOL;
		shell_exec('sudo kill ' . $attributes['pid']);

		/** FORCE RESET CONTROLLER */
		echo 'Force Reset' . PHP_EOL;
		$_command = 'sudo python ' . PYTHON_PATH . 'force_reset.py';
		shell_exec($_command);

		sleep(2);

		echo 'boot' . PHP_EOL;
		include '/var/www/fabui/script/boot.php';

		sleep(3);
		//==== LOCK FILE
		fopen(LOCK_FILE, "w");
	}

	shell_exec('sudo kill ' . $attributes['pid']);

	//UPDATE TASK
	update_task($tid, $status, file_get_contents($task['attributes']));

	$_macro_end_print_response = TEMP_PATH . 'macro_response';
	$_macro_end_print_trace = TEMP_PATH . 'macro_trace';

	$end_macro = 'end_print_additive';

	write_file($_macro_end_print_trace, '', 'w');
	chmod($_macro_end_print_trace, 0777);

	write_file($_macro_end_print_response, '', 'w');
	chmod($_macro_end_print_response, 0777);

	//EXEC END MACRO
	echo 'MACRO: sudo python ' . PYTHON_PATH . 'gmacro.py ' . $end_macro . ' ' . $_macro_end_print_trace . ' ' . $_macro_end_print_response . ' 1 > /dev/null &' . PHP_EOL;
	shell_exec('sudo python ' . PYTHON_PATH . 'gmacro.py ' . $end_macro . ' ' . $_macro_end_print_trace . ' ' . $_macro_end_print_response . ' 1 > /dev/null &');

	$db -> close();

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/** FINALIZE SLICE TASK
 *
 * @param $tid - TASK ID
 * @param $status - TASK STATUS (STOPPED - PERFORMED)
 *
 **/
function finalize_slice($tid, $status) {

	//LOAD DB
	$db = new Database();
	//GET TASK
	$task = $db -> query('select * from sys_tasks where id=' . $tid);

	//CHECK IF TASK WAS ALREARDY FINALIZED
	if ($task['status'] == 'stopped' || $task['status'] == 'performed') {
		exit ;
	}

	//GET TASK ATTRIBUTES
	$attributes = json_decode($task['attributes'], TRUE);

	if ($status == 'stopped') {// IF STATUS IS STOPPED JUST KILL ALL PROCESSESS
		//KILL ALL PROCESSESS
		shell_exec('sudo kill ' . $attributes['slicer_pid']);
		shell_exec('sudo kill ' . $attributes['perl_pid']);
		//$log->info('Task #'.$tid.' kill process #'.$attributes['slicer_pid'].' #'.$attributes['perl_pid']);

	} else {

		//MOVE OUTPUT FILE TO OBJECT FOLDER
		$_id_object = $attributes['id_object'];
		$id_file = $attributes['id_new_file'];
		$_output = $attributes['output'];
		$_configuration = $attributes['configuration'];

		$_output_file_name = get_name($_output);
		$_output_extension = get_file_extension($_output_file_name);
		$_output_folder_destination = '/var/www/upload/' . str_replace('.', '', $_output_extension) . '/';
		$_output_file_name = set_filename($_output_folder_destination, $_output_file_name);

		//MOVE TO FINALLY FOLDER
		shell_exec('sudo cp ' . $_output . ' ' . $_output_folder_destination . $_output_file_name);
		//$log->info('Task #'.$tid.' file moved in:'.$_output_folder_destination.$_output_file_name);

		//ADD PERMISSIONS
		shell_exec('sudo chmod 746 ' . $_output_folder_destination . $_output_file_name);

		//UPDATE FILE RECORD TO DB
		$data_file['file_name'] = $_output_file_name;
		$data_file['file_path'] = $_output_folder_destination;
		$data_file['full_path'] = $_output_folder_destination . $_output_file_name;
		$data_file['raw_name'] = str_replace($_output_extension, '', $_output_file_name);
		$data_file['orig_name'] = $_output_file_name;
		$data_file['file_ext'] = $_output_extension;
		$data_file['file_size'] = filesize($_output_folder_destination . $_output_file_name);
		$data_file['print_type'] = print_type($_output_folder_destination . $_output_file_name);
		$data_file['note'] = 'Sliced on ' . date("F j, Y, g:i a");
		$data_file['insert_date'] = 'now()';
		$data_file['file_type'] = 'text/plain';

		$db -> update('sys_files', array('column' => 'id', 'value' => $id_file, 'sign' => '='), $data_file);

		//ADD ASSOCIATION OBJECT->FILE
		$data['id_obj'] = $_id_object;
		$data['id_file'] = $id_file;

		$id_ass = $db -> insert('sys_obj_files', $data);

		/** LAUNCH GCODE ANALYZER */
		shell_exec('sudo php ' . SCRIPT_PATH . '/gcode_analyzer.php ' . $id_file . ' > /dev/null & echo $!');
	}

	$db -> close();

	//UPDATE TASK
	update_task($tid, $status);

	//REMOVE ALL TEMPORARY FILES
	shell_exec('sudo rm -rf ' . $attributes['folder']);
	unlock();
	//$log->info('Task #'.$tid.' end finalizing');

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/** FINALIZE SELF TEST TASK
 *
 * @param $tid - TASK ID
 * @param $status - TASK STATUS (STOPPED - PERFORMED)
 *
 **/
function finalize_self_test($tid, $status) {

	//global $log;

	//$log->info('Task #'.$tid.' self test '.$status);
	//$log->info('Task #'.$tid.' start finalizing');

	//LOAD DB
	$db = new Database();
	//GET TASK
	$task = $db -> query('select * from sys_tasks where id=' . $tid);
	$task = $task[0];

	//GET TASK ATTRIBUTES
	$attributes = json_decode($task['attributes'], TRUE);

	$db -> close();

	//UPDATE TASK
	update_task($tid, $status);

	//SLEEP MORE TO LET THE UI REFRESH
	//sleep(5);

	//REMOVE ALL TEMPORARY FILES
	shell_exec('sudo rm -rf ' . $attributes['folder']);
	unlock();
	//$log->info('Task #'.$tid.' end finalizing');

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/** FINALIZE UPDATE FIRMWARE TASK
 *
 * @param $tid - TASK ID
 * @param $status - TASK STATUS (STOPPED - PERFORMED)
 *
 **/
function finalize_update_fw($tid, $status) {

	//global $log;

	//$log->info('Task #'.$tid.' update FW '.$status);
	//$log->info('Task #'.$tid.' start finalizing');

	//LOAD DB
	$db = new Database();
	//GET TASK
	$task = $db -> query('select * from sys_tasks where id=' . $tid);
	$task = $task[0];

	//GET TASK ATTRIBUTES
	$attributes = json_decode($task['attributes'], TRUE);
	$db -> close();

	//UPDATE TASK
	update_task($tid, $status);
	//START UP THE BOARD
	shell_exec('sudo python ' . PYTHON_PATH . 'gmacro.py start_up /var/www/temp/flashing.trace /var/www/temp/flashing.log 1 > /dev/null &');
	sleep(10);
	//REMOVE ALL TEMPORARY FILES
	//shell_exec('sudo rm -rf ' . $attributes['folder']);
	//unlock();
	//$log->info('Task #'.$tid.' end finalizing');

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/** FINALIZE UPDATE FIRMWARE TASK
 *
 * @param $tid - TASK ID
 * @param $status - TASK STATUS (STOPPED - PERFORMED)
 *
 **/

function finalize_mesh($tid, $status) {

	//global $log;

	//$log->info('Task #'.$tid.' mesh '.$status);
	//$log->info('Task #'.$tid.' start finalizing');
	//LOAD DB
	$db = new Database();
	//GET TASK
	$task = $db -> query('select * from sys_tasks where id=' . $tid);
	$task = $task[0];

	//GET TASK ATTRIBUTES
	$attributes = json_decode($task['attributes'], TRUE);

	//MOVE OUTPUT FILE TO OBJECT FOLDER
	$_id_object = $attributes['id_object'];
	$id_file = $attributes['id_new_file'];
	$_output = $attributes['output'];

	$_output_file_name = get_name($_output);
	$_output_extension = get_file_extension($_output);
	$_output_folder_destination = '/var/www/upload/' . str_replace('.', '', $_output_extension) . '/';
	$_output_file_name = set_filename($_output_folder_destination, $_output_file_name);

	// MOVE TO FINALLY FOLDER
	shell_exec('sudo cp ' . $_output . ' ' . $_output_folder_destination . $_output_file_name);
	// ADD PERMISSIONS
	shell_exec('sudo chmod 746 ' . $_output_folder_destination . $_output_file_name);

	// INSERT RECORD TO DB
	$data_file['file_name'] = $_output_file_name;
	$data_file['file_path'] = $_output_folder_destination;
	$data_file['full_path'] = $_output_folder_destination . $_output_file_name;
	$data_file['raw_name'] = str_replace($_output_extension, '', $_output_file_name);
	$data_file['client_name'] = str_replace($_output_extension, '', $_output_file_name);
	$data_file['orig_name'] = $_output_file_name;
	$data_file['file_ext'] = $_output_extension;
	$data_file['file_size'] = filesize($_output_folder_destination . $_output_file_name);
	$data_file['print_type'] = print_type($_output_folder_destination . $_output_file_name);
	$data_file['note'] = 'Reconstructed on ' . date("F j, Y, g:i a");
	$data_file['insert_date'] = 'now()';
	$data_file['file_type'] = 'application/octet-stream';

	// ADD TASK RECORD TO DB
	$db -> update('sys_files', array('column' => 'id', 'value' => $id_file, 'sign' => '='), $data_file);

	// ADD ASSOCIATION OBJ FILE
	$data['id_obj'] = $_id_object;
	$data['id_file'] = $id_file;

	$id_ass = $db -> insert('sys_obj_files', $data);

	$db -> close();

	//UPDATE TASK
	update_task($tid, $status);
	sleep(10);
	//REMOVE ALL TEMPORARY FILES
	shell_exec('sudo rm -rf ' . $attributes['folder']);
	unlock();
	//$log->info('Task #'.$tid.' end finalizing');

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/** FINALIZE GENERAL TASK
 *
 * @param $tid - TASK ID
 * @param $type - TASK TYPE)
 * @param $status - TASK STATUS (STOPPED - PERFORMED)
 *
 **/

function finalize_general($tid, $type, $status) {

	//global $log;

	//$log->info('Task #'.$tid.' '.$type.' '.$status);
	//$log->info('Task #'.$tid.' start finalizing');

	//LOAD DB
	$db = new Database();
	//GET TASK
	$task = $db -> query('select * from sys_tasks where id=' . $tid);
	$task = $task[0];

	//GET TASK ATTRIBUTES
	$attributes = json_decode($task['attributes'], TRUE);
	$db -> close();

	//UPDATE TASK
	update_task($tid, $status);
	sleep(5);
	unlock();
	//REMOVE ALL TEMPORARY FILES
	shell_exec('sudo rm -rf ' . $attributes['folder']);
	//$log->info('Task #'.$tid.' end finalizing');

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/** FINALIZE SCAN TASK
 *
 * @param $tid - TASK ID
 * @param $type - TASK TYPE
 * @param $status - TASK STATUS (STOPPED - PERFORMED)
 *
 **/

function finalize_scan($tid, $type, $status) {
	//LOAD DB
	$db = new Database();
	//GET TASK
	$task = $db -> query('select * from sys_tasks where id=' . $tid);

	$task = $task[0];

	//GET TASK ATTRIBUTES
	$attributes = json_decode($task['attributes'], TRUE);

	if ($status == 'stopped') {
		
		$sp[] = 'fabui/python/r_scan.py';
		$sp[] = 'fabui/python/s_scan.py';
		$sp[] = 'fabui/python/p_scan.py';
		$sp[] = 'fabui/python/pg_scan.py';
		$sp[] = 'fabui/python/triangulation.py';
		
		kill_process_by_name($sp);

	} elseif ($type == 'scan_r' || $type == 'scan_p' || $type == "scan_s") {

		sleep(5);
		$id_obj = $attributes['id_obj'];

		if ($attributes['new'] == 'true') {

			// CREATE & ADD OBJ
			$_obj_data['obj_name'] = $attributes['obj_name'] == '' ? 'No name object' : $attributes['obj_name'];
			//$_obj_data['obj_name']        = 'scan_'.$_task_id.'_'.$_attributes['time'];
			$_obj_data['obj_description'] = 'Object created from scanning  on ' . date('l jS \of F Y h:i:s A');
			$_obj_data['date_insert'] = 'now()';
			$_obj_data['user'] = $task['user'];

			$id_obj = $db -> insert('sys_objects', $_obj_data);
		}

		if (!isset($attributes['pprocess_file'])) {
			$attributes['pprocess_file'] = 'cloud_' . $tid . '.asc';
		}

		// INSERT ASC FILE RECORD TO DB
		$_data_file['file_name'] = $attributes['pprocess_file'];
		$_data_file['file_type'] = 'application/octet-stream';
		$_data_file['file_path'] = '/var/www/upload/asc/';
		$_data_file['full_path'] = '/var/www/upload/asc/' . $attributes['pprocess_file'];
		$_data_file['raw_name'] = str_replace('.asc', '', $attributes['pprocess_file']);
		$_data_file['orig_name'] = $attributes['pprocess_file'];
		$_data_file['client_name'] = $attributes['pprocess_file'];
		$_data_file['file_ext'] = '.asc';
		$_data_file['file_size'] = filesize($attributes['folder'] . $attributes['pprocess_file']);
		$_data_file['insert_date'] = 'now()';
		$_data_file['note'] = 'Cloud data file obtained by scanning in ' . ucfirst($attributes['mode_name']) . ' mode on ' . date('l jS \of F Y h:i:s A');

		$id_file = $db -> insert('sys_files', $_data_file);

		/** MOVE ASC FILE TO UPLOAD/ASC */
		rename($attributes['folder'] . $attributes['pprocess_file'], $_data_file['full_path']);

		/** ASSOCIATE FILE TO OBJECT */
		$_data_assoc['id_obj'] = $id_obj;
		$_data_assoc['id_file'] = $id_file;

		$id_assoc = $db -> insert('sys_obj_files', $_data_assoc);

		/** UPDATE TASK */
		$attributes['id_obj'] = $id_obj;
		$attributes['id_file'] = $id_file;
		$attributes['monitor'] = json_encode(file_get_contents(TEMP_PATH . 'task_monitor.json'));

		$_data_update['attributes'] = json_encode($attributes);
		$db -> update('sys_tasks', array('column' => 'id', 'value' => $tid, 'sign' => '='), $_data_update);

	}

	/** UPDATE TASK */
	$attributes['monitor'] = json_decode(file_get_contents(TEMP_PATH . 'task_monitor.json'), true);

	$_data_update['attributes'] = json_encode($attributes);
	$db -> update('sys_tasks', array('column' => 'id', 'value' => $tid, 'sign' => '='), $_data_update);
	$db -> close();

	// EXEC MACRO END_SCAN
	$_time = time();
	$_destination_trace = TEMP_PATH . 'macro_trace';
	$_destination_response = TEMP_PATH . 'macro_response';

	write_file($_destination_trace, '', 'w');
	chmod($_destination_trace, 0777);

	write_file($_destination_response, '', 'w');
	chmod($_destination_response, 0777);

	/** EXEC */
	$_command = 'sudo python ' . PYTHON_PATH . 'gmacro.py end_scan ' . $_destination_trace . ' ' . $_destination_response . ' 1';
	shell_exec($_command);

	//UPDATE TASK
	update_task($tid, $status);
	//sleep(5);
	//REMOVE ALL TEMPORARY FILES
	//shell_exec('sudo rm -rf ' . $attributes['folder']);
	//unlock();
	//$log->info('Task #'.$tid.' end finalizing');

}

function finalize_update_sw($tid, $status) {

	//LOAD DB
	$db = new Database();
	//GET TASK
	$task = $db -> query('select * from sys_tasks where id=' . $tid);
	$task = $task[0];

	//GET TASK ATTRIBUTES
	$attributes = json_decode($task['attributes'], TRUE);
	$update_info = json_decode(file_get_contents($attributes['monitor']), TRUE);
	//update software version
	$db -> update('sys_configuration', array('column' => 'sys_configuration.key', 'value' => 'fabui_version', 'sign' => '='), array('value' => $update_info['version']));
	$db -> close();
	//UPDATE TASK
	update_task($tid, $status);
	sleep(5);
	unlock();
	//REMOVE ALL TEMPORARY FILES
	shell_exec('sudo rm -rf ' . $attributes['folder']);

}

function send_mail($attributes, $user) {

	// subject
	$subject = 'Task completed';

	// message
	$message = 'Hi ' . ucfirst($user['first_name']) . '<br> The print is completed';

	// To send HTML mail, the Content-type header must be set
	$headers = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	// Additional headers
	$headers .= 'To: ' . ucfirst($user['first_name']) . ' ' . ucfirst($user['last_name']) . ' <' . $user['email'] . ">\r\n";
	$headers .= 'From: Your Fabtotum Personal Fabricator <noreply@fabtotum.com>' . "\r\n";
	// Mail it
	$to = $user['email'];
	mail($to, $subject, $message, $headers);

}

function unlock() {
	if (file_exists(LOCK_FILE)) {
		shell_exec('sudo rm ' . LOCK_FILE);
	}
}
?>