<?php

/**
 * Uses custom posts and fields to store form data
 */
class CRED_Forms_Model extends CRED_Abstract_Model {

    public function __construct() {
        parent::__construct();

        $this->post_type_name = CRED_FORMS_CUSTOM_POST_NAME;
    }

    public function register_form_type() {
        $args = array(
            'labels' => array(
                'name' => __( 'Post Forms', 'wp-cred' ),
                'singular_name' => __( 'Post Form', 'wp-cred' ),
                'add_new' => __( 'Add New', 'wp-cred' ),
                'add_new_item' => __( 'Add New Post Form', 'wp-cred' ),
                'edit_item' => __( 'Edit Post Form', 'wp-cred' ),
                'new_item' => __( 'New Post Form', 'wp-cred' ),
                'view_item' => __( 'View Post Form', 'wp-cred' ),
                'search_items' => __( 'Search Post Forms', 'wp-cred' ),
                'not_found' => __( 'No forms found', 'wp-cred' ),
                'not_found_in_trash' => __( 'No form found in Trash', 'wp-cred' ),
                'parent_item_colon' => '',
                'menu_name' => 'CRED Post Forms'
            ),
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => false,
            'rewrite' => false,
            'can_export' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 80,
            'supports' => array('title', 'editor')
        );
        register_post_type( $this->post_type_name, $args );

        add_filter( 'user_can_richedit', array(&$this, 'disable_richedit_for_cred_forms') );
    }

	/**
	 * @return array|bool
	 */
    public function getDefaultMessages() {
        static $messages = false;

        if ( !$messages ) {
            $messages = array(
                'cred_message_post_saved' => 'Post ' . __( 'Saved', 'wp-cred' ),
                'cred_message_post_not_saved_singular' => __( 'The post was not saved because of the following problem:', 'wp-cred' ),
                'cred_message_post_not_saved_plural' => __( 'The post was not saved because of the following %NN problems:', 'wp-cred' ),
                'cred_message_invalid_form_submission' => 'Invalid Form Submission (nonce failure)',
                'cred_message_no_data_submitted' => 'Invalid Form Submission (maybe a file has a size greater than allowed)',
                'cred_message_upload_failed' => 'Upload Failed',
                'cred_message_field_required' => 'This field is required',
                'cred_message_enter_valid_date' => 'Please enter a valid date',
                'cred_message_values_do_not_match' => 'Field values do not match',
                'cred_message_enter_valid_email' => 'Please enter a valid email address',
	            'cred_message_enter_valid_colorpicker' => 'Please use a valid hexadecimal value',
                'cred_message_enter_valid_number' => 'Please enter numeric data',
                'cred_message_enter_valid_url' => 'Please enter a valid URL address',
                'cred_message_enter_valid_captcha' => 'Wrong CAPTCHA',
                'cred_message_missing_captcha' => 'Missing CAPTCHA',
                'cred_message_show_captcha' => 'Show CAPTCHA',
                'cred_message_edit_skype_button' => 'Edit Skype Button',
                'cred_message_not_valid_image' => 'Not Valid Image',
                'cred_message_file_type_not_allowed' => 'File type not allowed',
                'cred_message_image_width_larger' => 'Image width larger than %dpx',
                'cred_message_image_height_larger' => 'Image height larger than %dpx',
                'cred_message_show_popular' => 'Show Popular',
                'cred_message_hide_popular' => 'Hide Popular',
                'cred_message_add_taxonomy' => 'Add',
                'cred_message_remove_taxonomy' => 'Remove',
                'cred_message_add_new_taxonomy' => 'Add New',
            );
        }

        return $messages;
    }

	/**
	 * @return array|bool
	 */
    public function getDefaultMessageDescriptions() {
        static $desc = false;

        if ( !$desc ) {
            $desc = array(
                'cred_message_post_saved' => __( 'Post saved Message', 'wp-cred' ),
                'cred_message_post_not_saved_singular' => __( 'Post not saved message (one problem)', 'wp-cred' ),
                'cred_message_post_not_saved_plural' => __( 'Post not saved message (several problems)', 'wp-cred' ),
                'cred_message_invalid_form_submission' => __( 'Invalid submission message', 'wp-cred' ),
                'cred_message_no_data_submitted' => __( 'Invalid Form Submission (maybe a file has a size greater than allowed)', 'wp-cred' ),
                'cred_message_upload_failed' => __( 'Upload failed message', 'wp-cred' ),
                'cred_message_field_required' => __( 'Required field message', 'wp-cred' ),
                'cred_message_enter_valid_date' => __( 'Invalid date message', 'wp-cred' ),
                'cred_message_values_do_not_match' => __( 'Invalid hidden field value message', 'wp-cred' ),
                'cred_message_enter_valid_email' => __( 'Invalid email message', 'wp-cred' ),
                'cred_message_enter_valid_colorpicker' => __( 'Invalid color picker message', 'wp-cred' ),
                'cred_message_enter_valid_number' => __( 'Invalid numeric field message', 'wp-cred' ),
                'cred_message_enter_valid_url' => __( 'Invalid URL message', 'wp-cred' ),
                'cred_message_enter_valid_captcha' => __( 'Invalid captcha message', 'wp-cred' ),
                'cred_message_missing_captcha' => __( 'Missing captcha message', 'wp-cred' ),
                'cred_message_show_captcha' => __( 'Show captcha button', 'wp-cred' ),
                'cred_message_edit_skype_button' => __( 'Edit skype button', 'wp-cred' ),
                'cred_message_not_valid_image' => __( 'Invalid image message', 'wp-cred' ),
                'cred_message_file_type_not_allowed' => __( 'Invalid file type message', 'wp-cred' ),
                'cred_message_image_width_larger' => __( 'Invalid image width message', 'wp-cred' ),
                'cred_message_image_height_larger' => __( 'Invalid image height message', 'wp-cred' ),
                'cred_message_show_popular' => __( 'Taxonomy show popular message', 'wp-cred' ),
                'cred_message_hide_popular' => __( 'Taxonomy hide popular message', 'wp-cred' ),
                'cred_message_add_taxonomy' => __( 'Add taxonomy term', 'wp-cred' ),
                'cred_message_remove_taxonomy' => __( 'Remove taxonomy term', 'wp-cred' ),
                'cred_message_add_new_taxonomy' => __( 'Add new taxonomy message', 'wp-cred' )
            );
        }

        return $desc;
    }

	/**
	 * @param $fields
	 *
	 * @return array|mixed|object
	 */
    public function changeFormat($fields) {
        // change format here
        if ( isset( $fields['form_settings'] ) ) {
            $form_settings = $fields['form_settings'];
            if ( !isset( $form_settings->form ) ) {
	            if ( isset( $form_settings->message ) ) {
		            if ( is_string( $form_settings->message ) ) {
			            $_message = $form_settings->message;
		            } else {
			            $_message = '';
		            }
	            } else {
		            $_message = '';
	            }

                $setts = new stdClass;
                $setts->form = array(
                    'type' => isset( $form_settings->form_type ) ? $form_settings->form_type : '',
                    'action' => isset( $form_settings->form_action ) ? $form_settings->form_action : '',
                    'action_page' => isset( $form_settings->form_action_page ) ? $form_settings->form_action_page : '',
                    'action_message' => $_message,
                    'redirect_delay' => isset( $form_settings->redirect_delay ) ? $form_settings->redirect_delay : 0,
                    'hide_comments' => isset( $form_settings->hide_comments ) ? $form_settings->hide_comments : 0,
                    'theme' => isset( $form_settings->cred_theme_css ) ? $form_settings->cred_theme_css : 'minimal',
                    'has_media_button' => isset( $form_settings->has_media_button ) ? $form_settings->has_media_button : 0,
                    'include_wpml_scaffold' => isset( $form_settings->include_wpml_scaffold ) ? $form_settings->include_wpml_scaffold : 0,
                    'include_captcha_scaffold' => isset( $form_settings->include_captcha_scaffold ) ? $form_settings->include_captcha_scaffold : 0
                );
                $setts->post = array(
                    'post_type' => isset( $form_settings->post_type ) ? $form_settings->post_type : '',
                    'post_status' => isset( $form_settings->post_status ) ? $form_settings->post_status : ''
                );
                unset( $fields['form_settings'] );
                $fields['form_settings'] = $setts;
            }
        }

        if ( isset( $fields['extra'] ) ) {
            // reformat messages
            if ( isset( $fields['extra']->messages ) ) {
                foreach ( $fields['extra']->messages as $mid => $msg ) {
                    if ( is_array( $msg ) && isset( $msg['msg'] ) ) {
                        $fields['extra']->messages[$mid] = $msg['msg'];
                    }
                }
            }
        }

        if ( isset( $fields['notification'] ) ) {
            $nt = (object) $fields['notification'];
            $notts = new stdClass;
            $notts->enable = isset( $nt->enable ) ? $nt->enable : 0;
            $notts->notifications = isset( $nt->notifications ) ? $nt->notifications : array();
            foreach ( $notts->notifications as $ii => $n ) {
                if ( isset( $n['mail_to_type'] ) ) {
                    $_type = isset( $n['mail_to_type'] ) ? $n['mail_to_type'] : '';
                    $notts->notifications[$ii] = array(
                        'event' => array(
                            'type' => 'form_submit',
                            'post_status' => '',
                            'condition' => array(),
                            'any_all' => ''
                        ),
                        'to' => array(
                            'type' => array(
                                $_type
                            ),
                            'wp_user' => array(
                                'to_type' => 'to',
                                'user' => isset( $n['mail_to_user'] ) ? $n['mail_to_user'] : ''
                            ),
                            'mail_field' => array(
                                'to_type' => 'to',
                                'address_field' => isset( $n['mail_to_field'] ) ? $n['mail_to_field'] : '',
                                'name_field' => '',
                                'lastname_field' => ''
                            ),
                            'user_id_field' => array(
                                'to_type' => 'to',
                                'field_name' => isset( $n['mail_to_user_id_field'] ) ? $n['mail_to_user_id_field'] : ''
                            ),
                            'specific_mail' => array(
                                'address' => isset( $n['mail_to_specific'] ) ? $n['mail_to_specific'] : '',
                            )
                        ),
                        'from' => array(
                            'address' => isset( $n['from_addr'] ) ? $n['from_addr'] : '',
                            'name' => isset( $n['from_name'] ) ? $n['from_name'] : ''
                        ),
                        'mail' => array(
                            'subject' => isset( $n['subject'] ) ? $n['subject'] : '',
                            'body' => isset( $n['body'] ) ? $n['body'] : ''
                        )
                    );
                }

                // apply some defaults
                $notts->notifications[$ii] = $this->merge( array(
                    'event' => array(
                        'type' => 'form_submit',
                        'post_status' => 'publish',
                        'condition' => array(
                        ),
                        'any_all' => 'ALL'
                    ),
                    'to' => array(
                        'type' => array(),
                        'wp_user' => array(
                            'to_type' => 'to',
                            'user' => ''
                        ),
                        'mail_field' => array(
                            'to_type' => 'to',
                            'address_field' => '',
                            'name_field' => '',
                            'lastname_field' => ''
                        ),
                        'user_id_field' => array(
                            'to_type' => 'to',
                            'field_name' => ''
                        ),
                        'specific_mail' => array(
                            'address' => ''
                        )
                    ),
                    'from' => array(
                        'address' => '',
                        'name' => ''
                    ),
                    'mail' => array(
                        'subject' => '',
                        'body' => ''
                    )
                        ), $notts->notifications[$ii] );
            }
            unset( $fields['notification'] );
            $fields['notification'] = $notts;
        }

        // provide some defaults
        $fields = $this->merge( array(
            'form_settings' => (object) array(
                'post' => array(
                    'post_type' => '',
                    'post_status' => ''
                ),
                'form' => array(
                    'type' => '',
                    'action' => '',
                    'action_page' => '',
                    'action_message' => '',
                    'redirect_delay' => 0,
                    'hide_comments' => 0,
                    'theme' => 'minimal',
                    'has_media_button' => 0,
                    'include_wpml_scaffold' => 0,
                    'include_captcha_scaffold' => 0
                )
            ),
            'extra' => (object) array(
                'css' => '',
                'js' => '',
                'messages' => $this->getDefaultMessages()
            ),
            'notification' => (object) array(
                'enable' => 0,
                'notifications' => array()
            )
                ), $fields );

        return $fields;
    }

//=================== GENERAL (CUSTOM) POST HANDLING METHODS ====================================================

	/**
	 * Function that retrieves post_meta
	 *
	 * @param $post_id
	 * @param $meta
     * @param $single
	 *
	 * @return mixed
	 */
	public function getPostMeta( $post_id, $meta, $single = true ) {
		return get_post_meta( $post_id, $meta, $single );
	}

	/**
	 * @param int $post_id
	 * @param string|array $data
	 *
	 * @return bool|int
	 */
    public function setAttachedData($post_id, $data) {
        return update_post_meta( intval( $post_id ), '__cred_notification_data', $data ); // serialize
    }

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 */
    public function removeAttachedData($post_id) {
        return delete_post_meta( intval( $post_id ), '__cred_notification_data' );
    }

	/**
	 * @param int $post_id
	 *
	 * @return array
	 */
    public function getAttachedData($post_id) {
        return get_post_meta( intval( $post_id ), '__cred_notification_data', true ); // unserialize
    }

	/**
	 * @param int $post_id
	 * @param bool $force_delete
	 *
	 * @return bool
	 */
    public function deletePost($post_id, $force_delete = true) {
	    if ( $force_delete ) {
		    $result = wp_delete_post( $post_id, $force_delete );
	    } else {
		    $result = wp_trash_post( $post_id );
	    }
        return ($result !== false);
    }

	/**
	 * @param $object_field
	 * @param null $include_fields_only
	 *
	 * @return array
	 */
	public function get_object_fields( $object_field, $include_fields_only = null ) {
		return $this->getPostFields( $object_field, $include_fields_only );
	}

	/**
	 * @param $post_id
	 * @param null $only
	 *
	 * @return array
	 */
	public function getPostFields( $post_id, $only = null ) {
		$fields = get_post_custom( $post_id );

		//Adding post fields that can be used as conditions meta_modified
		$post = get_post($post_id);
		$fields['post_title'] = $post->post_title;
		$fields['post_content'] = $post->post_content;
		$fields['post_excerpt'] = $post->post_excerpt;

		foreach ( $fields as $field_slug => $field ) {
			if ( is_array( $field ) ) {
				foreach ( $field as $index => $value ) {
					$fields[ $field_slug ][ $index ] = maybe_unserialize( maybe_unserialize( $value ) );
				}
			} else {
				$fields[ $field_slug ] = maybe_unserialize( $field );
			}
		}
		if ( null !== $only
			&& empty( $only ) ) {
			$fields = array();
		} elseif ( $only
			&& is_array( $only )
			&& ! empty( $only ) ) {
			$fields = array_intersect_key( $fields, array_flip( $only ) );
		}

		return $fields;
	}

	/**
	 * @param $post
	 *
	 * @return array
	 */
    public function getPostTaxonomies($post) {
	    $all_taxonomies = get_taxonomies( array(
		    'public'   => true,
		    '_builtin' => false,
	    ), 'objects', 'or' );
        $taxonomies = array();
        foreach ( $all_taxonomies as $taxonomy ) {
	        if ( ! in_array( $post->post_type, $taxonomy->object_type ) ) {
		        continue;
	        }
	        if ( in_array( $taxonomy->name, array( 'post_format' ) ) ) {
		        continue;
	        }

	        $key = $taxonomy->name;
            $taxonomies[$key] = array(
                'label' => $taxonomy->label,
                'name' => $taxonomy->name,
                'hierarchical' => $taxonomy->hierarchical,
            );
            $taxonomies[$key]['terms'] = $this->buildTerms( wp_get_post_terms( $post->ID, $taxonomy->name, array("fields" => "all") ) );
        }
        unset( $all_taxonomies );
        return $taxonomies;
    }

	/**
	 * @param $post_id
	 *
	 * @return array
	 */
	public function getPost( $post_id ) {
		$post_id = intval( $post_id );

		// get post
		$post = get_post( $post_id );
		$fields = array();
		$taxonomies = array();
		$extra = array();
		if ( isset( $post ) ) {
			// get post meta fields
			$fields = $this->getPostFields( $post_id );

			// get post type taxonomies
			$taxonomies = $this->getPostTaxonomies( $post );

			$_featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'thumbnail' );
			$_featured_image_url = isset( $_featured_image[0] ) ? $_featured_image[0] : "";

			// extra fields
			$extra = array(
				'featured_img_html' => $_featured_image_url,
			);
		}

		return array( $post, $fields, $taxonomies, $extra );
	}

	/**
	 * @param int $post
	 * @param array $fields
	 * @param array|null $taxonomies
	 *
	 * @return int|WP_Error
	 */
	public function addPost($post, $fields, $taxonomies = null) {
		global $user_ID;
		$allowed_tags = $allowed_protocols = array();
		$this->setAllowed( $allowed_tags, $allowed_protocols );

		if ( isset( $post->post_title ) ) {
			$post->post_title = wp_kses( $post->post_title, $allowed_tags, $allowed_protocols );
		}
		if ( isset( $post->post_content ) ) {
			$post->post_content = wp_kses( $post->post_content, $allowed_tags, $allowed_protocols );
		}
		if ( isset( $post->post_excerpt ) ) {
			$post->post_excerpt = wp_kses( $post->post_excerpt, $allowed_tags, $allowed_protocols );
		}

		$up_post = array(
			'ID' => $post->ID,
			'post_date' => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			'post_type' => $post->post_type,
			'post_category' => array(0)
		);

		if ( isset( $post->post_author ) ) {
			$up_post['post_author'] = $post->post_author;
		}
		if ( isset( $post->post_title ) ) {
			$up_post['post_title'] = $post->post_title;
		}
		if ( isset( $post->post_content ) ) {
			$up_post['post_content'] = $post->post_content;
		}
		if ( isset( $post->post_excerpt ) ) {
			$up_post['post_excerpt'] = $post->post_excerpt;
		}
		if ( isset( $post->post_status ) ) {
			$up_post['post_status'] = $post->post_status;
		}
		if ( isset( $post->post_parent ) ) {
			$up_post['post_parent'] = $post->post_parent;
		}
		if ( isset( $post->post_type ) ) {
			$up_post['post_type'] = $post->post_type;
		}

		$post_id = wp_insert_post( $up_post, true );

		if ( !is_wp_error( $post_id ) ) {
			if ( isset( $fields['removed'] )
				&& is_array( $fields['removed'] ) ) {
				// remove the fields that need to be removed
				foreach ( $fields['removed'] as $meta_key ) {
					delete_post_meta( $post_id, $meta_key );
				}
			}
			$fields['fields'] = $this->esc_data( $fields['fields'] );
			foreach ( $fields['fields'] as $meta_key => $meta_value ) {
				if ( is_array( $meta_value )
					&& ! $fields['info'][ $meta_key ]['save_single'] ) {
					foreach ( $meta_value as $meta_value_single ) {
						$meta_value_single = str_replace( '\\\\"', '"', $meta_value_single );
						$meta_value_single = wp_kses( $meta_value_single, $allowed_tags, $allowed_protocols );
						$meta_value_single = str_replace( "&amp;", "&", $meta_value_single );
						add_post_meta( $post_id, $meta_key, $meta_value_single, false /* $unique */ );
					}
				} else {
					if ( is_array( $meta_value ) ) {
						foreach ( $meta_value as &$meta_val ) {
							$meta_val = str_replace( '\\\\"', '"', $meta_val );
							$meta_val = wp_kses( $meta_val, $allowed_tags, $allowed_protocols );
							$meta_val = str_replace( "&amp;", "&", $meta_val );
						}
					} else {
						$meta_value = str_replace( '\\\\"', '"', $meta_value );
						$meta_value = wp_kses( $meta_value, $allowed_tags, $allowed_protocols );
						//avoid & to be replaced with &amp;
						$meta_value = str_replace( "&amp;", "&", $meta_value );
					}
					add_post_meta( $post_id, $meta_key, $meta_value, false /* $unique */ );
				}
			}

			if ( $taxonomies ) {
				$taxonomies = $this->esc_data( $taxonomies );
				foreach ( $taxonomies['flat'] as $tax ) {
					// attach them to post
					wp_set_post_terms( $post_id, $tax['add'], $tax['name'], false );
				}

				foreach ( $taxonomies['hierarchical'] as $tax ) {
					foreach ( $tax['add_new'] as $ii => $addnew ) {
						/**
						 * if numeric parent, then check is there such a taxonomy
						 */
						if ( is_numeric( $addnew['parent'] ) && is_object( get_term( $addnew['parent'], $tax['name'] ) ) ) {
							$pid = (int) $addnew['parent'];
							if ( $pid < 0 )
								$pid = 0;

							$result = wp_insert_term( $addnew['term'], $tax['name'], array('parent' => $pid) );
							if ( !is_wp_error( $result ) ) {
								$tax['add_new'][$ii]['id'] = $result['term_id'];
								$ind = array_search( $addnew['term'], $tax['terms'] );
								if ( $ind !== false )
									$tax['terms'][$ind] = $result['term_id'];
							}
						}
						else {
							$par_id = false;
							foreach ( $tax['add_new'] as $ii2 => $addnew2 ) {
								if ( $addnew['parent'] == $addnew2['term'] && isset( $addnew2['id'] ) ) {
									$par_id = $addnew2['id'];
									break;
								}
							}
							if ( $par_id !== false ) {
								$pid = (int) $par_id;
								if ( $pid < 0 ) {
									$pid = 0;
								}
								$result = wp_insert_term( $addnew['term'], $tax['name'], array( 'parent' => $pid ) );
							} else {
								$result = wp_insert_term( $addnew['term'], $tax['name'], array( 'parent' => 0 ) );
							}

							if ( !is_wp_error( $result ) ) {
								$tax['add_new'][$ii]['id'] = $result['term_id'];
								$ind = array_search( $addnew['term'], $tax['terms'] );
								if ( $ind !== false ) {
									$tax['terms'][ $ind ] = $result['term_id'];
								}
							}
						}
						delete_option( $tax['name'] . "_children" ); // clear the cache
					}
					// attach them to post
					wp_set_post_terms( $post_id, $tax['terms'], $tax['name'], false );
				}
			}
		}
		return ($post_id);
	}

	/**
	 * @param array $obj_terms
	 *
	 * @return array
	 */
	protected function buildTerms($obj_terms) {
        $tax_terms = array();
        foreach ( $obj_terms as $term ) {
            $tax_terms[] = array(
                'name' => $term->name,
                'count' => $term->count,
                'parent' => $term->parent,
                'term_taxonomy_id' => $term->term_taxonomy_id,
                'term_id' => $term->term_id
            );
        }
        return $tax_terms;
    }

	/**
	 * @param object $post
	 *
	 * @return array
	 */
    protected function delete_post_taxonomies($post) {
        $taxonomies = $this->getPostTaxonomies( $post );

        foreach ( $taxonomies as $taxonomy ) {
            //Delete all terms only if taxonomy does exist on frontend
            $to_delete = "new_tax_text_" . $taxonomy['name'];
	        if ( ! isset( $_POST[ $to_delete ] ) ) {
		        continue;
	        }

	        if ( count( $taxonomy['terms'] ) > 0 ) {
                $delete = array();

                foreach ( $taxonomy['terms'] as $terms ) {
                    $delete[] = $terms['term_id'];
                }
                wp_remove_object_terms( $post->ID, $delete, $taxonomy['name'] );
            }
        }
        return $taxonomies;
    }

	/**
	 * @param array $allowed_tags
	 * @param array $allowed_protocols
	 */
    protected function setAllowed(&$allowed_tags, &$allowed_protocols) {
        $__allowed_tags = wp_kses_allowed_html( 'post' );
        $__allowed_protocols = array('http', 'https', 'mailto');
        $settings_model = CRED_Loader::get( 'MODEL/Settings' );
        $settings = $settings_model->getSettings();
        $allowed_tags = isset( $settings['allowed_tags'] ) ? $settings['allowed_tags'] : $__allowed_tags;
        foreach ( $__allowed_tags as $key => $value ) {
            if ( !isset( $allowed_tags[$key] ) ) {
                unset( $__allowed_tags[$key] );
            }
        }
        $allowed_tags = $__allowed_tags;
        $allowed_protocols = $__allowed_protocols;
    }

	/**
	 * @param object $post
	 * @param array $fields
	 * @param array|null $taxonomies
	 *
	 * @return mixed
	 */
    public function updatePost($post, $fields, $taxonomies = null) {
        global $user_ID;
        $allowed_tags = $allowed_protocols = array();
        $this->setAllowed( $allowed_tags, $allowed_protocols );

        if ( isset( $post->post_title ) )
            $post->post_title = wp_kses( $post->post_title, $allowed_tags, $allowed_protocols );
        if ( isset( $post->post_content ) )
            $post->post_content = wp_kses( $post->post_content, $allowed_tags, $allowed_protocols );
        if ( isset( $post->post_excerpt ) )
            $post->post_excerpt = wp_kses( $post->post_excerpt, $allowed_tags, $allowed_protocols );

        $post_id = $post->ID;
        $up_post = array(
            'ID' => $post->ID,
            'post_type' => $post->post_type
        );
	    if ( isset( $post->post_author ) ) {
		    $up_post['post_author'] = $post->post_author;
	    }
	    if ( isset( $post->post_status ) ) {
		    $up_post['post_status'] = $post->post_status;
	    }
	    if ( isset( $post->post_title ) ) {
		    $up_post['post_title'] = $post->post_title;
	    }
	    if ( isset( $post->post_content ) ) {
		    $up_post['post_content'] = $post->post_content;
	    }
	    if ( isset( $post->post_excerpt ) ) {
		    $up_post['post_excerpt'] = $post->post_excerpt;
	    }
	    if ( isset( $post->post_parent ) ) {
		    $up_post['post_parent'] = $post->post_parent;
	    }

        wp_update_post( $up_post );

        if ( isset( $fields['removed'] ) && is_array( $fields['removed'] ) ) {
            // remove the fields that need to be removed
            foreach ( $fields['removed'] as $meta_key ) {
                delete_post_meta( $post_id, $meta_key );
            }
        }

        $fields['fields'] = $this->esc_data( $fields['fields'] );
        foreach ( $fields['fields'] as $meta_key => $meta_value ) {
            delete_post_meta( $post_id, $meta_key );
            if ( is_array( $meta_value ) && !$fields['info'][$meta_key]['save_single'] ) {
                foreach ( $meta_value as $meta_value_single ) {
                    $meta_value_single = str_replace( '\\\\"', '"', $meta_value_single );
                    $meta_value_single = wp_kses( $meta_value_single, $allowed_tags, $allowed_protocols );
                    $meta_value_single = str_replace( "&amp;", "&", $meta_value_single );

                    add_post_meta( $post_id, $meta_key, $meta_value_single, false /* $unique */ );
                }
            } else {
                if ( is_array( $meta_value ) ) {
                    foreach ( $meta_value as &$meta_val ) {
                        $meta_val = str_replace( '\\\\"', '"', $meta_val );
                        $meta_val = wp_kses( $meta_val, $allowed_tags, $allowed_protocols );
                        $meta_val = str_replace( "&amp;", "&", $meta_val );
                    }
                } else {
                    $meta_value = str_replace( '\\\\"', '"', $meta_value );
                    $meta_value = wp_kses( $meta_value, $allowed_tags, $allowed_protocols );
                    $meta_value = str_replace( "&amp;", "&", $meta_value );
                }
                add_post_meta( $post_id, $meta_key, $meta_value, false /* $unique */ );
            }
        }

        if ( $taxonomies ) {
            $post_taxonomies = $this->delete_post_taxonomies( $post );

            $taxonomies = $this->esc_data( $taxonomies );
            foreach ( $taxonomies['flat'] as $tax ) {
                $old_terms = wp_get_post_terms( $post_id, $tax['name'], array("fields" => "names") );
                // remove deleted terms
                $new_terms = (!empty( $old_terms ) && (isset( $tax['remove'] ) && !empty( $tax['remove'] ))) ? array_diff( $old_terms, $tax['remove'] ) : array();
                // add new terms
                $new_terms = (!empty( $new_terms )) ? array_merge( $new_terms, $tax['add'] ) : $tax['add'];
                // attach them to post
                wp_set_post_terms( $post_id, $new_terms, $tax['name'], false );
            }

            if ( !empty( $taxonomies['hierarchical'] ) ) {
                foreach ( $taxonomies['hierarchical'] as $tax ) {
                    foreach ( $tax['add_new'] as $ii => $addnew ) {
                        $_gterms = get_term( $addnew['parent'], $tax['name'] );
                        if ( is_numeric( $addnew['parent'] ) && is_object( $_gterms ) ) {
                            $pid = (int) $addnew['parent'];
                            if ( $pid < 0 )
                                $pid = 0;
                            $result = wp_insert_term( $addnew['term'], $tax['name'], array('parent' => $pid) );
                            if ( !is_wp_error( $result ) ) {
                                $tax['add_new'][$ii]['id'] = $result['term_id'];
                                $ind = array_search( $addnew['term'], $tax['terms'] );
                                if ( $ind !== false ) {
                                    $tax['terms'][$ind] = $result['term_id'];
                                }
                            } else {
                                continue;
                            }
                        } else {
                            $par_id = false;
                            foreach ( $tax['add_new'] as $ii2 => $addnew2 ) {
                                if ( $addnew['parent'] == $addnew2['term'] && isset( $addnew2['id'] ) ) {
                                    $par_id = $addnew2['id'];
                                    break;
                                }
                            }

                            $pid = 0;
                            if ( $par_id !== false ) {
                                $pid = (int) $par_id;
	                            if ( $pid < 0 ) {
		                            $pid = 0;
	                            }
                            }
                            $result = wp_insert_term( $addnew['term'], $tax['name'], array('parent' => $pid) );
                            if ( !is_wp_error( $result ) ) {
                                $tax['add_new'][$ii]['id'] = $result['term_id'];
                                $ind = array_search( $addnew['term'], $tax['terms'] );
                                if ( $ind !== false ) {
                                    $tax['terms'][$ind] = $result['term_id'];
                                }
                            } else {
                                continue;
                            }
                        }
                    }
                    // attach them to post
                    wp_set_post_terms( $post_id, $tax['terms'], $tax['name'], false );
                }
            } else {
                //Fixed set uncategorized, check if category is a term of this post
	            if ( isset( $post_taxonomies['category'] ) && ( ! has_category( '', $post_id ) ) ) {
		            wp_set_post_categories( $post_id, array( 1 ) );
	            }
            }
        }

        return ($post_id);
    }

}
