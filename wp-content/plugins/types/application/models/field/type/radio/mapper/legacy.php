<?php

/**
 * Class Types_Field_Type_Radio_Mapper_Legacy
 *
 * Mapper for "Radio" field
 *
 * @since 2.3
 */
class Types_Field_Type_Radio_Mapper_Legacy extends Types_Field_Mapper_Abstract {
	/**
	 * @var Types_Field_Type_Radio_Factory
	 */
	protected $field_factory;

	/**
	 * @param $id
	 * @param $id_post
	 *
	 * @return null|Types_Field_Type_Legacy
	 * @throws Exception
	 */
	public function find_by_id( $id, $id_post ) {
		if( ! $field = $this->database_get_field_by_id( $id ) ) {
			return null;
		};

		if( $field['type'] !== 'radio' ) {
			throw new Exception( 'Types_Field_Type_Radio_Mapper_Legacy can not map type: ' . $field['type'] );
		}

		$field = $this->map_common_field_properties( $field );

		$options = isset( $field['data']['options'] )
		    ? $field['data']['options']
			: array();

		$controlled = $this->is_controlled_by_types( $field );
		if( $value = $this->get_user_value( $id_post, $field['slug'], $field['repeatable'], $controlled ) ) {
			$value = array_shift( $value );
		}

		$entity = $this->field_factory->get_field( $field );

		foreach( $options as $option_id => $option_data ) {
			if( $option_data == 'no-default') {
				continue;
			}

			if( 'default' === $option_id ) {
				continue;
			}

			$option_data['id'] = $option_id;
			$option_data['checked'] = false;

			if( isset( $option_data['value'] ) ) {
				$option_data['store_value'] = $option_data['value'];

				if( $value == $option_data['value'] ) {
					$option_data['checked'] = $option_data['value'];
				}
			}

			if( isset( $option_data['display_value'] ) ) {
				$option_data['display_value_checked'] = $option_data['display_value'];
			}

			$option = new Types_Field_Part_Option( $entity, $option_data );
			$entity->add_option( $option );
		}

		return $entity;
	}
}
