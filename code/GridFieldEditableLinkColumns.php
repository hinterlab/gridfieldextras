<?php

/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 *
 */

class GridFieldEditableLinkColumns extends GridFieldEditableColumns {
	/**
	 * @var string
	 */
	protected $LinkColName = 'LinkID';
	
	public function __construct($LinkRelationshipColName = 'LinkID') {
		
		$this->LinkColName = $LinkRelationshipColName;
		$this->setDisplayFields();
	}
	
	/**
	 * get default display fields setting for editable links ( LinkField )
	 */
	protected function getLinkDisplayField(){
		
		$ColumName = $this->LinkColName;
		
		return array(
			$ColumName => function($record, $column, $grid) { return new LinkField($column); }
		);
	}
	
	
	/**
	 * setup dispaly fields.
	 * 
	 * and link field is always in display field.
	 */
	public function setDisplayFields($fields = array()) {
		
		if(!is_array($fields)) {
			throw new InvalidArgumentException('
				Arguments passed to GridFieldDataColumns::setDisplayFields() must be an array');
		}
		
		$LinkFieldSetting = $this->getLinkDisplayField();
		
		$fields = $LinkFieldSetting + $fields;
		
		parent::setDisplayFields($fields);
		
		return $this;
	}
	
	
	/**
	 * don't need to save editable columns data. 
	 * over written this function and leave it as empty. do nothing.
	 * 
	 * saving data here may cause unexpected bug (e.g increasing version number for versioned has_many dataobject while saving a page.). 
	 */
	public function handleSave(GridField $grid, DataObjectInterface $record) {
		
		
	}

}
