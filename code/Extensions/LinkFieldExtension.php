<?php 

/**
 *
 * @author  Guy Watson <guy.watson@internetrix.com.au>
 * @package  gridfieldextras
 *
 */

namespace Internetrix\GridFieldExtras\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

class LinkFieldExtension extends Extension{
	public function onBeforeRender($field){
		Requirements::javascript(GRIDFIELDEXTRAS_DIR . '/javascript/linkfield-extras.js');
		$field->setAttribute('data-title', $field->Title());
	}
}