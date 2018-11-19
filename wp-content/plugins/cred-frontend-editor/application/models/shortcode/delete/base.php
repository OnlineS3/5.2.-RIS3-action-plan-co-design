<?php

/**
 * Class CRED_Shortcode_Delete_Base
 *
 * @since m2m
 */
class CRED_Shortcode_Delete_Base  {

	/**
	 * @var Toolset_Shortcode_Attr_Interface
	 */
	protected $item;
	
	/**
	 * @param Toolset_Shortcode_Attr_Interface $item
	 */
	public function __construct( Toolset_Shortcode_Attr_Interface $item ) {
		$this->item = $item;
	}
	
}