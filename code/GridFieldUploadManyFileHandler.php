<?php
/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 *
 * A custom grid field request handler that allows interacting with form fields when uploading files.
 * 
 */
class GridFieldUploadManyFileHandler extends RequestHandler {
	
	protected $gridField;
	protected $component;
	protected $controller;
	
	private static $allowed_actions = array(
		'uploadManyForm'
	);
	
	private static $url_handlers = array(
		'$Action!' => '$Action'
	);
	
	/**
	 *
	 * @param GridFIeld $gridField
	 * @param GridField_URLHandler $component
	 * @param Controller $controller
	 */
	public function __construct($gridField, $component, $controller){
		$this->gridField  = $gridField;
		$this->component  = $component;
		$this->controller = $controller;
		parent::__construct();
	}
	
	public function index($request) {
		Requirements::javascript(FRAMEWORK_DIR . '/javascript/AssetUploadField.js');
		Requirements::css(FRAMEWORK_DIR . '/css/AssetUploadField.css');
		
		$controller = $this->getToplevelController();
		$form 		= $this->uploadManyForm();
		$form->setTemplate('LeftAndMain_EditForm');
		$form->addExtraClass('center cms-content');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm Content');
		
		$crumbs 		= $this->Breadcrumbs();
		$page 			= $crumbs->offsetGet($crumbs->count()-2);
		$form->Backlink = $page->Link;
		
		$form->Fields()->push(LiteralField::create('BackLink', '<a href="'. $form->Backlink .'" class="backlink ss-ui-button cms-panel-link" data-icon="back">Back</a>'));
		
		if($this->request->isAjax()){
			$response = new SS_HTTPResponse(Convert::raw2json(array('Content' => $form->forAjaxTemplate()->getValue())));
			$response->addHeader('X-Pjax', 'Content');
			$response->addHeader('Content-Type', 'text/json');
			$response->addHeader('X-Title', 'SilverStripe - Upload Files');
			return $response;
		}else {
			return $controller->customise(array( 'Content' => $form));
		}
	}
	
	public function uploadManyForm(){
		$uploadField = RelationAttachUploadField::create('ReplacementFile', '')->setList($this->gridField->getList());
		$folder 	 = Folder::get()->byID($this->getRequest()->param('FolderID'));
	
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
