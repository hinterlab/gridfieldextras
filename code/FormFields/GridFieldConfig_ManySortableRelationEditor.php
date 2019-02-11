<?php

namespace Internetrix\GridFieldExtras\FormFields;

use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use Internetrix\GridFieldExtras\Model\BetterGridFieldManyRelationHandler;

class GridFieldConfig_ManySortableRelationEditor extends GridFieldConfig {
	/**
	 *
	 * @param int $itemsPerPage - How many items per page should show up
	 */
	public function __construct($itemsPerPage=null,$obj=null) {
		
		$this->addComponent(new GridFieldButtonRow('before'));
		$this->addComponent(new GridFieldToolbarHeader());
		$this->addComponent(new GridFieldAddNewButton('toolbar-header-right'));
		if ($obj && $obj->ID) {
		    $this->addComponent(new GridFieldSortableRows('Sort'));
			$this->addComponent($bb = new BetterGridFieldManyRelationHandler(true,'toolbar-header-right'));
			$bb->setButtonTitle('TOGGLE_RELATION', 'Choose existing...');
		}		
//		$this->addComponent(new GridFieldAddExistingAutocompleter('toolbar-header-right'));
		$this->addComponent($sort = new GridFieldSortableHeader());
		$this->addComponent(new GridFieldDataColumns());
		$this->addComponent(new GridFieldEditButton());
		$this->addComponent(new GridFieldDeleteAction(true));
//		$this->addComponent(new GridFieldPageCount('toolbar-header-right'));
		$this->addComponent($filter = new GridFieldFilterHeader());
		$this->addComponent($pagination = new GridFieldPaginator($itemsPerPage));
		$this->addComponent(new GridFieldDetailForm());

		$sort->setThrowExceptionOnBadDataType(false);
		$filter->setThrowExceptionOnBadDataType(false);
		$pagination->setThrowExceptionOnBadDataType(false);
	}
}