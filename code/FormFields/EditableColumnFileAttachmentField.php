<?php

namespace Internetrix\GridFieldExtras\FormFields;

use UncleCheese\Dropzone\FileAttachmentField;
use SilverStripe\View\Requirements;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\Control\Controller;
use SilverStripe\Assets\File;

class EditableColumnFileAttachmentField extends FileAttachmentField {
	
	/**
	 * Telling the field whether to save into a has_one relationship or into the has_one inside another relationship
	 * @var boolean
	 */
	protected $saveIntoRelationshipObject = false;
	
	/**
	 * The name of the field the file is to be saved into
	 * @var string
	 */
	protected $saveIntoRelationshipField;
	
	/**
	 * The name of the parent relationship
	 * @var string
	 */
	protected $parentRelationshipName;
	
	/**
	 * Sets the name of the field the file is to be saved into
	 * @param int boolean
	 * @return  EditableColumnFileAttachmentField
	 */
	public function setSaveIntoRelationshipField($saveIntoRelationshipField){
		$this->permissions['detach'] = false;
		$this->saveIntoRelationshipField = $saveIntoRelationshipField;
		$this->setSaveIntoRelationshipObject(true);
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getSaveIntoRelationshipField(){
		return $this->saveIntoRelationshipField;
	}
	
	/**
	 * Sets whether to save into a has_one relationship or into the has_one inside another relationship
	 * @param int boolean
	 * @return  EditableColumnFileAttachmentField
	 */
	public function setSaveIntoRelationshipObject($saveIntoRelationshipObject){
		$this->saveIntoRelationshipObject = $saveIntoRelationshipObject;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function getSaveIntoRelationshipObject(){
		return $this->saveIntoRelationshipObject;
	}
	
	public function setParentRelationshipName($parentRelationshipName){
		$this->parentRelationshipName = $parentRelationshipName;
		return $this;
	}
	
	public function getParentRelationshipName(){
		return $this->parentRelationshipName;
	}
	
	/**
	 * Almost an exact copy of parent::FieldHolder()
	 * Uses editablecolumnsdropzone.js instead of dropzone.js
	 * 
	 * @param array $attributes [description]
     * @return  SSViewer
	 */
	public function FieldHolder($attributes = array ()) {
		Requirements::javascript(GRIDFIELDEXTRAS_DIR.'/javascript/editablecolumnsdropzone.js');
		Requirements::javascript(DROPZONE_DIR.'/javascript/file_attachment_field.js');
		if($this->isCMS()) {
			Requirements::javascript(DROPZONE_DIR.'/javascript/file_attachment_field_backend.js');
		}
		Requirements::css(DROPZONE_DIR.'/css/file_attachment_field.css');
	
		if(!$this->getSetting('url')) {
			$this->settings['url'] = $this->Link('upload');
		}
	
		if(!$this->getSetting('maxFilesize')) {
			$this->settings['maxFilesize'] = static::get_filesize_from_ini();
		}
		// The user may not have opted into a multiple upload. If the form field
		// is attached to a record that has a multi relation, set that automatically.
		$this->settings['uploadMultiple'] = $this->IsMultiple();
	
		// Auto filter images if assigned to an Image relation
		if($class = $this->getFileClass()) {
			if(Injector::inst()->get($class) instanceof Image) {
				$this->imagesOnly();
			}
		}
	
		if($token = $this->getForm()->getSecurityToken()) {
			$this->addParam($token->getName(), $token->getSecurityID());
		}
	
	
		$context = $this;
		
		if(count($attributes)) {
			$context = $this->customise($attributes);
		}
		
		return $context->renderWith($this->getFieldHolderTemplates());
	}
	
	public function Field($properties = array()){
		return $this->FieldHolder($properties);
	}

	/**
	 * Saves the field into a record
	 * @param  DataObjectInterface $record
	 * @return FileAttachmentField
	 */
	public function saveInto(DataObjectInterface $record) {
		$fieldname = $this->getName();
		if(!$fieldname) return $this;
		$value = $this->Value();
	
		// Handle deletions. This is a bit of a hack. A workaround for having a single form field
		// post two params.
		$deletions = Controller::curr()->getRequest()->postVar('__deletion__'.$this->getName());
		
		if($deletions) {
			foreach($deletions as $id) {
				$this->deleteFileByID($id);
			}
		}
		
		if($this->getSaveIntoRelationshipObject()){
			
			$relationClass = $record->getRelationClass($fieldname);
			$relationshipField = $this->getSaveIntoRelationshipField();
			
			$parentRelationshipName = $this->getParentRelationshipName();
			if(!$parentRelationshipName){
				$parentRelationshipName = $record->ClassName.'s';
			}
			
			//first delete the relationship objects
			$deletions = Controller::curr()->getRequest()->postVar('__deletion__'.$parentRelationshipName);

			if($deletions) {
				foreach($deletions as $id) {
					if(is_array($id) && isset($id[$record->ID][$fieldname])){
						foreach($id[$record->ID][$fieldname] as $id) {
							$this->deleteRelationshipObjectAndFileByID($record, $id, $relationClass, $relationshipField);
						}	
					}
				}
			}
			
			if($relationClass && $relationshipField){
				
				if(is_array($value)){
					foreach($value as $k => $v){
						if(is_numeric($v)){
							$obj = $relationClass::get()->filter($relationshipField, $v)->first();
							if(!$obj){
								$obj = $relationClass::create();
								
								$this->setRelationValue(false, $v, $obj, $relationshipField);
								
								$file = File::get()->byID($v);
								if($file){
									if ($obj->hasDatabaseField('Title')) {
										$obj->Title = $file->Title;
										$obj->write();
									} elseif ($obj->hasDatabaseField('Name')) {
										$obj->Name = $file->Title;
										$obj->write();
									}
									$obj->write();
								}
								
								$record->$fieldname()->add($obj);
							}
						}
					}
				}
			}
		}else{
			$relation = $this->getRelation();
			$this->setRelationValue($relation, $this->Value(), $record, $fieldname);
		}
	
		return $this;
	}
	
	public function setRelationValue($relation, $value, $record, $fieldname){
		if($relation) {
			$relation->setByIDList($value);
		} elseif($record->has_one($fieldname)) {
			$record->{"{$fieldname}ID"} = $value ?: 0;
		} elseif($record->hasField($fieldname)) {
			$record->$fieldname = is_array($value) ? implode(',', $value) : $value;
		}
	}
	
	
	protected function deleteRelationshipObjectAndFileByID($record, $id, $relationClass, $relationshipField) {
		
		if($this->CanDelete() && ($fieldname = $this->getName()) ) {
			
			$obj = $record->$fieldname()->filter($relationshipField, $id)->first();
			
			if($obj){
				
				$pos = strpos($relationshipField, 'ID');
				if($pos !== false){
					$relationshipField = substr($relationshipField, 0, $pos);
				}
				
				$file = $obj->getComponent($relationshipField);
				
				if($file && $file->exists() && $file->canDelete()){
					$file->delete();
				}
				
				if($obj->canDelete()){
					$obj->delete();
					return true;
				}
			}
			
		}
	
		return false;
	}

	
	/**
	 * Almost an exact copy of FormField::extraClass()
	 * Doesnt add Type() to the class array
	 * 
	 * @return string
	 */
	public function extraClass() {
		$classes = array();
	
		$classes[] = 'fileattachment';
	
		if($this->extraClasses) {
			$classes = array_merge(
				$classes,
				array_values($this->extraClasses)
			);
		}
	
		if(!$this->Title()) {
			$classes[] = 'nolabel';
		}
	
		// Allow custom styling of any element in the container based on validation errors,
		// e.g. red borders on input tags.
		//
		// CSS class needs to be different from the one rendered through {@link FieldHolder()}.
		if($this->Message()) {
			$classes[] .= 'holder-' . $this->MessageType();
		}
	
		return implode(' ', $classes);
	}
	
	/**
	 * Render FieldHolder instead of Field. This ensures the js and css is included
	 */
	public function forTemplate() {
		return $this->FieldHolder();
	}

}
