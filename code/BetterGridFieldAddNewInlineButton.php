<?php

class BetterGridFieldAddNewInlineButton extends GridFieldAddNewInlineButton {
	
	public function getHTMLFragments($grid) {
		if($grid->getList() && !singleton($grid->getModelClass())->canCreate()) {
			return array();
		}
	
		$fragment = $this->getFragment();
	
		if(!$editable = $grid->getConfig()->getComponentByType('GridFieldEditableColumns')) {
			throw new Exception('Inline adding requires the editable columns component');
		}
	
		Requirements::javascript(THIRDPARTY_DIR . '/javascript-templates/tmpl.js');
		GridFieldExtensions::include_requirements();
		
		
		$list = $grid->getList();
		if($list) {
			$record = Object::create($grid->getModelClass());
		
			if($record && $record->hasField('Sort')){
				Requirements::javascript(GRIDFIELDEXTRAS_DIR.'/javascript/addinlinewithsort.js');
				$grid->setAttribute('data-add-inline-sort', $list->max('Sort') + 1);
			}
		}
		
		$data = new ArrayData(array(
			'Title'  => $this->getTitle(),
		));
	
		return array(
			$fragment => $data->renderWith('GridFieldAddNewInlineButton'),
			'after'   => $this->getRowTemplate($grid, $editable)
		);
	}
	
	public function getRowTemplate(GridField $grid, GridFieldEditableColumns $editable) {
		$columns = new ArrayList();
		$handled = array_keys($editable->getDisplayFields($grid));
	
		if($grid->getList()) {
			$record = Object::create($grid->getModelClass());
		} else {
			$record = null;
		}
	
		$fields = $editable->getFields($grid, $record);
	
		foreach($grid->getColumns() as $column) {
			if(in_array($column, $handled)) {
				$field = $fields->fieldByName($column);
				$field->setName(sprintf(
					'%s[%s][{%%=o.num%%}][%s]', $grid->getName(), 'GridFieldAddNewInlineButton', $field->getName()
				));
				
				if($column == 'Sort'){
					$field->setValue('{%=o.sort%}');
				}
	
				$content = $field->Field();
			} else {
				$content = null;
			}
	
			$attrs = '';
	
			foreach($grid->getColumnAttributes($record, $column) as $attr => $val) {
				$attrs .= sprintf(' %s="%s"', $attr, Convert::raw2att($val));
			}
			
			$content = str_replace('o-num', '{%=o.num%}', $content);
	
			$columns->push(new ArrayData(array(
				'Content'    => $content,
				'Attributes' => $attrs,
				'IsActions'  => $column == 'Actions'
			)));
		}
	
		return $columns->renderWith('GridFieldAddNewInlineRow');
	}

}