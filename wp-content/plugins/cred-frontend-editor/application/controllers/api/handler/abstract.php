<?php

abstract class CRED_Api_Handler_Abstract implements CRED_Api_Handler_Interface{
	protected $domain_data = array(
		CRED_Form_Domain::POSTS => array(
			'post_type' => 'cred-form',
			'transient' => CRED_Cache::POST_FORMS_TRANSIENT_KEY
		),
		CRED_Form_Domain::USERS => array(
			'post_type' => 'cred-user-form',
			'transient' => CRED_Cache::USER_FORMS_TRANSIENT_KEY
		),
		CRED_Form_Domain::ASSOCIATIONS => array(
			'post_type' => CRED_Association_Form_Main::ASSOCIATION_FORMS_POST_TYPE,
			'transient'	=> CRED_Cache::ASSOCIATION_FORMS_TRANSIENT_KEY
		)
	);
}