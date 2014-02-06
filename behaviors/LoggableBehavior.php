<?php

class LoggableBehavior extends CActiveRecordBehavior{
	private $_oldattributes = array();
	/** @var $_trail AuditTrail|null */
	private $_auditTrail = null;

	public $allowed = array();
	public $ignored = array();
	public $ignored_class = array();

	public $dateFormat = 'Y-m-d H:i:s';
	public $userAttribute = null;

	public $storeTimestamp = false;
	public $skipNulls = true;

	public function afterSave($event){
		$allowedFields = $this->allowed;
		$ignoredFields = $this->ignored;
		$ignoredClasses = $this->ignored_class;

		$newattributes = $this->getOwner()->getAttributes();
		$oldattributes = $this->getOldAttributes();

		// Lets check if the whole class should be ignored
		if(sizeof($ignoredClasses) > 0){
			if(array_search(get_class($this->getOwner()), $ignoredClasses) !== false)
				return;
		}

		// Lets unset fields which are not allowed
		if(sizeof($allowedFields) > 0){
			foreach($newattributes as $f => $v){
				if(array_search($f, $allowedFields) === false) unset($newattributes[$f]);
			}

			foreach($oldattributes as $f => $v){
				if(array_search($f, $allowedFields) === false) unset($oldattributes[$f]);
			}
		}

		// Lets unset fields which are ignored
		if(sizeof($ignoredFields) > 0){
			foreach($newattributes as $f => $v){
				if(array_search($f, $ignoredFields) !== false) unset($newattributes[$f]);
			}

			foreach($oldattributes as $f => $v){
				if(array_search($f, $ignoredFields) !== false) unset($oldattributes[$f]);
			}
		}

		// If this is a new record lets add a CREATE notification
		if ($this->getOwner()->getIsNewRecord())
			$this->leaveTrail('CREATE');

		// If no difference then WHY?
		// There is some kind of problem here that means "0" and 1 do not diff for array_diff so beware: stackoverflow.com/questions/12004231/php-array-diff-weirdness :S
		if(count(array_diff_assoc($newattributes, $oldattributes)) <= 0) return;

		// Now lets actually write the attributes
		$this->auditAttributes($newattributes, $oldattributes);

		// Reset old attributes to handle the case with the same model instance updated multiple times
		$this->setOldAttributes($this->getOwner()->getAttributes());

		return parent::afterSave($event);
	}

	public function auditAttributes($newattributes, $oldattributes = array()){

		foreach ($newattributes as $name => $value) {
			$old = isset($oldattributes[$name]) ? $oldattributes[$name] : '';

			// If we are skipping nulls then lets see if both sides are null
			if($this->skipNulls && empty($old) && empty($value)){
				continue;
			}

			// If they are not the same lets write an audit log
			if ($value != $old) {
				$this->leaveTrailDetail($this->getAuditTrailId(), $name, $value, $old);
			}
		}
	}

	public function getAuditTrailId(){
		if($this->_auditTrail==null)
			$this->leaveTrail($this->getOwner()->getIsNewRecord()?'CREATE':'UPDATE');

		if($this->_auditTrail!=null)
			return $this->_auditTrail->id;
		else
			return null;
	}

	public function afterDelete($event){
		$this->leaveTrail('DELETE');
		return parent::afterDelete($event);
	}

	public function afterFind($event){
		$this->setOldAttributes($this->getOwner()->getAttributes());
		return parent::afterFind($event);
	}

	public function getOldAttributes(){
		return $this->_oldattributes;
	}

	public function setOldAttributes($value){
		$this->_oldattributes=$value;
	}

	public function leaveTrail($action){
		$log			= new AuditTrail();
		$log->action	= $action;
		$log->model		= get_class($this->getOwner()); // Gets a plain text version of the model name
		$log->model_pk	= $this->getNormalizedPk();
		$log->occurredOn		= $this->storeTimestamp ? time() : date($this->dateFormat); // If we are storing a timestamp lets get one else lets get the date
		$log->id_users	= $this->getUserId(); // Lets get the user id
		$log->save();
		$this->_auditTrail=$log;
	}

	public function leaveTrailDetail($id_auditTrail, $name = null, $value = null, $oldValue = null){
		$log			= new AuditTrailDetail();
		$log->id_auditTrail	= $id_auditTrail;
		$log->field		= $name;
		$log->oldValue  = $oldValue;
		$log->newValue  = $value;
		return $log->save();
	}

	public function getUserId(){
		if(isset($this->userAttribute)){
			$data = $this->getOwner()->getAttributes();
			return isset($data[$this->userAttribute]) ? $data[$this->userAttribute] : null;
		}else{
			try {
				$userid = Yii::app()->user->id;
				return empty($userid) ? null : $userid;
			} catch(Exception $e) { //If we have no user object, this must be a command line program
				return null;
			}
		}
	}

	protected function getNormalizedPk(){
		$pk = $this->getOwner()->getPrimaryKey();
		return is_array($pk) ? json_encode($pk) : $pk;
	}
}
