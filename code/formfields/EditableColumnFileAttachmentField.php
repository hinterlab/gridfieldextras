<?php

class EditableColumnFileAttachmentField extends FileAttachmentField{
	
	/*
	 * Almost an exact copy of parent::FieldHolder()
	 * Uses editablecolumnsdropzone.js instead of dropzone.js
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
	
	/*
	 * Almost an exact copy of FormField::extraClass()
	 * Doesnt add Type() to the class array
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
	
	/*
	 * Render FieldHolder instead of Field. This ensures the js and css is included
	 */
	public function forTemplate() {
		return $this->FieldHolder();
	}

}
