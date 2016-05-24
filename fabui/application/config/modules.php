<?php
//
$make_sons[] = array('name' => 'print', 'label'=>'Print', 'menu' => TRUE, 'icon' => '<i class="icon-fab-print fa-lg fa-fw"></i>');
$make_sons[] = array('name' => 'mill',  'label'=>'Mill',  'menu' => TRUE, 'icon' => '<i class="icon-fab-mill fa-lg fa-fw"></i>');
$make_sons[] = array('name' => 'scan',  'label'=>'Scan',  'menu' => TRUE, 'icon' => '<i class="icon-fab-scan fa-lg fa-fw"></i>');
$make_sons[] = array('name' => 'history', 'label'=>'History', 'menu' => TRUE, 'icon' => '<i class="fa fa-lg fa-fw fa-history"></i>');

// maintenance sub-items menu
$_maintenance_sons[] = array('name' => 'head',              'label'=> 'Head Installation',              'icon' => '<i class="fa fa-lg fa-fw  fa-toggle-down"></i>');
$_maintenance_sons[] = array('name' => 'spool',             'label'=> 'Spool management',             'icon' => '<i class="fa fa-lg fa-fw  fa-circle-o-notch"></i>');
$_maintenance_sons[] = array('name' => 'feeder',            'label'=> 'Feeder',            'icon' => '<i class="fa fa-lg fa-fw  fa-cog"></i>');
$_maintenance_sons[] = array('name' => '4-axis',            'label'=> '4th Axis',          'icon' => '<i class="fa fa-lg fa-fw  fa-arrows-h"></i>');
$_maintenance_sons[] = array('name' => 'bed-calibration',   'label'=> 'Bed Calibration',   'icon' => '<i class="fa fa-lg fa-fw  fa-arrows-h"></i>');
$_maintenance_sons[] = array('name' => 'probe-calibration', 'label'=> 'Probe Calibration', 'icon' => '<i class="fa fa-lg fa-fw fa-crosshairs"></i>');
//$_maintenance_sons[] = array('name' => 'self-test',         'label'=> 'Self Test',         'icon' => '<i class="fa fa-lg fa-fw  fa-dashboard"></i>');
$_maintenance_sons[] = array('name' => 'first-setup',       'label'=> 'First Setup',       'icon' => '<i class="fa fa-lg fa-fw  fa-magic"></i>');
$_maintenance_sons[] = array('name' => 'system-info',       'label'=> 'System Info',       'icon' => '<i class="fa fa-lg fa-fw  fa-info-circle"></i>');


$_network_sons[] = array('name' => 'eth',  'label'=> 'Ethernet', 'icon' => '<i class="fa fa-lg fa-fw fa-sitemap"></i>');
$_network_sons[] = array('name' => 'wlan', 'label'=> 'Wi-Fi',    'icon' => '<i class="fa fa-lg fa-fw fa-wifi"></i>');
$_network_sons[] = array('name' => 'dns',  'label'=> 'DNS-SD',   'icon' => '<i class="fa fa-lg fa-fw fa-binoculars "></i>');

$_settings_sons[] = array('name' => 'index',     'label'=> 'Hardware',   'icon' => '<i class="fa fa-lg fa-fw fa-gear"></i>');
$_settings_sons[] = array('name' => 'network',   'label'=> 'Network',   'icon' => '<i class="fa fa-lg fa-fw fa-globe"></i>', 'sons' => $_network_sons);
//$_settings_sons[] = array('name' => 'hardware',  'label'=> 'Hardware',  'icon' => '<i class="fa fa-lg fa-fw fa-gear"></i>');
$_settings_sons[] = array('name' => 'raspi-cam', 'label'=> 'Raspi Cam', 'icon' => '<i class="fa fa-lg fa-fw fa-camera"></i>');

$_plugins_sons[] = array('name' => 'index', 'label'=> 'Installed Plugins');
$_plugins_sons[] = array('name' => 'upload', 'label'=> 'Add New');

$modules[] = array('name' => 'dashboard',     'label'=>'Dashboard',     'menu' => TRUE, 'icon' => '<i class="fa fa-lg fa-fw  fa-home"></i>');
$modules[] = array('name' => 'make',          'label'=>'Make',          'menu' => TRUE, 'icon' => '<i style="vertical-align:0% !important;" class="fa fa-lg fa-fw fa-play fa-rotate-90  txt-color-blue"></i>', 'sons' => $make_sons);
$modules[] = array('name' => 'jog',           'label'=>'Jog',           'menu' => TRUE, 'icon' => '<i class="fab-lg fab-fw  icon-fab-jog"></i>');
$modules[] = array('name' => 'objectmanager', 'label'=>'Objectmanager', 'menu' => TRUE, 'icon' => '<i class="fa fa-lg fa-fw fa-folder-open"></i>');
$modules[] = array('name' => 'maintenance',   'label'=>'Maintenance',   'menu' => TRUE, 'icon' => '<i class="fa fa-lg fa-fw  fa-wrench"></i>', 'sons' => $_maintenance_sons);
$modules[] = array('name' => 'settings',      'label'=>'Settings',      'menu' => TRUE, 'icon' => '<i class="fa fa-lg fa-fw  fa-cogs"></i>','sons'=> $_settings_sons);
$modules[] = array('name' => 'updates',       'label'=>'Updates',       'menu' => TRUE, 'icon' => '<i class="fa fa-lg fa-fw  fa-refresh"></i>');
$modules[] = array('name' => 'plugin',        'label'=>'Plugins',       'menu' => TRUE, 'icon' => '<i class="fa fa-lg fa-fw  fa-plug"></i>', 'sons'=>$_plugins_sons);
$modules[] = array('name' => 'support',       'label'=>'Support',       'menu' => TRUE, 'icon' => '<i class="fa fa-lg fa-fw  fa-life-ring"></i>');