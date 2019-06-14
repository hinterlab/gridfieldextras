<?php

/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 *
 */

namespace Internetrix\GridFieldExtras\Extensions;

use SilverStripe\AssetAdmin\Controller\AssetAdmin;
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
	
//		$this->setConfig('previewMaxWidth', 40);
//		$this->setConfig('previewMaxHeight', 30);
		$this->addExtraClass('ss-assetuploadfield');
		$this->removeExtraClass('ss-uploadfield');
		// $this->setTemplate('AssetUploadField');
//		$this->setOverwriteWarning(false);
	}
	
	public function setList(DataList $list){
		$this->list = $list;
		return $this;
	}
	
	public function getList(){
		return $this->list;
	}
	
	public function upload(HTTPRequest $request) {

        if ($this->isDisabled() || $this->isReadonly()) {
            return $this->httpError(403);
        }

        // CSRF check
        $token = $this->getForm()->getSecurityToken();
        if (!$token->checkRequest($request)) {
            return $this->httpError(400);
        }

        $tmpFile = $request->postVar('Upload');
        /** @var File $file */
        $file = $this->saveTemporaryFile($tmpFile, $error);

        // Prepare result
        if ($error) {
            $result = [
                'message' => [
                    'type' => 'error',
                    'value' => $error,
                ]
            ];
            $this->getUpload()->clearErrors();
            return (new HTTPResponse(json_encode($result), 400))
                ->addHeader('Content-Type', 'application/json');
        }

        // We need an ID for getObjectFromData
        if (!$file->isInDB()) {
            $file->write();
        }

        if($this->list instanceof DataList){
            $this->list->add($file);
        }

        // Return success response
        $result = [
            AssetAdmin::singleton()->getObjectFromData($file)
        ];

        // Don't discard pre-generated client side canvas thumbnail
        if ($result[0]['category'] === 'image') {
            unset($result[0]['thumbnail']);
        }
        $this->getUpload()->clearErrors();
        return (new HTTPResponse(json_encode($result)))
            ->addHeader('Content-Type', 'application/json');
	}

}
