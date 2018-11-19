<?php

/**
 * Cred fields model
 * (get custom fields for post types)
 */
class CRED_Fields_Model extends CRED_Fields_Abstract_Model implements CRED_Singleton {

	/** @var array  Is referred to native post fields */
	private static $basic_post_fields;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * @return mixed
	 */
	public static function get_basic_post_fields() {
		return self::$basic_post_fields;
	}

	/**
	 * @param bool $_custom
	 *
	 * @return array
	 */
    public function getTypesDefaultFields($_custom = false) {
        if (!$_custom) {
            return array(
                'checkbox' => array('title' => __('Checkbox', 'wp-cred'), 'type' => 'checkbox', 'parameters' => array('name' => true, 'default' => true)),
                'checkboxes' => array('title' => __('Checkboxes', 'wp-cred'), 'type' => 'checkboxes', 'parameters' => array('name' => true, 'options' => true, 'labels' => true, 'default' => true)),
                'select' => array('title' => __('Select', 'wp-cred'), 'type' => 'select', 'parameters' => array('name' => true, 'options' => true, 'labels' => true, 'default' => true)),
                'multiselect' => array('title' => __('Multi Select', 'wp-cred'), 'type' => 'multiselect', 'parameters' => array('name' => true, 'options' => true, 'labels' => true, 'default' => true)),
                'radio' => array('title' => __('Radio', 'wp-cred'), 'type' => 'radio', 'parameters' => array('name' => true, 'options' => true, 'labels' => true, 'default' => true)),
                'date' => array('title' => __('Date', 'wp-cred'), 'type' => 'date', 'parameters' => array('name' => true, 'default' => true, 'format' => true)),
                'email' => array('title' => __('Email', 'wp-cred'), 'type' => 'email', 'parameters' => array('name' => true, 'default' => true)),
                'url' => array('title' => __('URL', 'wp-cred'), 'type' => 'url', 'parameters' => array('name' => true, 'default' => true)),
                'skype' => array('title' => __('Skype', 'wp-cred'), 'type' => 'skype', 'parameters' => array('name' => true, 'skypename' => true, 'style' => true)),
                'phone' => array('title' => __('Phone', 'wp-cred'), 'type' => 'phone', 'parameters' => array('name' => true, 'default' => true)),
                'textfield' => array('title' => __('Single Line', 'wp-cred'), 'type' => 'textfield', 'parameters' => array('name' => true, 'default' => true)),
                'hidden' => array('title' => __('Hidden', 'wp-cred'), 'type' => 'hidden', 'parameters' => array('name' => true, 'default' => true)),
                'password' => array('title' => __('Password', 'wp-cred'), 'type' => 'password', 'parameters' => array('name' => true)),
                'textarea' => array('title' => __('Multiple Lines', 'wp-cred'), 'type' => 'textarea', 'parameters' => array('name' => true, 'default' => true)),
                'wysiwyg' => array('title' => __('WYSIWYG', 'wp-cred'), 'type' => 'wysiwyg', 'parameters' => array('name' => true, 'default' => true)),
                'numeric' => array('title' => __('Numeric', 'wp-cred'), 'type' => 'numeric', 'parameters' => array('name' => true, 'default' => true)),
                'integer' => array('title' => __('Integer', 'wp-cred'), 'type' => 'integer', 'parameters' => array('name' => true, 'default' => true)),
                'file' => array('title' => __('File', 'wp-cred'), 'type' => 'file', 'parameters' => array('name' => true)),
                'image' => array('title' => __('Image', 'wp-cred'), 'type' => 'image', 'parameters' => array('name' => true)),
                //support Types new audio and video field types consider than as file type
                'audio' => array('title' => __('Audio', 'wp-cred'), 'type' => 'audio', 'parameters' => array('name' => true)),
                'video' => array('title' => __('Video', 'wp-cred'), 'type' => 'video', 'parameters' => array('name' => true)),
                //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/187372519/comments
                //support for colorpicker and embedded media field
                'colorpicker' => array('title' => __('Colorpicker', 'wp-cred'), 'type' => 'colorpicker', 'parameters' => array('name' => true)),
                'embed' => array('title' => __('Embedded Media', 'wp-cred'), 'type' => 'embed', 'parameters' => array('name' => true)),
            );
        } else {
            return array(
                'checkbox' => array('title' => __('Checkbox', 'wp-cred'), 'type' => 'checkbox', 'parameters' => array('name' => true, 'default' => true)),
                'checkboxes' => array('title' => __('Checkboxes', 'wp-cred'), 'type' => 'checkboxes', 'parameters' => array('name' => true, 'options' => true, 'labels' => true, 'default' => true)),
                'select' => array('title' => __('Select', 'wp-cred'), 'type' => 'select', 'parameters' => array('name' => true, 'options' => true, 'labels' => true, 'default' => true)),
                /* 'multiselect'=> array ( 'title'=>__('Multi Select','wp-cred'), 'type' => 'multiselect', 'parameters'=>array('name'=>true,'options'=>true,'labels'=>true,'default'=>true)), */
                'radio' => array('title' => __('Radio', 'wp-cred'), 'type' => 'radio', 'parameters' => array('name' => true, 'options' => true, 'labels' => true, 'default' => true)),
                'date' => array('title' => __('Date', 'wp-cred'), 'type' => 'date', 'parameters' => array('name' => true, 'default' => true, 'format' => true)),
                'email' => array('title' => __('Email', 'wp-cred'), 'type' => 'email', 'parameters' => array('name' => true, 'default' => true)),
                'url' => array('title' => __('URL', 'wp-cred'), 'type' => 'url', 'parameters' => array('name' => true, 'default' => true)),
                'skype' => array('title' => __('Skype', 'wp-cred'), 'type' => 'skype', 'parameters' => array('name' => true, 'skypename' => true, 'style' => true)),
                'phone' => array('title' => __('Phone', 'wp-cred'), 'type' => 'phone', 'parameters' => array('name' => true, 'default' => true)),
                'textfield' => array('title' => __('Single Line', 'wp-cred'), 'type' => 'textfield', 'parameters' => array('name' => true, 'default' => true)),
                'hidden' => array('title' => __('Hidden', 'wp-cred'), 'type' => 'hidden', 'parameters' => array('name' => true, 'default' => true)),
                'password' => array('title' => __('Password', 'wp-cred'), 'type' => 'password', 'parameters' => array('name' => true)),
                'textarea' => array('title' => __('Multiple Lines', 'wp-cred'), 'type' => 'textarea', 'parameters' => array('name' => true, 'default' => true)),
                'wysiwyg' => array('title' => __('WYSIWYG', 'wp-cred'), 'type' => 'wysiwyg', 'parameters' => array('name' => true, 'default' => true)),
                'numeric' => array('title' => __('Numeric', 'wp-cred'), 'type' => 'numeric', 'parameters' => array('name' => true, 'default' => true)),
                'integer' => array('title' => __('Integer', 'wp-cred'), 'type' => 'integer', 'parameters' => array('name' => true, 'default' => true)),
                'file' => array('title' => __('File', 'wp-cred'), 'type' => 'file', 'parameters' => array('name' => true)),
                'image' => array('title' => __('Image', 'wp-cred'), 'type' => 'image', 'parameters' => array('name' => true)),
                //support Types new audio and video field types consider than as file type
                'audio' => array('title' => __('Audio', 'wp-cred'), 'type' => 'audio', 'parameters' => array('name' => true)),
                'video' => array('title' => __('Video', 'wp-cred'), 'type' => 'video', 'parameters' => array('name' => true)),
                //support for colorpicker and embedded media field
                'colorpicker' => array('title' => __('Colorpicker', 'wp-cred'), 'type' => 'colorpicker', 'parameters' => array('name' => true)),
                'embed' => array('title' => __('Embedded Media', 'wp-cred'), 'type' => 'embed', 'parameters' => array('name' => true)),
            );
        }
    }

	/**
	 * @param string $text
	 * @param null $post_type
	 * @param int $limit
	 *
	 * @return mixed
	 *
	 * @deprecated since 1.9.4      use CRED_Field_Utils::get_instance()->get_potential_posts()
	 */
	public function suggestPostsByTitle( $text, $post_type = null, $limit = 20 ) {
        $post_status = "('publish','private')";
        $not_in_post_types = "('view','view-template','attachment','revision','" . CRED_FORMS_CUSTOM_POST_NAME . "')";
        $text = '%' . cred_wrap_esc_like( $text ) . '%';

		$values_to_prepare = array();

        $sql = "SELECT distinct ID, post_title FROM {$this->wpdb->posts} 
			WHERE post_title LIKE %s 
			AND post_status IN $post_status ";
		$values_to_prepare[] = $text;

        if ( $post_type !== null ) {
            if (is_array($post_type)) {
                $post_type_str = "";
                foreach ($post_type as $pt) {
                    $post_type_str .= "'$pt',";
                }
                $post_type_str = rtrim($post_type_str, ',');
                $sql .= " AND post_type in ($post_type_str)";
            } else {
	            $sql .= " AND post_type = %s";
				$values_to_prepare[] = $post_type;
            }
        }

        $sql .= " ORDER BY ID DESC ";

        $limit = intval($limit);
	    if ( $limit > 0 ) {
		    $sql .= " LIMIT 0, $limit";
	    }

        $results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				$sql,
				$values_to_prepare
			)
		);

        return $results;
    }

	/**
	 * @param array $custom_exclude
	 *
	 * @return array
	 */
    public function getPostTypes($custom_exclude = array()) {
        $exclude = array('revision', 'attachment', 'nav_menu_item');
	    if ( ! empty( $custom_exclude ) ) {
		    $exclude = array_merge( $exclude, $custom_exclude );
	    }

        $post_types = get_post_types(array('public' => true, 'publicly_queryable' => true, 'show_ui' => true), 'names');
        $post_types = array_merge($post_types, get_post_types(array('public' => true, '_builtin' => true,), 'names', 'and'));
        $post_types = array_diff($post_types, $exclude);
        sort($post_types, SORT_STRING);
        $returned_post_types = array();
        foreach ($post_types as $pt) {
            $pto = get_post_type_object($pt);
            $returned_post_types[] = array('type' => $pt, 'name' => $pto->labels->name);
        }
        unset($post_types);
        return $returned_post_types;
    }

	/**
	 * @return array
	 */
	public function getPostTypesWithoutTypes() {
		$wpcf_custom_types = get_option( 'wpcf-custom-types', false );
		if ( $wpcf_custom_types ) {
			return $this->getPostTypes( array_keys( $wpcf_custom_types ) );
		} else {
			return $this->getPostTypes();
		}
	}

	/**
	 * @param string $post_type
	 * @param array $exclude_fields
	 * @param bool $show_private
	 * @param int $paged
	 * @param int $perpage
	 * @param string $orderby
	 * @param string $order
	 *
	 * @return mixed
	 */
	public function getPostTypeCustomFields( $post_type, $exclude_fields = array(), $show_private = true, $paged, $perpage = 10, $orderby = 'meta_key', $order = 'asc' ) {
        /*
          TODO:
          make search incremental to avoid large data issues
         */
		
		// TODO To optimize this query we need to be careful with the post type and sorting options.

        $exclude = array('_edit_last', '_edit_lock', '_wp_old_slug', '_thumbnail_id', '_wp_page_template',);
		if ( ! empty( $exclude_fields ) ) {
			$exclude = array_merge( $exclude, $exclude_fields );
		}
		
		$limit = 512 + count( $exclude );

        $exclude = "'" . implode("','", $exclude) . "'"; //wrap in quotes

        if ($paged < 0) {
	        if ( $show_private ) {
		        $sql = $this->wpdb->prepare(
					"SELECT COUNT(DISTINCT(pm.meta_key)) 
					FROM {$this->wpdb->postmeta} as pm, {$this->wpdb->posts} as p
					WHERE pm.post_id = p.ID
					AND p.post_type = %s
					AND pm.meta_key NOT IN ({$exclude})
					AND pm.meta_key NOT LIKE %s 
					LIMIT %d", 
					array( $post_type, "wpcf-%", $limit ) 
				);
	        } else {
		        $sql = $this->wpdb->prepare(
					"SELECT COUNT(DISTINCT(pm.meta_key)) 
					FROM {$this->wpdb->postmeta} as pm, {$this->wpdb->posts} as p
					WHERE pm.post_id = p.ID 
					AND p.post_type = %s
					AND pm.meta_key NOT IN ({$exclude})
					AND pm.meta_key NOT LIKE %s 
					AND pm.meta_key NOT LIKE %s 
					LIMIT %d", 
					array( $post_type, "wpcf-%", "\_%", $limit )
				);
	        }

            return $this->wpdb->get_var($sql);
        }
		
		// TODO To optimize this query we need to be careful with the post type and sorting options.
		
        $paged = intval($paged);
        $perpage = intval($perpage);
        $paged--;
        $order = strtoupper($order);
		if ( ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
			$order = 'ASC';
		}
		if ( ! in_array( $orderby, array( 'meta_key' ) ) ) {
			$orderby = 'meta_key';
		}

		if ( $show_private ) {
			$sql = $this->wpdb->prepare(
				"SELECT DISTINCT(pm.meta_key) 
				FROM {$this->wpdb->postmeta} as pm, {$this->wpdb->posts} as p
				WHERE pm.post_id = p.ID
				AND p.post_type = %s
				AND pm.meta_key NOT IN ({$exclude})
				AND pm.meta_key NOT LIKE %s 
				ORDER BY pm.{$orderby} {$order}
				LIMIT " . ( $paged * $perpage ) . ", " . $perpage,
				array( $post_type, "wpcf-%" )
			);
		} else {
			$sql = $this->wpdb->prepare(
				"SELECT DISTINCT(pm.meta_key) 
				FROM {$this->wpdb->postmeta} as pm, {$this->wpdb->posts} as p
				WHERE pm.post_id = p.ID
				AND p.post_type = %s
				AND pm.meta_key NOT IN ({$exclude})
				AND pm.meta_key NOT LIKE %s 
				AND pm.meta_key NOT LIKE %s
				ORDER BY pm.{$orderby} {$order}
				LIMIT " . ( $paged * $perpage ) . ", " . $perpage,
				array( $post_type, "wpcf-%", "\_%" )
			);
		}

        $fields = $this->wpdb->get_col($sql);

        return $fields;
    }

	/**
	 * @param null $post_type
	 * @param bool $force_all
	 *
	 * @return array|mixed
	 */
	public function getCustomFields( $post_type = null, $force_all = false ) {
        $custom_field_options = self::CUSTOM_FIELDS_OPTION;
        $custom_fields = get_option($custom_field_options, false);

        if ($force_all) {
	        if ( $custom_fields && ! empty( $custom_fields ) ) {
		        return $custom_fields;
	        }
        }

	    if ( $post_type !== null ) {
		    if ( $custom_fields && ! empty( $custom_fields ) && isset( $custom_fields[ $post_type ] ) ) {
			    return $custom_fields[ $post_type ];
		    }

		    return array();
	    } else {
		    if ( $custom_fields && ! empty( $custom_fields ) ) {
			    return $custom_fields;
		    }

		    return array();
	    }
    }

	/**
	 * Create and init Toolset Forms custom field
	 *
	 * @param null $field_data
	 */
	public function setCustomField( $field_data = null ) {
		if ( $field_data !== null
			&& isset( $field_data['post_type'] )
		) {
			$post_type = $field_data['post_type'];
			$custom_field_options = self::CUSTOM_FIELDS_OPTION;
			$field = array(
				'id' => $field_data['name'],
				'post_type' => $field_data['post_type'],
				'cred_custom' => true,
				'slug' => $field_data['name'],
				'type' => $field_data['type'],
				'name' => $field_data['name'],
				//added isset for back compatibility
				'default' => isset( $field_data['default'] ) ? $field_data['default'] : "",
				'data' => array(
					'repetitive' => 0,
					'validate' => array(
						'required' => array(
							'active' => isset( $field_data['required'] ),
							'value' => isset( $field_data['required'] ),
							'message' => __( 'This field is required', 'wp-cred' ),
						),
					),
					'validate_format' => isset( $field_data['validate_format'] ),
				),
			);

			if ( ! isset( $field_data['include_scaffold'] ) ) {
				$field['_cred_ignore'] = true;
			}

			switch ( $field_data['type'] ) {
				case 'checkbox':
					$field['data']['set_value'] = $field_data['default'];
					break;
				case 'checkboxes':
					$field['data']['options'] = array();
					if ( ! isset( $field_data['options']['value'] ) ) {
						$field_data['options'] = array( 'value' => array(), 'label' => array(), 'option_default' => array() );
					}
					foreach ( $field_data['options']['value'] as $ii => $option ) {
						$option_id = $option;
						$field['data']['options'][ $option_id ] = array(
							'title' => $field_data['options']['label'][ $ii ],
							'set_value' => $option,
						);
						if ( isset( $field_data['options']['option_default'] ) && in_array( $option, $field_data['options']['option_default'] ) ) {
							$field['data']['options'][ $option_id ]['checked'] = true;
						}
					}
					break;
				case 'date':
					$field['data']['validate']['date'] = array(
						'active' => isset( $field_data['validate_format'] ),
						'format' => 'mdy',
						'message' => __( 'Please enter a valid date', 'wp-cred' ),
					);
					break;
				case 'radio':
				case 'select':
					$field['data']['options'] = array();
					$default_option = 'no-default';
					if ( ! isset( $field_data['options']['value'] ) ) {
						$field_data['options'] = array( 'value' => array(), 'label' => array(), 'option_default' => '' );
					}
					foreach ( $field_data['options']['value'] as $ii => $option ) {
						$option_id = $option;
						//$option_id=$atts['field'].'_option_'.$ii;
						$field['data']['options'][ $option_id ] = array(
							'title' => $field_data['options']['label'][ $ii ],
							'value' => $option,
							'display_value' => $option,
						);
						if ( isset( $field_data['options']['option_default'] ) && ! empty( $field_data['options']['option_default'] ) && $field_data['options']['option_default'] == $option ) {
							$default_option = $option_id;
						}
					}
					$field['data']['options']['default'] = $default_option;
					break;
				case 'email':
					$field['data']['validate']['email'] = array(
						'active' => isset( $field_data['validate_format'] ),
						'message' => __( 'Please enter a valid email address', 'wp-cred' ),
					);
					break;
				case 'numeric':
					$field['data']['validate']['number'] = array(
						'active' => isset( $field_data['validate_format'] ),
						'message' => __( 'Please enter numeric data', 'wp-cred' ),
					);
					break;
				case 'integer':
					$field['data']['validate']['integer'] = array(
						'active' => isset( $field_data['validate_format'] ),
						'message' => __( 'Please enter integer data', 'wp-cred' ),
					);
					break;
				case 'embed':
				case 'url':
					$field['data']['validate']['url'] = array(
						'active' => isset( $field_data['validate_format'] ),
						'message' => __( 'Please enter a valid URL address', 'wp-cred' ),
					);
					break;
				case 'colorpicker':
					$field['data']['validate']['hexadecimal'] = array(
						'active' => isset( $field_data['validate_format'] ),
						'message' => __( 'Please use a valid hexadecimal value', 'wp-cred' ),
					);
					break;
				default:
					break;
			}
			$custom_fields = get_option( $custom_field_options );

			if ( $custom_fields && ! empty( $custom_fields ) && isset( $custom_fields[ $post_type ] ) ) {
				if ( is_array( $custom_fields[ $post_type ] ) ) {
					$custom_fields[ $post_type ][ $field_data['name'] ] = $field;
				} else {
					$custom_fields[ $post_type ] = array( $field_data['name'] => $field );
				}
			} else {
				if ( ! $custom_fields || empty( $custom_fields ) ) {
					$custom_fields = array();
				}
				$custom_fields[ $post_type ] = array( $field_data['name'] => $field );
			}

			$this->save_custom_fields( $custom_fields );
		}
	}

	/**
	 * @param string $post_type
	 * @param string $field_name
	 * @param bool $types_format
	 *
	 * @return array|mixed
	 */
	public function getCustomField( $post_type, $field_name, $types_format = false ) {
		$custom_fields = $this->getCustomFields( $post_type );
		if ( isset( $custom_fields[ $field_name ] ) ) {
			if ( $types_format ) {
				return $custom_fields[ $field_name ];
			} else {
				$field_data = $custom_fields[ $field_name ];
				$field = array(
					'post_type' => $post_type,
					'name' => $field_name,
					'default' => $field_data['default'],
					'type' => $field_data['type'],
					'required' => isset( $field_data['data']['validate']['required']['active'] ) && $field_data['data']['validate']['required']['active'],
					'validate_format' => isset( $field_data['data']['validate_format'] ) && $field_data['data']['validate_format'],
					'include_scaffold' => (bool) ( ! isset( $field_data['_cred_ignore'] ) || ! $field_data['_cred_ignore'] ),
				);
				switch ( $field_data['type'] ) {
					case 'checkbox':
						$field['default'] = $field_data['data']['set_value'];
						break;
					case 'checkboxes':
						$field['options'] = array( 'value' => array(), 'label' => array(), 'option_default' => array() );
						foreach ( $field_data['data']['options'] as $ii => $option ) {
							$field['options']['value'][] = $option['set_value'];
							$field['options']['label'][] = $option['title'];
							if ( isset( $option['checked'] ) && $option['checked'] ) {
								$field['options']['option_default'][] = $option['set_value'];
							}
						}
						break;
					case 'radio':
					case 'select':
						$field['options'] = array( 'value' => array(), 'label' => array(), 'option_default' => '' );
						foreach ( $field_data['data']['options'] as $ii => $option ) {
							if ( $ii == 'default' ) {
								continue;
							}
							$field['options']['value'][] = $option['value'];
							$field['options']['label'][] = $option['title'];
							if ( isset( $field_data['data']['options']['default'] ) && $option['value'] == $field_data['data']['options']['default'] ) {
								$field['options']['option_default'] = $option['value'];
							}
						}
						break;
					default:
						break;
				}

				return $field;
			}
		} else {
			return array();
		}
	}

	/**
	 * @param string $post_type
	 * @param array $field_names
	 * @param string $action
	 */
	public function ignoreCustomFields( $post_type, $field_names, $action = 'ignore' ) {
		$custom_fields = $this->getCustomFields( $post_type, true );

		if ( ! $custom_fields
			|| ! isset( $custom_fields[ $post_type ] )
		) {
			return;
		}

		$custom_field_names = array_keys( $custom_fields[ $post_type ] );
		foreach ( $field_names as $field_name ) {

			if ( in_array( $field_name, $custom_field_names ) ) {
				switch ( $action ) {
					case 'ignore':
						$custom_fields[ $post_type ][ $field_name ]['_cred_ignore'] = true;
						break;
					case 'unignore':
						unset( $custom_fields[ $post_type ][ $field_name ]['_cred_ignore'] );
						break;
					case 'reset':
						unset( $custom_fields[ $post_type ][ $field_name ] );
						break;
				}
			}
		}

		$this->save_custom_fields( $custom_fields );
	}

	/**
	 * Function responsible get all fields structure given a Post Type
	 * NOTE: Types controlled fields do not have prefix 'wpcf-'
	 *
	 * @param string $post_type
	 * @param bool $add_default
	 * @param null $localized_message_callback
	 *
	 * @return array
	 */
	public function getFields($post_type, $add_default = true, $localized_message_callback = null) {
		if ( empty( $post_type )
			|| $post_type == null
			|| $post_type == false
		) {
			return array();
		}

		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object ) {
			return array();
		}

		//m2m is enabled in order to disable old _wpcf_belongs parent shortcodes
		$is_m2m_enabled = CRED_Form_Relationship::get_instance()->is_m2m_enabled();

		// ALL FIELDS
		$all_fields = array();
		$groups = array();
		$fields = array();
		$groups_conditions = array();

		$post_type_original = $post_type;
		$post_type = '%,' . $post_type . ',%';

		$cred_fields_types_utils = new CRED_Fields_Types_Utils();

		// WPCF CUSTOM TYPES
		$isTypesActive = defined('WPCF_VERSION');
		//Not needed anymore
		//$wpcf_custom_types = $cred_fields_types_utils->get_wpcf_custom_types();
		//$isTypesPost = ($isTypesActive && $wpcf_custom_types) ? array_key_exists($post_type_original, $wpcf_custom_types) : false;

		$credCustomFields = $this->getCustomFields($post_type_original);
		$isCredCustomPost = (bool) (!empty($credCustomFields));

		// SET GROUPS FIELDS
		$cred_fields_types_utils->set_fields_groups_and_group_conditions( $post_type_object, $fields, $groups, $groups_conditions );

		// SET Toolset Forms CUSTOM FIELDS
		$cred_fields_types_utils->add_cred_custom_fields_in_groups( $isCredCustomPost, $credCustomFields, $fields, $groups );

		// PARENTS FIELDS
		$post_reference_fields = array();
		$relationships = array();
		$parents = $cred_fields_types_utils->get_parent_fields( $post_type_original );
		if ( $is_m2m_enabled ) {
			/*
			SET Post Reference Field Relationship
			NOTE: get_post_reference_fields must be always after add_cred_custom_fields_in_groups as it needs fields set by Types
			*/
			$post_reference_fields = $cred_fields_types_utils->get_post_reference_fields( $fields, $post_type_object );

			// MAP LEGACY PARENTS WITH RELATIONSHIPS
			CRED_Form_Relationship::get_instance()->map_parents_legacy_relationships( $parents, $post_type_object );

			// RELATIONSHIPS
			$relationships = CRED_Form_Relationship::get_instance()->get_relationships( $post_type_object );
		}

		// POST FIELDS
		$post_fields = $cred_fields_types_utils->get_post_fields( $add_default, $localized_message_callback, $post_type_object );

		// HIERARCHICAL PARENT FIELDS
		$hierarchical_parents = $cred_fields_types_utils->get_hierarchical_parent_fields( $post_type_object );

		// EXTRA FIELDS
		$extra_fields = $cred_fields_types_utils->get_extra_fields( $post_type_object );

		// BASIC FORM FIELDS
		$form_fields = $cred_fields_types_utils->get_form_fields();

		// TAXONOMIES FIELDS
		$taxonomies = $cred_fields_types_utils->get_taxonomies( $post_type_object );

		$all_fields['_post_data'] = $post_type_object->labels;
		$all_fields['groups'] = $groups;
		$all_fields['groups_conditions'] = $groups_conditions;
		$all_fields['form_fields'] = $form_fields;
		$all_fields['post_fields'] = $post_fields;
		$all_fields['custom_fields'] = $fields;
		$all_fields['post_reference_fields'] = $post_reference_fields;
		$all_fields['taxonomies'] = $taxonomies;
		$all_fields['parents'] = $parents;
		$all_fields['hierarchical_parents'] = $hierarchical_parents;
		$all_fields['relationships'] = $relationships;
		$all_fields['extra_fields'] = $extra_fields;
		$all_fields['form_fields_count'] = count($form_fields);
		$all_fields['post_fields_count'] = count($post_fields);
		$all_fields['custom_fields_count'] = count($fields);
		$all_fields['post_reference_fields_count'] = count($post_reference_fields);
		$all_fields['taxonomies_count'] = count($taxonomies);
		$all_fields['parents_count'] = count($parents);
		$all_fields['relationships_count'] = count($relationships);
		$all_fields['extra_fields_count'] = count($extra_fields);

		self::$basic_post_fields = $post_fields;

		return $all_fields;
	}

	/**
	 * @param string $post_type
	 * @param null $post_id
	 * @param int $results
	 * @param string $order
	 * @param string $ordering
	 *
	 * @return array|mixed
	 *
	 * @deprecated since 1.9.3 use get_potential_parents( $post_type, $wpml_context, $wpml_name, $query = '' )
	 */
    public function getPotentialParents($post_type, $post_id = null, $results = 0, $order = 'date', $ordering = 'desc') {
        $post_status = "('publish','private')";

	    if ( $order != 'title' ) {
		    $order = 'post_date';
	    }

        $ordering = strtoupper($ordering);
        $ordering = in_array($ordering, array('ASC', 'DESC')) ? $ordering : 'DESC';

	    if ( ! is_numeric( $results ) || is_nan( $results ) ) {
		    $results = 0;
	    } else {
		    $results = intval( $results );
	    }

        $args = array(
            'posts_per_page' => ($results > 0) ? $results : -1,
            'numberposts' => ($results > 0) ? $results : -1,
            'offset' => 0,
            'category' => '',
            'orderby' => $order,
            'order' => $ordering,
            'include' => '',
            'exclude' => '',
            'meta_key' => '',
            'meta_value' => '',
            'post_type' => $post_type,
            'post_mime_type' => '',
            'post_parent' => '',
            'post_status' => apply_filters('cred_get_potential_parents_post_status', array('publish', 'private')),
            'suppress_filters' => false,
        );
        $parents = get_posts($args);

        $parents = apply_filters('wpml_cred_potential_parents_filter', $parents, $args);

        return $parents;
    }

	/**
	 * @return mixed
	 */
    public function getAllFields() {
        return get_option('wpcf-fields');
    }
}
