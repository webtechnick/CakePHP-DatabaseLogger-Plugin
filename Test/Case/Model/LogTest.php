<?php
/* Log Test cases generated on: 2011-08-08 13:46:32 : 1312832792*/
App::uses('Log', 'DatabaseLogger.Model');

class LogTest extends CakeTestCase {
	var $fixtures = array('app.database_logger.log');
	
	function startTest() {
		$this->Log = ClassRegistry::init('Log');
	}
	
	function test_textSearch(){
		$result = $this->Log->textSearch('query');
		debug($result);
	}

	function endTest() {
		unset($this->Log);
		ClassRegistry::flush();
	}

}
