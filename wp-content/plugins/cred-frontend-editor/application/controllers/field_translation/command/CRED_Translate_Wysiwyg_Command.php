<?php

class CRED_Translate_Wysiwyg_Command extends CRED_Translate_Field_Command_Base {

	public function execute() {
		$this->field_attributes = array_merge( $this->field_attributes, array( 'disable_xss_filters' => true ) );
		if ( 'post_content' == $this->field_name &&
			isset( $this->form->fields['form_settings']->form['has_media_button'] ) &&
			$this->form->fields['form_settings']->form['has_media_button']
		) {
			$this->field_attributes['has_media_button'] = true;
		}

		return new CRED_Field_Translation_Result( $this->field_configuration, $this->field_type, $this->field_name, $this->field_value, $this->field_attributes, $this->field );
	}
}