<?php

class BetterGridFieldManyRelationHandler extends GridFieldManyRelationHandler{
	
	protected $exclude;
	
	public function __construct($useToggle = true, $segement = 'before', $exclude = null) {
		parent::__construct($useToggle, $segement);
		$this->cheatList 	 = new GridFieldManyRelationHandler_HasManyList;
		$this->cheatManyList = new GridFieldManyRelationHandler_ManyManyList;
		$this->exclude 		 = $exclude;
	}
	

	public function getManipulatedData(GridField $gridField, SS_List $list) {
		if(!$list instanceof RelationList) {
			user_error('GridFieldManyRelationHandler requires the GridField to have a RelationList. Got a ' . get_class($list) . ' instead.', E_USER_WARNING);
		}

		$state = $this->getState($gridField);

		// We don't use setupState() as we need the list
		if($state->FirstTime) {
			$state->RelationVal = array_values($list->getIdList()) ?: array();
		}
		if(!$state->ShowingRelation && $this->useToggle) {
			return $list;
		}

		$query = clone $list->dataQuery();
		try {
			$query->removeFilterOn($this->cheatList->getForeignIDFilter($list));
		} catch(InvalidArgumentException $e) { /* NOP */ }
		$orgList = $list;
		$list = new DataList($list->dataClass());
		$list = $list->setDataQuery($query);
		if($orgList instanceof ManyManyList) {
			
			//fixing duplicated objects which is caused by 'DISTINCT "Page_Items"."Sort"'
			$list = new DataList($list->dataClass());
			$query = $list->dataQuery();
			
			//hide drag and drop function
			if(class_exists('GridFieldOrderableRows')){
				$gridField->getConfig()->removeComponentsByType('GridFieldOrderableRows');
			}
			
			$joinTable = $this->cheatManyList->getJoinTable($orgList);
			$baseClass = ClassInfo::baseDataClass($list->dataClass());
			$localKey = $this->cheatManyList->getLocalKey($orgList);
			$query->leftJoin($joinTable, "\"$joinTable\".\"$localKey\" = \"$baseClass\".\"ID\"");
			$list = $list->setDataQuery($query);
			
			if($this->exclude){
				$list = $list->exclude($this->exclude);
			}
			
		}
		return $list;
	}
	
	
	protected function toggleGridRelation(GridField $gridField, $arguments, $data) {
		parent::toggleGridRelation($gridField, $arguments, $data);
		
		//we have to reset pagination.
		$PaginationState = $gridField->State->GridFieldPaginator;
		$PaginationState->currentPage = 1;
	}
	
	protected function cancelGridRelation(GridField $gridField, $arguments, $data) {
		parent::cancelGridRelation($gridField, $arguments, $data);

		//we have to reset pagination.
		$PaginationState = $gridField->State->GridFieldPaginator;
		$PaginationState->currentPage = 1;
	}

	protected function saveGridRelation(GridField $gridField, $arguments, $data) {
		parent::saveGridRelation($gridField, $arguments, $data);

		//we have to reset pagination.
		$PaginationState = $gridField->State->GridFieldPaginator;
		$PaginationState->currentPage = 1;
	}

}
