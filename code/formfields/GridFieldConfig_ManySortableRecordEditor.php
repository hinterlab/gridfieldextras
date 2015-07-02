<?php
class GridFieldConfig_ManySortableRecordEditor extends GridFieldConfig {
	/**
	 *
	 * @param int $itemsPerPage - How many items per page should show up
	 */
	public function __construct($itemsPerPage=null,$obj=null) {

		$this->addComponent(new GridFieldButtonRow('before'));
		$this->addComponent(new GridFieldToolbarHeader());
		$this->addComponent(new GridFieldAddNewButton('toolbar-header-right'));
		$this->addComponent($sort = new GridFieldSortableHeader());
		$this->addComponent(new GridFieldDataColumns());
		$this->addComponent(new GridFieldEditButton());
		$this->addComponent(new GridFieldDeleteAction());
//		$this->addComponent(new GridFieldPageCount('toolbar-header-right'));
		if ($obj && $obj->ID) {
		    $this->addComponent(new GridFieldOrderableRows('Sort'));
		}
		$this->addComponent($pagination = new GridFieldPaginator($itemsPerPage));
		$this->addComponent(new GridFieldDetailForm());

		$sort->setThrowExceptionOnBadDataType(false);
		$pagination->setThrowExceptionOnBadDataType(false);
	}
}