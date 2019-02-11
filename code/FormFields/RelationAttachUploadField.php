<?php

/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 *
 */

namespace Internetrix\GridFieldExtras\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\DataList;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;

class RelationAttachUploadField extends UploadField {
	
	protected $list;
	
	private static $allowed_actions = [
		'upload'
	];
	
	public function __construct($name, $title = null, SS_List $items = null) {
	
		parent::__construct($name, $title, $items);
	
		$this->setConfig('previewMaxWidth', 40);
		$this->setConfig('previewMaxHeight', 30);
		$this->addExtraClass('ss-assetuploadfield');
		$this->removeExtraClass('ss-uploadfield');
		$this->setTemplate('AssetUploadField');
		$this->setOverwriteWarning(false);
	}
	
	public function setList(DataList $list){
		$this->list = $list;
		return $this;
	}
	
	public function getList(){
		return $this->list;
	}
	
	public function upload(HTTPRequest $request) {
		if($this->isDisabled() || $this->isReadonly() || !$this->canUpload()) {
			return $this->httpError(403);
		}
	
		// Protect against CSRF on destructive action
		$token = $this->getForm()->getSecurityToken();
		if(!$token->checkRequest($request)) return $this->httpError(400);
	
		// Get form details
		$name = $this->getName();
		$postVars = $request->postVar($name);
	
		// Save the temporary file into a File object
		$uploadedFiles = $this->extractUploadedFileData($postVars);
		$firstFile = reset($uploadedFiles);
		$file = $this->saveTemporaryFile($firstFile, $error);
		if(empty($file)) {
			$return = array('error' => $error);
		} else {
			if($this->list instanceof DataList){
				$this->list->add($file);
			}
			$return = $this->encodeFileAttributes($file);
		}
	
		// Format response with json
		$response = new HTTPResponse(Convert::raw2json(array($return)));
		$response->addHeader('Content-Type', 'text/plain');
		if (!empty($return['error'])) $response->setStatusCode(403);
		return $response;
	}

}
