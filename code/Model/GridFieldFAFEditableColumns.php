<?php

namespace Internetrix\GridFieldExtras\Model;

use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Control\Controller;

class GridFieldFAFEditableColumns extends GridFieldEditableColumns{
	
	/**
	 * Almost an exact copy of GridFieldEditableColumns::getForm()
	 * Uses Form::MERGE_IGNORE_FALSEISH when calling $form->loadDataFrom()
	 *
	 * @param GridField $grid
	 * @param DataObjectInterface $record
	 * @return Form
	 */
	public function getForm(GridField $grid, DataObjectInterface $record) {
		$fields = $this->getFields($grid, $record);
	
		$form = new Form($this, null, $fields, new FieldList());
		$form->loadDataFrom($record, Form::MERGE_IGNORE_FALSEISH);
	
		$form->setFormAction(Controller::join_links(
			$grid->Link(), 'editable/form', $record->ID
		));
	
		return $form;
	}
}
