<?php

class CRED_Ajax_Handler_Get_Relationship_Fields extends Toolset_Ajax_Handler_Abstract{

	function process_call( $arguments ) {
		$this->ajax_begin( 
			array( 
				'nonce' => CRED_Ajax::CALLBACK_GET_RELATIONSHIP_FIELDS, 
				'parameter_source' => 'get', 
				'is_public' => true
			) 
		);

		$relationship = toolset_getget( 'relationship' );
		
		if ( empty( $relationship ) ) {
			$this->ajax_finish( array( 'message' => __( 'Wrong or missing relationship.', 'wp-cred' ) ), false );
		}
		
		$results = array();
		
		$relationship_repository = Toolset_Relationship_Definition_Repository::get_instance();
		
		$relationship_definition = $relationship_repository->get_definition( $relationship );
		
		$parent_types = $relationship_definition->get_parent_type()->get_types();
		$parent_type = $parent_types[0];
		$parent_type_object = get_post_type_object( $parent_type );
		$child_types = $relationship_definition->get_child_type()->get_types();
		$child_type = $child_types[0];
		$child_type_object = get_post_type_object( $child_type );
		
		$results['roles'] = array(
			'parent' => array(
				'label' => $parent_type_object->label,
				'shortcode' => CRED_Shortcode_Association_Role::SHORTCODE_NAME,
				'requiredItem' => true,
				'attributes' => array(
					'role' => Toolset_Relationship_Role::PARENT
				),
				'options' => $this->get_role_options()
			),
			'child' => array(
				'label' => $child_type_object->label,
				'shortcode' => CRED_Shortcode_Association_Role::SHORTCODE_NAME,
				'requiredItem' => true,
				'attributes' => array(
					'role' => Toolset_Relationship_Role::CHILD
				),
				'options' => $this->get_role_options()
			)
		);
		
		if ( $relationship_definition->has_association_field_definitions() ) {
			$results['meta'] = array();
			$association_fields_definitions = $relationship_definition->get_association_field_definitions();
			foreach ( $association_fields_definitions as $field_definition ) {
				$field = $field_definition->get_definition_array();
				$results['meta'][ $field_definition->get_slug() ] = array(
					'label' => $field_definition->get_name(),
					'shortcode' => CRED_Shortcode_Association_Field::SHORTCODE_NAME,
					'requiredItem' => ( isset( $field['data']['validate']['required']['active'] ) && $field['data']['validate']['required']['active'] ),
					'attributes' => array(
						'name' => $field_definition->get_slug()
					)
				);
			}
		}
		
		$this->ajax_finish( $results, true );
	}
	
	/**
	 * Get the options for each role selector shortcode.
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	private function get_role_options() {
		return array(
			'sortingCombo' => array(
				'label' => __( 'Sorting', 'wp-cred' ),
				'type'   => 'group',
				'fields' => array(
					'orderby' => array(
						'type' => 'select',
						'options'      => array(
							'ID' => __( 'Order options by post ID', 'wp-cred' ),
							'date' => __( 'Order options by post date', 'wp-cred' ),
							'title' => __( 'Order options by post title', 'wp-cred' )
						),
						'defaultValue' => 'ID'
					),
					'order' => array(
						'type' => 'select',
						'options'      => array(
							'DESC' => __( 'Descending', 'wp-cred' ),
							'ASC' => __( 'Ascending', 'wp-cred' )
						),
						'defaultValue' => 'DESC'
					)
				)
			),
			'filteringCombo' => array(
				'label' => __( 'Filtering', 'wp-cred' ),
				'type'   => 'group',
				'fields' => array(
					'author' => array(
						'type' => 'select',
						'options'      => array(
							'' => __( 'Options by any author', 'wp-cred' ),
							'$current' => __( 'Options only by the current visitor', 'wp-cred' )
						),
						'defaultValue' => ''
					)
				),
				'description' => __( 'Include options created by any or a specific author. Not logged in visitors might not be able to submit the form.', 'wp-cred' )
			),
			'stylingCombo' => array(
				'type'   => 'group',
				'fields' => array(
					'class' => array(
						'label' => __( 'Additional classnames', 'wp-cred' ),
						'type'        => 'text'
					),
					'style' => array(
						'label' => __( 'Additional inline styles', 'wp-cred' ),
						'type'        => 'text'
					)
				),
				'description' => __( 'Include specific classnames in the selector, or add your own inline styles.', 'wp-cred' )
			)
		);
	}
	
}