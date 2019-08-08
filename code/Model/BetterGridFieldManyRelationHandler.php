<?php

namespace Internetrix\GridFieldExtras\Model;

use GridFieldManyRelationHandler;
use GridFieldManyRelationHandler_HasManyList;
use GridFieldManyRelationHandler_ManyManyList;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\RelationList;
use InvalidArgumentException;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ManyManyList;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class BetterGridFieldManyRelationHandler extends GridFieldManyRelationHandler
{
    protected $exclude;

    public function __construct($useToggle = true, $segement = 'before', $exclude = null)
    {
        parent::__construct($useToggle, $segement);
        $this->cheatList = new GridFieldManyRelationHandler_HasManyList;
        $this->cheatManyList = new GridFieldManyRelationHandler_ManyManyList;
        $this->exclude = $exclude;
    }

    public function getManipulatedData(GridField $gridField, SS_List $list)
    {
        if (!$list instanceof RelationList) {
            user_error('GridFieldManyRelationHandler requires the GridField to have a RelationList. Got a ' . get_class($list) . ' instead.', E_USER_WARNING);
        }

        $state = $this->getState($gridField);

        // We don't use setupState() as we need the list
        if ($state->FirstTime) {
            $state->RelationVal = array_values($list->getIdList()) ?: array();
        }
        if (!$state->ShowingRelation && $this->useToggle) {
            return $list;
        }

        $query = clone $list->dataQuery();
        try {
            $query->removeFilterOn($this->cheatList->getForeignIDFilter($list));
        } catch (InvalidArgumentException $e) {
            /* NOP */
        }
        $orgList = $list;
        $list = new DataList($list->dataClass());
        $list = $list->setDataQuery($query);

        if ($orgList instanceof ManyManyList) {
            $list = new DataList($list->dataClass());
            $query = $list->dataQuery();

            $gridField->getConfig()->removeComponentsByType(GridFieldOrderableRows::class);
            $gridField->getConfig()->removeComponentsByType(GridFieldEditButton::class);
            $gridField->getConfig()->removeComponentsByType(GridFieldDeleteAction::class);
            // add a column for the filter controls
            $gridField->getConfig()->addComponent(new GridFieldDummyColumn());

            $joinTable = $this->cheatManyList->getJoinTable($orgList);
            $baseClass = DataObject::getSchema()->baseDataClass($list->dataClass());
            $baseClass = DataObject::getSchema()->tableName($baseClass);

            $localKey = $this->cheatManyList->getLocalKey($orgList);
            $query->leftJoin($joinTable, "\"$joinTable\".\"$localKey\" = \"$baseClass\".\"ID\"");
            $list = $list->setDataQuery($query);

            if ($this->exclude) {
                $list = $list->exclude($this->exclude);
            }
        }
        return $list;
    }

    protected function toggleGridRelation(GridField $gridField, $arguments, $data)
    {
        parent::toggleGridRelation($gridField, $arguments, $data);

        //we have to reset pagination.
        $PaginationState = $gridField->State->GridFieldPaginator;
        $PaginationState->currentPage = 1;
    }

    protected function cancelGridRelation(GridField $gridField, $arguments, $data)
    {
        parent::cancelGridRelation($gridField, $arguments, $data);

        //we have to reset pagination.
        $PaginationState = $gridField->State->GridFieldPaginator;
        $PaginationState->currentPage = 1;
    }

    protected function saveGridRelation(GridField $gridField, $arguments, $data)
    {
        parent::saveGridRelation($gridField, $arguments, $data);

        //we have to reset pagination.
        $PaginationState = $gridField->State->GridFieldPaginator;
        $PaginationState->currentPage = 1;
    }

    public function setExclude($exclude)
    {
        $this->exclude = $exclude;
        return $this;
    }

    public function getExclude()
    {
        return $this->exclude;
    }
}
