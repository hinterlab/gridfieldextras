<?php

/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 *
 */

class GridFieldDummyColumn implements GridField_ColumnProvider {
	
	/**
	 * Add a column
	 * 
	 * @param type $gridField
	 * @param array $columns 
	 */
	public function augmentColumns($gridField, &$columns) {
		if(!in_array('TestActions', $columns))
			$columns[] = 'TestActions';
	}
	

	public function getColumnAttributes($gridField, $record, $columnName) {
		$defaultAtts = array('class' => 'col-buttons');
		return $defaultAtts;
	}
	
	/**
	 * Add the title 
	 * 
	 * @param GridField $gridField
	 * @param string $columnName
	 * @return array
	 */
	public function getColumnMetadata($gridField, $columnName) {
		if($columnName == 'TestActions') {
			return array('title' => '');
		}
	}
	
	/**
	 * Which columns are handled by this component
	 * 
	 * @param type $gridField
	 * @return type 
	 */
	public function getColumnsHandled($gridField) {
		return array('TestActions');
	}
	
	/**
	 * @param GridField $gridField
	 * @param DataObject $record
	 * @param string $columnName
	 *
	 * @return string - the HTML for the column 
	 */
	public function getColumnContent($gridField, $record, $columnName) {
		return null;
		$data = new ArrayData(array(
			'Link' => Controller::join_links($gridField->Link('item'), $record->ID, 'edit')
		));
		return $data->renderWith('GridField_DummyColumn');
	}
}
