<?php
/**
* Database logger
* @author Nick Baker 
* @version 1.0
* @license MIT

# Setup

in app/config/bootstrap.php add the following

CakeLog::config('database', array(
	'engine' => 'DatabaseLog.DatabaseLog',
	'model' => 'CustomLogModel' //'DatabaseLog.Log' by default
));

*/
App::uses('ClassRegistry', 'Utility');
App::uses('CakeLogInterface','Log');
App::uses('Log', 'DatabaseLog.Model');
class DatabaseLog implements CakeLogInterface{
	
	/**
	* Model name placeholder
	*/
	var $model = null;
	
	/**
	* Model object placeholder
	*/
	var $Log = null;
	
	/**
	* Contruct the model class
	*/
	function __construct($options = array()){
		$this->model = isset($options['model']) ? $options['model'] : 'DatabaseLog.Log';
		$this->Log = ClassRegistry::init($this->model);
	}
	
	/**
	* Write the log to database
	*/
	function write($type, $message){
		$this->Log->create();		
		$this->Log->save(array(
			'type' => $type,
			'message' => $message
		));
	}
}
