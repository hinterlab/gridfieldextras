<?php
/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 * 
 * This component should be used when versioned objects (stage, live) are being sorted in a model admin. 
 * The sort order will be updated on both the stage and live tables
 *
 */
class VersionedGridFieldSortableRows extends GridFieldSortableRows {
	protected $saveToLive;
	
	/**
	 * @param String $sortColumn Column that should be used to update the sort information
	 */
	public function __construct($sortColumn, $saveToLive = false) {
		$this->saveToLive = $saveToLive;
		parent::__construct($sortColumn);
	}
	
	/**
	 * Handles saving of the row sort order
	 * @param GridField $gridField Grid Field Reference
	 * @param Array $data Data submitted in the request
	 */
	protected function saveGridRowSort(GridField $gridField, $data) {
		$dataList = $gridField->getList();
	
		if(class_exists('UnsavedRelationList') && $dataList instanceof UnsavedRelationList) {
			user_error('Cannot sort an UnsavedRelationList', E_USER_ERROR);
			return;
		}
	
		if(!singleton($gridField->getModelClass())->canEdit()){
			throw new ValidationException(_t('GridFieldSortableRows.EditPermissionsFailure', "No edit permissions"),0);
		}
	
		if (empty($data['ItemIDs'])) {
			user_error('No items to sort', E_USER_ERROR);
		}
	
		$className = $gridField->getModelClass();
		$owner = $gridField->Form->getRecord();
		$items = clone $gridField->getList();
		$many_many = ($items instanceof ManyManyList);
		$sortColumn = $this->sortColumn;
		$pageOffset = 0;
	
		if ($paginator = $gridField->getConfig()->getComponentsByType('GridFieldPaginator')->First()) {
			$pageState = $gridField->State->GridFieldPaginator;
				
			if($pageState->currentPage && is_int($pageState->currentPage) && $pageState->currentPage>1) {
				$pageOffset = $paginator->getItemsPerPage() * ($pageState->currentPage - 1);
			}
		}
	
	
		if ($many_many) {
			list($parentClass, $componentClass, $parentField, $componentField, $table) = $owner->many_many($gridField->getName());
		}else {
			//Find table containing the sort column
			$table=false;
			$class=$gridField->getModelClass();
			$db = Config::inst()->get($class, "db", CONFIG::UNINHERITED);
			if(!empty($db) && array_key_exists($sortColumn, $db)) {
				$table=$class;
			}else {
				$classes=ClassInfo::ancestry($class, true);
				foreach($classes as $class) {
					$db = Config::inst()->get($class, "db", CONFIG::UNINHERITED);
					if(!empty($db) && array_key_exists($sortColumn, $db)) {
						$table=$class;
						break;
					}
				}
			}
				
			if($table===false) {
				user_error('Sort column '.$this->sortColumn.' could not be found in '.$gridField->getModelClass().'\'s ancestry', E_USER_ERROR);
				exit;
			}
				
			$baseDataClass=ClassInfo::baseDataClass($gridField->getModelClass());
		}
	
	
		//Event to notify the Controller or owner DataObject before list sort
		if($owner && $owner instanceof DataObject && method_exists($owner, 'onBeforeGridFieldRowSort')) {
			$owner->onBeforeGridFieldRowSort(clone $items);
		}else if(Controller::has_curr() && Controller::curr() instanceof ModelAdmin && method_exists(Controller::curr(), 'onBeforeGridFieldRowSort')) {
			Controller::curr()->onBeforeGridFieldRowSort(clone $items);
		}
	
	
		//Start transaction if supported
		if(DB::getConn()->supportsTransactions()) {
			DB::getConn()->transactionStart();
		}
	
	
		//Perform sorting
		$ids = explode(',', $data['ItemIDs']);
		for($sort = 0;$sort<count($ids);$sort++) {
			$id = intval($ids[$sort]);
			if ($many_many) {
				DB::query('UPDATE "' . $table 
				. '" SET "' . $sortColumn.'" = ' . (($sort + 1) + $pageOffset)
				. ' WHERE "' . $componentField . '" = ' . $id . ' AND "' . $parentField . '" = ' . $owner->ID);
				if($this->saveToLive){
					//now lets also up the live table
					DB::query('UPDATE "' . $table . '_Live'
					. '" SET "' . $sortColumn.'" = ' . (($sort + 1) + $pageOffset)
					. ' WHERE "' . $componentField . '" = ' . $id . ' AND "' . $parentField . '" = ' . $owner->ID);
				}
			} else {
				DB::query('UPDATE "' . $table
				. '" SET "' . $sortColumn . '" = ' . (($sort + 1) + $pageOffset)
				. ' WHERE "ID" = '. $id);
	
				DB::query('UPDATE "' . $baseDataClass
				. '" SET "LastEdited" = \'' . date('Y-m-d H:i:s') . '\''
						. ' WHERE "ID" = '. $id);
				
				if($this->saveToLive){
					//now lets also up the live table
					
					DB::query('UPDATE "' . $table . '_Live'
					. '" SET "' . $sortColumn . '" = ' . (($sort + 1) + $pageOffset)
					. ' WHERE "ID" = '. $id);
					
					DB::query('UPDATE "' . $baseDataClass . '_Live'
					. '" SET "LastEdited" = \'' . date('Y-m-d H:i:s') . '\''
							. ' WHERE "ID" = '. $id);
				}
			}
		}
	
	
		//End transaction if supported
		if(DB::getConn()->supportsTransactions()) {
			DB::getConn()->transactionEnd();
		}
	
	
		//Event to notify the Controller or owner DataObject after list sort
		if($owner && $owner instanceof DataObject && method_exists($owner, 'onAfterGridFieldRowSort')) {
			$owner->onAfterGridFieldRowSort(clone $items);
		}else if(Controller::has_curr() && Controller::curr() instanceof ModelAdmin && method_exists(Controller::curr(), 'onAfterGridFieldRowSort')) {
			Controller::curr()->onAfterGridFieldRowSort(clone $items);
		}
	}
}
?>
