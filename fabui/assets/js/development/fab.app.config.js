/*
 * FABTOTUM APP CONFIG
 */
var base_url = '/fabui/'; 
/*
 * general
 */
number_updates = 0;
number_tasks   = 0;
/*
 * DOM elements
 */
$.console = $(".console"); //where display trace content
/*
 *  main url actions
 */
var reboot_url_action           = base_url + '/control/reboot';
var poweroff_url_action         = base_url + '/control/poweroff';
var logout_url                  = base_url + '/login/out';
var reset_controller_url_action = base_url + '/control/resetController';
var stop_all_url_action         = base_url + '/control/emergency';
var set_secure_url              = base_url + '/control/setSecure';
var set_recovery_url            = base_url + '/control/setRecovery';
var websocket_fallback_url      = base_url + '/control/ws_fallback';
var control_url                 = base_url + '/control';
var temperatures_file_url       = '/temp/temperature.json';
var jog_response_file_url       = '/temp/jog_response.json';
var task_monitor_file_url       = '/temp/task_monitor.json';
var updates_json_url            = '/temp/updates.json';
var network_info_url            = '/temp/network.json';
var update_check_url            = '/fabui/updates/check';
var check_notification_url      = '/fabui/application/modules/controller/ajax/check_notifications.php';
var secure_url_action           = '/fabui/application/modules/controller/ajax/secure.php';
var new_head_url_action         = '#maintenance/head?warning=1';
var emergency_json_url          = '/tmp/emergency.json';
var serial_exec_url_action      = base_url + '/jog/exec';
var first_setup_url_action      = '/#controller/first_setup';
var check_internet_url_action   = '/temp/internet';
/*
* xmlrpc
*/
var xmlrpc = true;
/*
 * socket 
 */
var socket_host = window.location.hostname;
var socket_port = 9002;
var socket = null;
var socket_connected = false;

/*
 * intervals 
 */
var notification_interval       = null;
var notification_interval_timer = 10000; //10 seconds
var safety_interval             = null;
var safety_interval_timer       = 3000 //3 seconds
var temperatures_interval       = null;
var temperatures_interval_timer = 5000 //5 seconds
var status_interval             = null;
var status_interval_timer       = 5000;
/*
 * global flags
 */
var is_macro_on     = false; //true if macro is running
var is_task_on      = false; //true if taks is running
var is_stopping_all = false; //true if emergency button was clicked
var is_emergency    = false; //true if printer is in emergency status

/*
 * 
 * 
 */
var maxTemperaturesPlot = 200;
var temperaturesPlot = {extruder: {temp: [], target: []}, bed: {temp:[], target:[]}};
//var app_storage_data = { last_update : new Date(), update: {}, task: {}};
//var app_storage_expire_time = 1800 ; // seconds (30 minutes) 
