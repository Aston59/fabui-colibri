<?php
session_start();
session_destroy();
//require_once '/var/www/lib/config.php';
//shell_exec('sudo bash '.SCRIPT_PATH.'bash/reboot.sh');
shell_exec('sudo reboot');
echo true;
?>