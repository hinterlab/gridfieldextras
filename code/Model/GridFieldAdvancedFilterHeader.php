<?php 

/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 *
 */

namespace Internetrix\GridFieldExtras\Model;

use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Form;
use SilverStripe\Control\Controller;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\TextField;

class GridFieldAdvancedFilterHeader extends GridFieldFilterHeader implements GridField_URLHandler {
	
	/**
	 * These are the columns that use a custom field
	 *
	 * @var array
	 */
	protected $filterFields = array();
	
	/**
	 * These are the columns where the post will send an ID. We need to look up the ID and search the mapped field
	 * Needs to be in the format 
	 * array(
	 * 	'FieldName' => array(
	 * 		'Class' 	  => '<class>',
	 * 		'LookUpField' => '<field>'
	 *));
	 *
	 * @var array
	 */
	protected $idToFieldMap = array();
	
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if(!$this->checkDataType($gridField->getList())) return;
	
		$state = $gridField->State->GridFieldFilterHeader;
		if($actionName === 'filter') {
			if(isset($data['filter'][$gridField->getName()])){
				foreach($data['filter'][$gridField->getName()] as $key => $filter ){
					$map = $this->getIDtoFieldMap();

					if(isset($map[$key]) && !empty($filter)){
						$obj = isset($map[$key]['Class']) ? $map[$key]['Class'] : null;
						$obj = $obj ? $obj::get()->byID($filter) : null;
						if($obj){
							$obj = $obj::get()->byID($filter);
							$state->Columns->$key = isset($map[$key]['LookUpField']) ? $obj->$map[$key]['LookUpField'] : null;
						}
					}else{
						$state->Columns->$key = $filter;
					}
				}
			}
		} elseif($actionName === 'reset') {
			$state->Columns = null;
		}
	}
	
	
	public function handleForm(GridField $grid, $request) {
	
		$form = $this->getForm($grid);
	
		foreach($form->Fields() as $field) {
			$field->setName($this->getFieldName($field->getName(), $grid));
		}
	
		return $form;
	}

	public function getURLHandlers($grid) {
		return array(
			'filter/form' => 'handleForm'
		);
	}
	
	public function getFields(GridField $grid) {
		$cols   = $this->getFilterFields();
		$fields = new FieldList();
	
		foreach($cols as $col => $info) {
			$field = null;
	
			if($info instanceof Closure) {
				$field = call_user_func($info, null, $col, $grid);
			} elseif(is_array($info)) {
				if(isset($info['callback'])) {
					$field = call_user_func($info['callback'], null, $col, $grid);
				} elseif(isset($info['field'])) {
					$field = new $info['field']($col);
				}
	
				if(!$field instanceof FormField) {
					throw new Exception(sprintf(
							'The field for column "%s" is not a valid form field',
							$col
					));
				}
			}
	
			if(!$field instanceof FormField) {
				throw new Exception(sprintf(
						'Invalid form field instance for column "%s"', $col
				));
			}
	
			$fields->push($field);
		}
	
		return $fields;
	}
	
	public function getForm(GridField $grid) {
		$fields = $this->getFields($grid);
	
		$form = new Form($this, null, $fields, new FieldList());
	
		$form->setFormAction(Controller::join_links(
				$grid->Link(), 'filter/form'
		));
	
		return $form;
	}
	
	/**
	 * Override the default behaviour of showing the models summaryFields with
	 * these fields instead
	 * Example: array( 'Name' => 'Members name', 'Email' => 'Email address')
	 *
	 * @param array $fields
	 */
	public function setFilterFields($fields) {
		if(!is_array($fields)) {
			throw new InvalidArgumentException('
				Arguments passed to GridFieldAdvancedFilterHeader::setFilterFields() must be an array');
		}
		$this->filterFields = $fields;
		return $this;
	}
	
	/**
	 * Get the FilterFields
	 *
	 * @return array
	 * @see GridFieldAdvancedFilterHeader::setFilterFields
	 */
	public function getFilterFields() {
		return $this->filterFields;
	}
	
	public function setIDToFieldMaps($fields) {
		if(!is_array($fields)) {
			throw new InvalidArgumentException('
				Arguments passed to GridFieldAdvancedFilterHeader::setIDToFieldMaps() must be an array');
		}
		$this->idToFieldMap = $fields;
		return $this;
	}
	
	public function getIDtoFieldMap() {
		return $this->idToFieldMap;
	}
	
	public function getHTMLFragments($gridField) {
		if(!$this->checkDataType($gridField->getList())) return;
	
		$forTemplate = new ArrayData(array());
		$forTemplate->Fields = new ArrayList;
		$columns = $gridField->getColumns();
		$filterArguments = $gridField->State->GridFieldFilterHeader->Columns->toArray();
		$currentColumn = 0;
		$customFilterFields = $this->getFilterFields();
		
		foreach($columns as $columnField) {
			$currentColumn++;
			$metadata = $gridField->getColumnMetadata($columnField);
			$title = $metadata['title'];
			$fields = new FieldGroup();
				
			if($title && $gridField->getList()->canFilterBy($columnField)) {
				$value = '';
				if(isset($filterArguments[$columnField])) {
					$value = $filterArguments[$columnField];
				}
				if(isset($customFilterFields[$columnField])){
					if($customFilterFields[$columnField] instanceof Closure) {
						$field = call_user_func($customFilterFields[$columnField], null, $columnField, $gridField);
					}
					$field->setForm($this->getForm($gridField));
					$field->setName($this->getFieldName($columnField, $gridField));
				}else{
					$field = new TextField('filter[' . $gridField->getName() . '][' . $columnField . ']', '', $value);
				}
	
				$field->addExtraClass('ss-gridfield-sort');
				$field->addExtraClass('no-change-track');
	
				$field->setAttribute('placeholder',
						_t('GridField.FilterBy', "Filter by ") . _t('GridField.'.$metadata['title'], $metadata['title']));
	
				$fields->push($field);
				$fields->push(
						GridField_FormAction::create($gridField, 'reset', false, 'reset', null)
						->addExtraClass('ss-gridfield-button-reset')
						->setAttribute('title', _t('GridField.ResetFilter', "Reset"))
						->setAttribute('id', 'action_reset_' . $gridField->getModelClass() . '_' . $columnField)
				);
			}
	
			if($currentColumn == count($columns)){
				$fields->push(
						GridField_FormAction::create($gridField, 'filter', false, 'filter', null)
						->addExtraClass('ss-gridfield-button-filter')
						->setAttribute('title', _t('GridField.Filter', "Filter"))
						->setAttribute('id', 'action_filter_' . $gridField->getModelClass() . '_' . $columnField)
				);
				$fields->push(
						GridField_FormAction::create($gridField, 'reset', false, 'reset', null)
						->addExtraClass('ss-gridfield-button-close')
						->setAttribute('title', _t('GridField.ResetFilter', "Reset"))
						->setAttribute('id', 'action_reset_' . $gridField->getModelClass() . '_' . $columnField)
				);
				$fields->addExtraClass('filter-buttons');
				$fields->addExtraClass('no-change-track');
			}
	
			$forTemplate->Fields->push($fields);
		}
	
		return array(
			'header' => $forTemplate->renderWith('GridFieldFilterHeader_Row'),
		);
	}
	
	protected function getFieldName($name,  GridField $grid) {
		return sprintf('filter[%s][%s]', $grid->getName(), $name);
	}
}