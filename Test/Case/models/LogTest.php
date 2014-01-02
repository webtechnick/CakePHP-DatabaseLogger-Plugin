<?php
/* Log Test cases generated on: 2011-08-08 13:46:32 : 1312832792*/
App::uses('Log', 'DatabaseLog.Model');

class LogTest extends CakeTestCase {
	var $fixtures = array('app.database_log.log');
	
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
