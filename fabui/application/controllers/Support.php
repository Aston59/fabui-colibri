<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
 
 class Support extends FAB_Controller {
 	
	public function index()
	{
		//load libraries, helpers, model
		$this->load->library('smart');
		$this->load->helper('form');
		//data
		$data = array();
		
		//main page widget
		$widgetOptions = array(
			'sortable'     => false, 'fullscreenbutton' => true,  'refreshbutton' => false, 'togglebutton' => false,
			'deletebutton' => false, 'editbutton'       => false, 'colorbutton'   => false, 'collapsed'    => false
		);
		
		$widget         = $this->smart->create_widget($widgetOptions);
		$widget->id     = 'main-widget-4th-axis';
		$widget->header = array('icon' => 'fa-support ', "title" => "<h2>Support</h2>");
		$widget->body   = array('content' => $this->load->view('support/widget', $data, true ), 'class'=>'fuelux');
		
		$this->addJsInLine($this->load->view('support/js', $data, true));
		$this->content = $widget->print_html(true);
		$this->view();
	}

 }
 
?>
