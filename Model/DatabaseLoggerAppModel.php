<?php
class DatabaseLoggerAppModel extends AppModel {
	var $recursive = -1;
	/**
	* Filter fields
	*/
	var $searchFields = array();
	
	/**
	* Configurations
	*/
	public $configs = array(
		'write' => 'default',
		'read' => 'default',
	);
	
	/**
	* Set the default datasource to the read setup in config
	*/
	public function __construct($id = false, $table = null, $ds = null) {
		if(Configure::load('database_logger')){
			$this->configs = Configure::read('DatabaseLogger');
		}
		parent::__construct($id, $table, $ds);
		$this->setDataSourceRead();
	}
	
	/**
	* Overwrite save to write to the datasource defined in config
	*/
	public function save($data = null, $validate = true, $fieldList = array()) {
		$this->setDataSourceWrite();
		$retval = parent::save($data, $validate, $fieldList);
		$this->setDataSourceRead();
		return $retval;
	}
	
	/**
	* Overwrite delete to delete to the datasource defined in config
	*/
	public function delete($id = null, $cascade = true) {
		$this->setDataSourceWrite();
		$retval = parent::delete($id, $cascade);
		$this->setDataSourceRead();
		return $retval;
	}
	
	/**
	* Overwrite find so I can do some nice things with it.
	* @param string find type
	* - last : find last record by created date
	* @param array of options
	*/
	function find($type = 'first', $options = array()){
		switch($type){
		case 'last':
			$options = array_merge(
				$options,
				array('order' => "{$this->alias}.{$this->primaryKey} DESC")
				);
			return parent::find('first', $options);    
		default: 
			return parent::find($type, $options);
		}
	}
	
	/**
	* return conditions based on searchable fields and filter
	* @param string filter
	* @return conditions array
	*/
	function generateFilterConditions($filter = NULL, $pre = ''){
		$retval = array();
		if($filter){
			foreach($this->searchFields as $field){
				$retval['OR']["$field LIKE"] =  '%' . $filter . '%'; 
			}
		}
		return $retval;
	}
	
	/**
	* Set the datasource to be read
	* if being tested, don't change, otherwise change to what we read
	*/
	private function setDataSourceRead(){
		if($this->useDbConfig != 'test'){
			$this->setDataSource($this->configs['read']);
		}
	}
	
	/**
	* Set the datasource to be write
	* if being tested, don't change, otherwise change to what we config
	*/
	private function setDataSourceWrite(){
		if($this->useDbConfig != 'test'){
			$this->setDataSource($this->configs['write']);
		}
	}
	
	/**
	  * Export the current model table into a csv file.
	  */
	function export($options = array(), $showHeaders = true){
	  $default_options = array(
	    'contain' => array()
	  );
	  
	  $options = array_merge($default_options, $options);
	  
	  $columns = array_keys($this->schema());
	  $headers = array();
	  foreach($columns as $column){
	    $headers[$this->alias][$column] = $column;
	  }
	  $data = $this->find('all', $options);
	  
	  array_unshift($data, $headers);
	  return $data;
	}
	
	/**
    * Generate a find or the conditions for a find
    * based on the search criteria  passed into it.
    *
    * @param array params to search on
    * @param array of options
    *  - order => array of how to order it (Model.created ASC by default)
    *  - recursive => int of recursive type (0 by default)
    *  - find => 'all' for find all to be preformed, 'first' for find first to be preformed, false if you just want conditions (false by default)
    *  - fields => array of fields to select. (* by default)
    */
  function search($params = array(), $options = array()){
    $options = array_merge(
      array(
        'order' => array("{$this->alias}.created ASC"),
        'recursive' => 0,
        'fields' => array('*'),
        'find' => false,
      ),
      $options
    );
    if(empty($this->_fields)){
      $this->_fields = $this->getColumnTypes();
    }
    
    //search through params, make sure there is a field that coresponds.
    $conditions = array();
    $field_options = array('LIKE','>=','<=','>','<');
    foreach($params as $key => $value){
      if(isset($this->_fields[$key]) && $value){
        if(is_array($value)){
          if(!empty($value['year'])){
            $conditions['AND']["{$this->alias}.$key"] = $value['year'] ."-". $value['month'] . "-" . $value['day'];
          }
        } else {
        	$values = explode('[or]', $value);
        	foreach($values as $value){
						$query_opt = (strpos($value, '%') !== false || strpos($value, '*') !== false) ? ' LIKE' : '';
						if(strpos($value, '*') !== false){
							$value = str_replace('*','%', $value);
						}
						if(strpos($value, '!') === 0){
        			$query_opt = ' !=';
        			$value = str_replace('!','', $value);
        		}
        		if($value == 'null'){
        			$value = '';
        		}
						if(count($values) == 1){
							$conditions['AND']["{$this->alias}.$key$query_opt"] = $value;
						} else {
							$conditions['AND']['OR'][] = array("{$this->alias}.$key$query_opt" => $value);
						}
					}
        }
      } elseif(isset($this->_fields['modified']) && ($key == 'start_date' || $key == 'end_date') && !empty($value['year'])){
        $opt = $key == 'start_date' ? '>=' : '<=';
        $conditions['AND']["{$this->alias}.created $opt"] = $params[$key]['year'] ."-". $params[$key]['month'] . "-" . $params[$key]['day'];
      } else {
        foreach($field_options as $opt){
          if(strpos($key, $opt)){
            $field = substr($key, 0, strlen($key) - (strlen($opt) + 1));
            if(isset($this->_fields[$field]) && $value){
            	$conditions['AND']["{$this->alias}.$key"] = $value;
              break;
            }
          }
        }
      }
    }
    if($options['find']){
      return $this->find($options['find'], array(
        'conditions' => $conditions,
        'order' => $options['order'],
        'recursive' => $options['recursive'],
        'fields' => $options['fields'],
      ));
    } else {
      return $conditions;
    }
  }
}