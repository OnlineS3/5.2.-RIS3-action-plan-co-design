<?php

/**
 * Class that transform "Custom Fields" shortcode attributes to field object for rendering fields
 *
 * All types fields
 *
 * @since 1.9.6
 */
class CRED_Field_Command_Custom_Fields extends CRED_Field_Command_Base {

	public function execute() {

		$field = CRED_StaticClass::$out[ 'fields' ][ 'custom_fields' ][ $this->field_name ];

		//Types placeholder like all properties should win against shortcode attributes
		$placeholder = $this->filtered_attributes[ 'placeholder' ];
		if ( empty( $this->filtered_attributes[ 'placeholder' ] )
			&& isset( $field[ 'data' ][ 'placeholder' ] ) ) {
			$placeholder = $field[ 'data' ][ 'placeholder' ];
		}

		$maybe_prefixed_name = $name = $field[ 'slug' ];
		if ( isset( $field[ 'plugin_type_prefix' ] ) ) {
			$maybe_prefixed_name = $field[ 'plugin_type_prefix' ] . $name;
		}
		$field[ 'form_html_id' ] = $this->translate_field_factory->get_html_form_field_id( $field );

		$additional_attributes = array(
			'class' => $this->filtered_attributes[ 'class' ],
			'output' => $this->filtered_attributes[ 'output' ],
			'preset_value' => $this->value,
			'urlparam' => $this->filtered_attributes[ 'urlparam' ],
		);

		if ( in_array( $field[ 'type' ], array( 'credimage', 'image', 'file', 'credfile' ) ) ) {
			$additional_attributes[ 'is_tax' ] = false;
			$additional_attributes[ 'max_width' ] = $this->filtered_attributes[ 'max_width' ];
			$additional_attributes[ 'max_height' ] = $this->filtered_attributes[ 'max_height' ];
		} else {
			$additional_attributes[ 'value_escape' ] = $this->escape;
			$additional_attributes[ 'make_readonly' ] = $this->readonly;
			$additional_attributes[ 'placeholder' ] = $placeholder;
			$additional_attributes[ 'select_text' ] = $this->filtered_attributes[ 'select_text' ];
		}

		//Do not delete this commented code, it is a new feature we will enable on the next future release
		//CRED_Select2_Utils::get_instance()->try_register_field_as_select2( $this->_filtered_attributes[ 'html_form_id' ], $maybe_prefixed_name, $field, $use_select2 );

		$field_object = $this->translate_field_factory->cred_translate_field( $maybe_prefixed_name, $field, $additional_attributes );

		/*
		 * check which fields are actually used in form
		 */
		CRED_StaticClass::$out[ 'form_fields' ][ $name ] = $this->get_uniformed_field( $field, $field_object );
		CRED_StaticClass::$out[ 'form_fields_info' ][ $name ] = array(
			'type' => $field[ 'type' ],
			'repetitive' => ( isset( $field[ 'data' ][ 'repetitive' ] ) && $field[ 'data' ][ 'repetitive' ] ),
			'plugin_type' => ( isset( $field[ 'plugin_type' ] ) ) ? $field[ 'plugin_type' ] : '',
			'name' => $maybe_prefixed_name,
		);

		return $field_object;
	}
}