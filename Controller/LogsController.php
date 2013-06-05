<?php
class LogsController extends DatabaseLoggerAppController {

	var $name = 'Logs';
	var $helpers = array('Time','Icing.Csv');
	var $paginate = array(
		'order' => 'Log.id DESC',
		'fields' => array(
			'Log.created',
			'Log.type',
			'Log.message',
			'Log.id'
		)
	);

	function admin_index($filter = null) {
		if(!empty($this->data)){
			$filter = $this->data['Log']['filter'];
		}
		$this->dataToNamed();
		$conditions = array_merge(
			$this->Log->search($this->request->params['named']),
			$this->Log->textSearch($filter)
		);
		$this->set('logs',$this->paginate($conditions));
		$this->set('types', $this->Log->getTypes());
		$this->set('filter', $filter);
		$this->set('search_params', $this->request->params['named']);
	}
	
	function admin_export($filter = null){
		$this->layout = 'csv';
		if(!empty($this->data)){
			$filter = $this->data['Log']['filter'];
		}
		if($this->RequestHandler->ext != 'csv'){
			$this->redirect(array('action' => 'export', 'ext' => 'csv', $filter));
		}
		$this->dataToNamed();
		$conditions = array_merge(
			$this->Log->search($this->request->params['named']),
			$this->Log->textSearch($filter)
		);
		$this->set('filename', 'export_logs.csv');
		$this->set('data',$this->Log->export(array('conditions' => $conditions, 'recursive' => -1)));
	}
	
	function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid log'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('log', $this->Log->read(null, $id));
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for log'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Log->delete($id)) {
			$this->Session->setFlash(__('Log deleted'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Log was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
