<?php

namespace Internetrix\GridFieldExtras\Model;

use GridFieldRelationHandler;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\GridField\GridField;

class BetterGridFieldHasOneRelationHandler extends GridFieldRelationHandler {
	protected $onObject;
	protected $relationName;
	protected $state = null;

	protected $targetObject;

	public function __construct(DataObject $onObject, $relationName, $targetFragment = 'before') {
		$this->onObject = $onObject;
		$this->relationName = $relationName;

		$hasOne = $onObject->has_one($relationName);
		if(!$hasOne) {
			user_error('Unable to find a has_one relation named ' . $relationName . ' on ' . $onObject->ClassName, E_USER_WARNING);
		}
		$this->targetObject = $hasOne;

		parent::__construct(false, $targetFragment);
	}
	
	protected function getState($gridField) {
		if(!$this->state) {
			$this->state = $gridField->State->GridFieldRelationHandler;
			$this->state = $this->setupState($this->state);
		}
		return $this->state;
	}

	protected function setupState($state, $extra = null) {
		if(!isset($state->RelationVal)) {
			$state->RelationVal = 0;
			$state->FirstTime = 1;
		} else {
			$state->FirstTime = 0;
		}
		if(!isset($state->ShowingRelation)) {
			$state->ShowingRelation = 0;
		}
		
		$this->state = $state;
		
		if($this->state->FirstTime) {
			$this->state->RelationVal = $this->onObject->{$this->relationName}()->ID;
		}
		return $this->state;
	}

	public function getColumnContent($gridField, $record, $columnName) {
		$class = $gridField->getModelClass();
		if(!($class == $this->targetObject || is_subclass_of($class, $this->targetObject))) {
			user_error($class . ' is not a subclass of ' . $this->targetObject . '. Perhaps you wanted to use ' . $this->targetObject . '::get() as the list for this GridField?', E_USER_WARNING);
		}

		$state = $this->getState($gridField);
		$checked = $state->RelationVal == $record->ID;
		$field = new ArrayData(array('Checked' => $checked, 'Value' => $record->ID, 'Name' => $this->relationName . 'ID'));
		return $field->renderWith('GridFieldHasOneRelationHandlerItem');
	}

	protected function saveGridRelation(GridField $gridField, $arguments, $data) {
		$field = $this->relationName . 'ID';
		$state = $this->getState($gridField);
		$id = intval($state->RelationVal);
		$this->onObject->{$field} = $id;
		$this->onObject->write();
		parent::saveGridRelation($gridField, $arguments, $data);
	}
}
