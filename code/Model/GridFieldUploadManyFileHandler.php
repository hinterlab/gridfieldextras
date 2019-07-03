<?php
/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 *
 * A custom grid field request handler that allows interacting with form fields when uploading files.
 * 
 */

namespace Internetrix\GridFieldExtras\Model;

use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use Internetrix\GridFieldExtras\Extensions\RelationAttachUploadField;
use SilverStripe\Assets\Folder;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_URLHandler;

class GridFieldUploadManyFileHandler extends RequestHandler {
	
	protected $gridField;
	protected $component;
	protected $controller;
	
	private static $allowed_actions = array(
		'uploadManyForm',
        'doSave'
	);
	
	private static $url_handlers = array(
		'$Action!' => '$Action'
	);
	
	/**
	 *
	 * @param GridField $gridField
	 * @param GridField_URLHandler $component
	 * @param Controller $controller
	 */
	public function __construct($gridField, $component, $controller){
		$this->gridField  = $gridField;
		$this->component  = $component;
		$this->controller = $controller;
		parent::__construct();
	}
	
	public function index($request)
    {
		$controller = $this->getToplevelController();
		$form 		= $this->uploadManyForm();
		$form->setTemplate([
            'type' => 'Includes',
            'SilverStripe\\Admin\\LeftAndMain_EditForm',
        ]);
        $form->addExtraClass('cms-content cms-edit-form center fill-height flexbox-area-grow');
        $form->setAttribute('data-pjax-fragment', 'CurrentForm Content');

		
		$crumbs = $this->Breadcrumbs();
		$page = $crumbs->offsetGet($crumbs->count()-2);
		$form->Backlink = $page->Link;
		
		$form->Actions()->push(
		    LiteralField::create('BackLink', '<a href="'. $form->Backlink .'" class="btn backlink ss-ui-button cms-panel-link" data-icon="back">Back</a>')
        );
		$form->Actions()->push(FormAction::create('doSave', 'Save')->addExtraClass('btn btn-primary'));
		
		if($this->request->isAjax()){
			$response = new HTTPResponse(Convert::raw2json(array('Content' => $form->forAjaxTemplate()->getValue())));
			$response->addHeader('X-Pjax', 'Content');
			$response->addHeader('Content-Type', 'text/json');
			$response->addHeader('X-Title', 'SilverStripe - Upload Files');
			return $response;
		}else {
			return $controller->customise(array( 'Content' => $form));
		}
	}

	public function doSave($data, Form $form)
    {

        if($data['ReplacementFile'] && $data['ReplacementFile']['Files']) {
            foreach ($data['ReplacementFile']['Files'] as $id) {
                $this->gridField->getList()->add($id);
            }
        }

        $message = 'Saved your file selection';
        $form->sessionMessage($message, 'good', ValidationResult::CAST_HTML);
        // Redirect after save
        return $this->redirectAfterSave();
    }

    protected function redirectAfterSave()
    {
        $controller = $this->getToplevelController();
        $crumbs = $this->Breadcrumbs();
        $page = $crumbs->offsetGet($crumbs->count()-2);
        $controller->getRequest()->addHeader('X-Pjax', 'Content');
        return $controller->redirect($page->Link, 302);
    }
	
	public function uploadManyForm()
    {
		$uploadField = RelationAttachUploadField::create('ReplacementFile', '')->setList($this->gridField->getList());
		$folder = Folder::get()->byID($this->getRequest()->param('FolderID'));
	
		if($folder && $folder->exists() && $folder->getFilename()) {
			$path = preg_replace('/^' . ASSETS_DIR . '\//', '', $folder->getFilename());
			$uploadField->setFolderName($path);
		} else {
			$uploadField->setFolderName('/');
		}
	
		$exts = $uploadField->getValidator()->getAllowedExtensions();
		asort($exts);
		$uploadField->Extensions = implode(', ', $exts);
	
		return Form::create($this, 'uploadManyForm', FieldList::create($uploadField), FieldList::create());
	}
	
	public function Breadcrumbs($unlinked = false){
		if(!$this->controller->hasMethod('Breadcrumbs')) return;
	
		$items = $this->controller->Breadcrumbs($unlinked);
		$items->push(new ArrayData(array(
			'Title' => 'Upload',
			'Link' => false
		)));
		return $items;
	}
	
	protected function getToplevelController() {
		$c = $this->controller;
		while($c && $c instanceof GridFieldDetailForm_ItemRequest) {
			$c = $c->getController();
		}
		return $c;
	}
	
	public function Link($action = null) {
		return Controller::join_links($this->gridField->Link(), 'upload-file', $this->getRequest()->param('FolderID'));
	}
	
}
