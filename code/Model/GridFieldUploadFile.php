<?php
/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 *
 * A component which lets the user select an upload folder and then upload a file
 *
 */

namespace Internetrix\GridFieldExtras\Model;

use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\Forms\GridField\GridField;
use GridFieldUploadManyFileHandler;
use SilverStripe\View\Requirements;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Assets\Folder;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;

class GridFieldUploadFile implements GridField_HTMLProvider, GridField_URLHandler {

	private static $allowed_actions = array(
		'handleUpload'
	);

	private $fragment;
	private $title;
	private $classes;
	private $folderName;

	/**
	 * @param string $fragment the fragment to render the button in
	 */
	public function __construct($fragment = 'before') {
		$this->setFragment($fragment);
		$this->setTitle('Upload');
	}

	/**
	 * Gets the foldername a file is uploaded to
	 *
	 * @return string
	 */
	public function getFolderName() {
		return $this->folderName;
	}

	/**
	 * Sets the default folder a file is uploaded to
	 *
	 * @param string $folderName
	 * @return GridFieldUploadFile $this
	 */
	public function setFolderName($folderName) {
		$this->folderName = $folderName;
		return $this;
	}
	
	/**
	 * Gets the fragment name this button is rendered into.
	 *
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment;
	}
	
	/**
	 * Sets the fragment name this button is rendered into.
	 *
	 * @param string $fragment
	 * @return GridFieldUploadFile $this
	 */
	public function setFragment($fragment) {
		$this->fragment = $fragment;
		return $this;
	}

	/**
	 * Gets the button title text.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the button title text.
	 *
	 * @param string $title
	 * @return GridFieldUploadFile $this
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Handles uploading a file into the selected folder
	 *
	 * @param GridField $grid
	 * @param SS_HTTPRequest $request
	 * @return GridFieldUploadFileHandler
	 */
	public function handleUpload(GridField $grid, $request) {
		$controller = $grid->getForm()->Controller();
		$handler 	= GridFieldUploadManyFileHandler::create($grid, $this, $controller, 'upload-file');
		
		return $handler->handleRequest($request/*, DataModel::inst()*/);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHTMLFragments($grid) {
		Requirements::javascript(GRIDFIELDEXTRAS_DIR . '/javascript/gridfieldextras.js');

		$folderField	 = $this->uploadForm($grid)->Fields()->dataFieldByName('GridFieldUploadFile[FolderID]');
		$data 			 = ArrayData::create(array(
			'Title'      => $this->getTitle(),
			'Link'       => Controller::join_links($grid->Link(), 'upload-file', '{folderid}'),
			'ClassField' => $folderField
		));

		return array(
			$this->getFragment() => $data->renderWith('GridFieldUploadFile')
		);
	}
	
	public function uploadForm(GridField $grid, $request = null) {
		$field 	= TreeDropdownField::create('GridFieldUploadFile[FolderID]', '', 'Folder')->addExtraClass('no-change-track');
		
		if($folderName = $this->getFolderName()){
			$defaultFolder = Folder::find_or_make($folderName);
			$field->setValue($defaultFolder->ID);
		}
		
		$form 	= Form::create($this, null, FieldList::create($field), FieldList::create());
		
		$form->setFormAction($grid->Link('GridFieldUploadFile/form'));
	
		return $form;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getURLHandlers($grid) {
		return array(
			'GridFieldUploadFile/form' 	=> 'uploadForm',
			'upload-file/$FolderID!' 	=> 'handleUpload'
		);
	}
}
