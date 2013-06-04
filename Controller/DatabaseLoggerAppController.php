<?php
class DatabaseLoggerAppController extends AppController {
	protected function dataToNamed($key = 'Search'){
  	$params = is_array($this->request->params['named']) ? $this->request->params['named'] : array();
  	$data = isset($this->request->data[$key]) ? $this->request->data[$key] : array();
  	$this->request->params['named'] = array_merge($data, $params);
  }
}