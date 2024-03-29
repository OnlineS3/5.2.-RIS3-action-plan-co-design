<?php

/**
 * Class CRED_Notification_Manager
 *
 * is responsible of mail notification and is used by CRED to send them as described into
 * cred post/user form notification settings.
 *
 * get_attached_data/set_attached_data attach notification data to a post/user form in order to
 * send notification when is triggered *
 *
 * @since 1.9.6
 */
class CRED_Notification_Manager_Post extends CRED_Notification_Manager_Base {

	private static $instance;
	protected $model;

	const FORM_PREFIX = 'cred-form-';

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * CRED_Notification_Manager_Post constructor.
	 *
	 * @param CRED_Forms_Model|null $model
	 */
	public function __construct( CRED_Forms_Model $model = null ) {
		parent::__construct();
		$this->model = ( null === $model ) ? CRED_Loader::get( 'MODEL/Forms' ) : $model;
	}

	/**
	 * @param int $meta_id
	 * @param int $post_id
	 * @param string $meta_key
	 * @param string $_meta_value
	 */
	public function updated_meta( $meta_id, $post_id, $meta_key, $_meta_value ) {
		switch ( $meta_key ) {
			case '_edit_lock':
			case '_toolset_edit_last':
				break;
			default:
				$this->check_for_notifications( $post_id, get_post( $post_id ) );
				break;
		}
	}

	/**
	 * Function called when an action post status switch is triggered (like 'draft_to_publish')
	 *
	 * @param WP_Post $post
	 */
	public function check_for_notifications_by_status_switch( $post ) {
		if ( isset( $post )
			&& $post instanceof WP_Post
		) {
			$this->event = 'post_modified';
			$this->check_for_notifications( $post->ID, $post );
		}
	}

	/**
	 * @param int $post_id
	 * @param object $post
	 */
	public function check_for_notifications( $post_id, $post ) {
		if ( isset( $post )
			&& $post->post_type == CRED_FORMS_CUSTOM_POST_NAME ) {
			return;
		}

		$model = $this->get_current_model();

		$attachedData = $model->getAttachedData( $post_id );
		if ( ! $attachedData ) {
			return;
		}

		$notification = false;
		foreach ( $attachedData as $form_id => $data ) {
			$notification = $model->getFormCustomField( $form_id, 'notification' );
			break;
		}

		if ( $notification ) {
			$this->trigger_notifications( $post_id, array(
				'notification' => $notification,
				'form_id' => $form_id,
				'post' => $post,
			) );
		}

		// keep up-to-date with notification settings for form and post field values
		$this->update( $post_id, $form_id );
	}

	/**
	 * @return CRED_Forms_Model
	 */
	protected function get_current_model() {
		return $this->model;
	}

	/**
	 * Returns a post or user object by generic $object_id and $is_user_form inputs
	 *
	 * @param int $post_id
	 *
	 * @return WP_Post|bool
	 */
	protected function get_form_object( $post_id ) {
		$object = get_post( (int) $post_id );

		return $object;
	}

	/**
	 * Prepare attached hashed snapshot data field referred to current time form fields
	 * in order to check if something has changed in them
	 *
	 * @param int $form_id
	 * @param int $post_id
	 * @param array $notifications
	 *
	 * @return array|null
	 */
	protected function get_attached_data( $form_id, $post_id, $notifications = array() ) {
		$model = $this->get_current_model();

		$object = $this->get_form_object( $post_id );
		if ( ! $object ) {
			return null;
		}

		if ( empty( $notifications ) ) {
			$notifications = $this->get_notification_data_by_model( $form_id, $model );
		}

		$attachedData = array();
		$snapshotFields = array();
		if ( ! empty( $notifications ) ) {
			foreach ( $notifications as $index => $notification ) {
				if ( isset( $notification[ 'event' ][ 'condition' ] ) ) {
					foreach ( $notification[ 'event' ][ 'condition' ] as $jj => $condition ) {
						if ( isset( $condition[ 'only_if_changed' ] ) &&
							$condition[ 'only_if_changed' ] &&
							! in_array( $condition[ 'field' ], $snapshotFields )
						) {
							// load all fields that have a changing condition from all notifications at once
							$snapshotFields[] = $condition[ 'field' ];
						}
					}
				}
			}

			$fields = $model->get_object_fields( $post_id, $snapshotFields );
			$snapshotFieldsValuesHash = $this->fold( $this->do_hash( $fields ) );
			$attachedData[ $form_id ] = array(
				'cred_form' => $form_id,
				'current' => array(
					'time' => time(),
					'post_status' => $object->post_status,
					'snapshot' => $snapshotFieldsValuesHash,
				),
			);
		}

		return $attachedData;
	}

	/**
	 * @param int $post_id
	 * @param array $attached_data
	 *
	 * @return bool
	 */
	protected function save_attached_data( $post_id, $attached_data ) {
		if ( empty( $attached_data ) ) {
			return false;
		}

		//Removing hooks before setAttachedData in order to avoid infinite loops
		//because of update_meta called in setAttachedData
		$model = $this->get_current_model();
		CRED_Notification_Manager_Utils::get_instance()->remove_hooks();
		$is_attached_data_saved = $model->setAttachedData( (int) $post_id, $attached_data );
		CRED_Notification_Manager_Utils::get_instance()->add_hooks();

		return $is_attached_data_saved;
	}

	/**
	 * @param int $post_id
	 * @param array $attached_data
	 *
	 * @return bool
	 */
	protected function delete_attached_data( $post_id, $attached_data = array() ) {
		if ( ! empty( $attached_data ) ) {
			return false;
		}

		$model = $this->get_current_model();

		return $model->removeAttachedData( (int) $post_id );
	}

	/**
	 * @param int $post_id
	 * @param array $data
	 * @param null $attached_data
	 */
	public function trigger_notifications( $post_id, $data, $attached_data = null ) {
		$form_id = $data[ 'form_id' ];
		$model = $this->get_current_model();

		if ( empty( $post_id ) ) {
			return;
		}

		if ( isset( $data[ 'post' ] ) ) {
			$object = $data[ 'post' ];
		} else {
			$object = get_post( $post_id );
		}

		if ( ! isset( $object ) ) {
			return;
		}

		if ( empty( $attached_data ) ) {
			$attached_data = $model->getAttachedData( $post_id );
		}

		// trigger for this event, if set
		if ( isset( $data[ 'event' ] ) ) {
			$this->event = $data[ 'event' ];
		} else {
			//Event can be set by check_for_notifications_by_status_switch call
			$this->event = ( isset( $this->event ) && ! empty( $this->event ) ) ? $this->event : false;
		}

		$notification = isset( $data[ 'notification' ] ) ? $data[ 'notification' ] : false;
		if (
			! $attached_data
			|| ! $notification
			|| ! isset( $notification->enable )
			|| ! $notification->enable
			|| empty( $notification->notifications )
		) {
			return;
		}

		$notifications_to_send = array();
		foreach ( $notification->notifications as $key => $single_notification ) {
			if ( isset( $single_notification[ 'disabled' ] )
				&& $single_notification[ 'disabled' ] == 1
			) {
				continue;
			}

			$snapshot = isset( $attached_data[ $form_id ] ) ? $attached_data[ $form_id ][ 'current' ] : array();

			$is_correct_notification_event_type = ( $single_notification[ 'event' ][ 'type' ] == $this->event );

			$is_payment_and_order_complete = ( $single_notification[ 'event' ][ 'type' ] == 'payment_complete'
				&& $this->event == 'order_completed' );

			$is_order_modified = ( $is_correct_notification_event_type
				&& $this->event == 'order_modified'
				&& isset( $data[ 'data_order' ] )
				&& isset( $data[ 'data_order' ][ 'new_status' ] )
				&& $data[ 'data_order' ][ 'new_status' ] == $single_notification[ 'event' ][ 'order_status' ]
				&& $data[ 'data_order' ][ 'previous_status' ] != $data[ 'data_order' ][ 'new_status' ] );

			$is_post_modified = ( $is_correct_notification_event_type
				&& $this->event == 'post_modified'
				&& isset( $object->post_status )
				&& $object->post_status == $single_notification[ 'event' ][ 'post_status' ]
				&& isset( $snapshot[ 'post_status' ] )
				&& $snapshot[ 'post_status' ] != $object->post_status );

			$is_form_submit = ( $is_correct_notification_event_type
				&& $this->event == 'form_submit' );

			$is_order_created = ( $is_correct_notification_event_type
				&& $this->event == 'order_created' );

			if ( $is_payment_and_order_complete
				|| $is_order_modified
				|| $is_post_modified
				|| $is_form_submit
				|| $is_order_created
			) {
				$notifications_to_send[] = $single_notification;
			} else {
				if ( isset( $single_notification[ 'event' ] ) ) {
					$condition_fields = array();
					$notification_condition_fields = array();
					if ( isset( $single_notification[ 'event' ][ 'condition' ] )
						&& ! empty( $single_notification[ 'event' ][ 'condition' ] )
					) {
						foreach ( $single_notification[ 'event' ][ 'condition' ] as $key => $condition ) {
							$condition_fields[] = $condition[ 'field' ];
						}
						$notification_condition_fields = $model->get_object_fields( $post_id, $condition_fields );
					}

					$send_notification = $this->evaluate_conditions( $single_notification, $notification_condition_fields, $snapshot );

					if ( $send_notification ) {
						$notifications_to_send[] = $single_notification;
					}
				}
			}
		}

		if ( ! empty( $notifications_to_send ) ) {
			$this->send_notifications( $post_id, $form_id, $notifications_to_send );
		}
	}

	/**
	 * Send Notification when a Post ID is just created
	 *
	 * @param int $post_id
	 * @param int $form_id
	 * @param array $notificationsToSent
	 *
	 * @return bool
	 */
	public function send_notifications( $post_id, $form_id, $notificationsToSent ) {
		// custom action hooks here, for 3rd-party integration
		// get Mailer
		$mailer = CRED_Loader::get( 'CLASS/Mail_Handler' );

		$mailer->setFormId( $form_id );
		$mailer->setPostId( $post_id );

		// get current user
		$user = $this->get_current_user_data();

		$now = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

		// get some data for placeholders
		$form_post = get_post( $form_id );
		$form_title = ( $form_post ) ? $form_post->post_title : '';
		$link = get_permalink( $post_id );
		$title = get_the_title( $post_id );
		$admin_edit_link = CRED_CRED::getPostAdminEditLink( $post_id );

		$subject_placeholders = array_merge( $this->get_placeholders_user_array( $user ), $this->get_placeholders_post_array( $post_id, $title, $form_title, $now ) );
		// placeholder codes, allow to add custom
		$data_subject = $this->get_data_subject_applying_filters( $post_id, $form_id, $subject_placeholders );

		$object_placeholders = array_merge( $this->get_placeholders_user_array( $user ), $this->get_placeholders_post_array( $post_id, $title, $form_title, $now, $link, $admin_edit_link ) );
		// placeholder codes, allow to add custom
		$data_body = $this->get_data_body_applying_filters( $post_id, $form_id, $object_placeholders );
		
		$send_notification_result = true;
		foreach ( $notificationsToSent as $notification_counter => $notification ) {

			$notification[ 'notification_counter' ] = $notification_counter;
			$notification[ 'form_id' ] = $form_id;
			$notification[ 'post_id' ] = $post_id;

			/*
			 * send_notification could be called from different hooks (save_posts / updated_post_meta)
			 * checking notification_queue will avoid to send duplicated notifications
			 */
			$hashed_notification_value = hash( 'md5', serialize( $notification ) );
			if ( in_array( $hashed_notification_value, $this->notification_sent_record ) ) {
				continue;
			}
			$this->notification_sent_record[] = $hashed_notification_value;

			//Checks for old notification (back compatibility)
			$notification_name = isset( $notification[ 'name' ] ) ? $notification[ 'name' ] : '';
			$mailer->setNotificationName( $notification_name );
			$mailer->setNotificationNum( $notification_counter );

			// bypass if nothing
			if (
				! $notification
				|| empty( $notification )
				|| ! ( isset( $notification[ 'to' ][ 'type' ] )
					|| isset( $notification[ 'to' ][ 'author' ] ) )
			) {
				continue;
			}

			// parse Notification Fields
			if ( ! isset( $notification[ 'to' ][ 'type' ] ) ) {
				$notification[ 'to' ][ 'type' ] = array();
			}
			if ( ! is_array( $notification[ 'to' ][ 'type' ] ) ) {
				$notification[ 'to' ][ 'type' ] = (array) $notification[ 'to' ][ 'type' ];
			}

			// reset mail handler
			$mailer->reset();
			$mailer->setHTML( true, false );
			$recipients = array();

			$this->try_add_author_to_recipients( $post_id, $form_id, $notification, $recipients );
			$this->try_add_mail_field_to_recipients( $post_id, $notification, $recipients );
			$this->try_add_wp_user_to_recipients( $notification, $recipients );
			$this->try_add_user_id_field_to_recipients( $post_id, $notification, $recipients );
			$this->try_add_specific_mail_to_recipients( $notification, $recipients );

			// add custom recipients by 3rd-party
			$recipients = apply_filters( 'cred_notification_recipients', $recipients, $notification, $form_id, $post_id );
			if ( ! $recipients
				|| empty( $recipients )
			) {
				continue;
			}

			$this->build_recipients( $recipients );
			$mailer->addRecipients( $recipients );

			if ( isset( $_POST[ CRED_StaticClass::PREFIX . 'cred_container_id' ] ) ) {
				$notification[ 'mail' ][ 'body' ] = str_replace( "[cred-container-id]", CRED_StaticClass::$_cred_container_id, $notification[ 'mail' ][ 'body' ] );
			}

			global $post;
			$oldpost = null;
			if ( $post ) {
				$oldpost = clone $post;
				$post = get_post( $post_id );
			}

			// build SUBJECT
			$subject = '';
			if ( isset( $notification[ 'mail' ][ 'subject' ] ) ) {
				$subject = $notification[ 'mail' ][ 'subject' ];
			}

			// build BODY
			$body = '';
			if ( isset( $notification[ 'mail' ][ 'body' ] ) ) {
				$body = $notification[ 'mail' ][ 'body' ];
			}

			$mail_subject = CRED_StaticClass::unesc_meta_data( $notification[ 'mail' ][ 'subject' ] );
			$mail_body = CRED_StaticClass::unesc_meta_data( $notification[ 'mail' ][ 'body' ] );

			$hashSubject = CRED_Helper::strHash( "notification-subject-" . $form_id . "-" . $notification_counter );
			$hashBody = CRED_Helper::strHash( "notification-body-" . $form_id . "-" . $notification_counter );

			$form = get_post( $form_id );
			$prefix = self::FORM_PREFIX;
			$context = $prefix . $form->post_title . '-' . $form_id;

			$subject = cred_translate( 'CRED Notification Subject ' . $hashSubject, $mail_subject, $context );
			$body = cred_translate( 'CRED Notification Body ' . $hashBody, $mail_body, $context );

			// replace placeholders
			$subject = $this->replace_placeholders( $subject, $data_subject );

			// replace placeholders
			$body = $this->replace_placeholders( $body, $data_body );

			if ( defined( 'WPCF_EMBEDDED_ABSPATH' )
				&& WPCF_EMBEDDED_ABSPATH
			) {
				require_once WPCF_EMBEDDED_ABSPATH . '/frontend.php';
			}

			// parse shortcodes if necessary relative to $post_id
			$subject = $this->render_with_post( $subject, $post_id, false );
			$subject = stripslashes( $subject );

			// parse shortcodes/rich text if necessary relative to $post_id
			$body = $this->render_with_post( $body, $post_id );
			$body = stripslashes( $body );

			$mailer->setSubject( $subject );
			$mailer->setBody( $body );

			$_from = $this->get_mail_form_by_notification( $notification );
			if ( ! empty( $_from ) ) {
				$mailer->setFrom( $_from );
			}

			// send it
			$_send_result = $mailer->send();

			if ( isset( $oldpost ) ) {
				$post = clone $oldpost;
				unset( $oldpost );
			}

			if ( $_send_result !== true ) {
				update_option( '_' . $form_id . '_last_mail_error', $_send_result );
			}

			$send_notification_result = $send_notification_result && $_send_result;
		}

		// custom action hooks here, for 3rd-party integration
		do_action( 'cred_after_send_notifications', $post_id );

		return $send_notification_result;
	}

	/**
	 * Try to add author notification to recipients
	 *
	 * @param int $post_id
	 * @param int $form_id
	 * @param array $notification
	 * @param array $recipients
	 *
	 * @return bool
	 */
	protected function try_add_author_to_recipients( $post_id, $form_id, $notification, &$recipients ) {
		if ( isset( $notification[ 'to' ][ 'author' ] )
			&& 'author' == $notification[ 'to' ][ 'author' ]
		) {
			$author_post_id = isset( $_POST[ 'form_' . $form_id . '_referrer_post_id' ] ) ? $_POST[ 'form_' . $form_id . '_referrer_post_id' ] : 0;
			$author_post = ( 0 == $author_post_id && $post_id ) ? get_post( $post_id ) : get_post( $author_post_id );
			$author_id = $author_post->post_author;

			if ( $author_id ) {
				$_to_type = 'to';
				$user_info = get_userdata( $author_id );

				$_addr_name = ( isset( $user_info ) && isset( $user_info->user_firstname ) && ! empty( $user_info->user_firstname ) ) ? $user_info->user_firstname : false;
				$_addr_lastname = ( isset( $user_info ) && isset( $user_info->user_lasttname ) && ! empty( $user_info->user_lasttname ) ) ? $user_info->user_lastname : false;
				$_addr = $user_info->user_email;

				if ( isset( $_addr ) ) {
					$recipients[] = array(
						'to' => $_to_type,
						'address' => $_addr,
						'name' => $_addr_name,
						'lastname' => $_addr_lastname,
					);

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Try to add mail field notification to recipients
	 *
	 * @param int $post_id
	 * @param array $notification
	 * @param array $recipients
	 * @param WP_User|null $the_user
	 *
	 * @return bool
	 */
	protected function try_add_mail_field_to_recipients( $post_id, $notification, &$recipients, $the_user = null ) {
		// notification to a mail field (which is saved as post meta)
		if (
			in_array( 'mail_field', $notification[ 'to' ][ 'type' ] )
			&& isset( $notification[ 'to' ][ 'mail_field' ][ 'address_field' ] )
			&& ! empty( $notification[ 'to' ][ 'mail_field' ][ 'address_field' ] )
		) {
			$_to_type = 'to';
			$_addr = false;
			$_addr_name = false;
			$_addr_lastname = false;

			$_addr = $this->model->getPostMeta( $post_id, $notification[ 'to' ][ 'mail_field' ][ 'address_field' ], false );

			if ( empty( $_addr ) ) {
				return false;
			}

			if (
				isset( $notification[ 'to' ][ 'mail_field' ][ 'to_type' ] )
				&& in_array( $notification[ 'to' ][ 'mail_field' ][ 'to_type' ], array( 'to', 'cc', 'bcc' ) )
			) {
				$_to_type = $notification[ 'to' ][ 'mail_field' ][ 'to_type' ];
			}

			if (
				isset( $notification[ 'to' ][ 'mail_field' ][ 'name_field' ] )
				&& ! empty( $notification[ 'to' ][ 'mail_field' ][ 'name_field' ] )
				&& '###none###' != $notification[ 'to' ][ 'mail_field' ][ 'name_field' ]
			) {
				$_addr_name = $this->model->getPostMeta( $post_id, $notification[ 'to' ][ 'mail_field' ][ 'name_field' ] );
			}

			if (
				isset( $notification[ 'to' ][ 'mail_field' ][ 'lastname_field' ] )
				&& ! empty( $notification[ 'to' ][ 'mail_field' ][ 'lastname_field' ] )
				&& '###none###' != $notification[ 'to' ][ 'mail_field' ][ 'lastname_field' ]
			) {
				$_addr_lastname = $this->model->getPostMeta( $post_id, $notification[ 'to' ][ 'mail_field' ][ 'lastname_field' ] );
			}

			// add to recipients
			foreach( $_addr as $address_to_notify ) {
				$recipients[] = array(
					'to' => $_to_type,
					'address' => $address_to_notify,
					'name' => $_addr_name,
					'lastname' => $_addr_lastname,
				);
			}

			return true;
		}

		return false;
	}

	/**
	 * Try to add notification user_id_field to recipients
	 *
	 * @param int $post_id
	 * @param array $notification
	 * @param array $recipients
	 *
	 * @return bool
	 */
	protected function try_add_user_id_field_to_recipients( $post_id, $notification, &$recipients ) {
		// notification to an exisiting wp user
		if ( in_array( 'user_id_field', $notification[ 'to' ][ 'type' ] ) ) {
			$_to_type = 'to';
			$_addr = false;
			$_addr_name = false;
			$_addr_lastname = false;

			if (
				isset( $notification[ 'to' ][ 'user_id_field' ][ 'to_type' ] ) &&
				in_array( $notification[ 'to' ][ 'user_id_field' ][ 'to_type' ], array( 'to', 'cc', 'bcc' ) )
			) {
				$_to_type = $notification[ 'to' ][ 'user_id_field' ][ 'to_type' ];
			}

			$user_id = @trim( $this->model->getPostMeta( $post_id, $notification[ 'to' ][ 'user_id_field' ][ 'field_name' ] ) );
			if ( $user_id ) {
				$user_info = get_userdata( $user_id );
				if ( $user_info ) {
					$_addr = ( isset( $user_info->user_email ) && ! empty( $user_info->user_email ) ) ? $user_info->user_email : false;
					$_addr_name = ( isset( $user_info->user_firstname ) && ! empty( $user_info->user_firstname ) ) ? $user_info->user_firstname : false;
					$_addr_lastname = ( isset( $user_info->user_lasttname ) && ! empty( $user_info->user_lasttname ) ) ? $user_info->user_lastname : false;

					// add to recipients
					$recipients[] = array(
						'to' => $_to_type,
						'address' => $_addr,
						'name' => $_addr_name,
						'lastname' => $_addr_lastname,
					);

					return true;
				}
			}
		}

		return false;
	}
}
