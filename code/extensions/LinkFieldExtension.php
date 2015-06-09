<?php 

/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 *
 */

class LinkFieldExtension extends Extension{
	public function onBeforeRender($field){
		Requirements::javascript(GRIDFIELDEXTRAS_DIR . '/javascript/linkfield-extras.js');
		$field->setAttribute('data-title', $field->Title());
	}
}