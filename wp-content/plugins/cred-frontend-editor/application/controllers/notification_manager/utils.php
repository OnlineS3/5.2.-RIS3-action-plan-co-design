<?php

/**
 * Class CRED_Notification_Manager_Utils used to initialize/manage hooks
 */
class CRED_Notification_Manager_Utils {

	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * CRED_Notification_Manager_Utils constructor.
	 */
	public function __construct() {
		$this->initialize();
	}

	/**
	 * Init hooks
	 */
	public function initialize() {
		add_action( 'wp_loaded', array( $this, 'add_hooks' ), 10 );
	}

	public function add_hooks() {

		

		$cred_notification_post = CRED_Notification_Manager_Post::get_instance();
		add_action( 'save_post', array( $cred_notification_post, 'check_for_notifications' ), 10, 2 );

		$cred_notification_user = CRED_Notification_Manager_User::get_instance();
		add_action( 'profile_update', array( $cred_notification_user, 'check_for_notifications' ), 10, 2 );

		/**
		 * check if status is changed
		 */
		$check_to_status = array( 'publish', 'pending', 'draft', 'private' );
		$check_from_status = array_merge( $check_to_status, array( 'new', 'future', 'trash' ) );
		foreach ( $check_from_status as $from ) {
			foreach ( $check_to_status as $to ) {
				if ( $from !== $to ) {
					$action = sprintf( '%s_to_%s', $from, $to );
					add_action( $action, array(
						$cred_notification_post,
						'check_for_notifications_by_status_switch',
					), 10, 1 );
				}
			}
		}

		$post_types = get_post_types( array(
			'public' => true,
			'publicly_queryable' => true,
			'_builtin' => true,
		), 'names', 'or' );

		foreach ( $post_types as $pt ) {
			$updated_meta = sprintf( "updated_%s_meta", $pt );
			add_action( $updated_meta, array( $cred_notification_post, 'updated_meta' ), 20, 4 );
		}
	}

	public function remove_hooks() {
		$cred_notification_post = CRED_Notification_Manager_Post::get_instance();
		remove_action( 'save_post', array( $cred_notification_post, 'check_for_notifications' ), 10, 2 );

		$cred_notification_user = CRED_Notification_Manager_User::get_instance();
		remove_action( 'profile_update', array( $cred_notification_user, 'check_for_notifications' ), 10, 2 );

		$check_to_status = array( 'publish', 'pending', 'draft', 'private' );
		$check_from_status = array_merge( $check_to_status, array( 'new', 'future', 'trash' ) );
		foreach ( $check_from_status as $from ) {
			foreach ( $check_to_status as $to ) {
				if ( $from !== $to ) {
					$action = sprintf( '%s_to_%s', $from, $to );
					remove_action( $action, array( $cred_notification_post, 'check_for_notifications' ), 10, 1 );
				}
			}
		}

		$post_types = get_post_types( array(
			'public' => true,
			'publicly_queryable' => true,
			'_builtin' => true,
		), 'names', 'or' );

		foreach ( $post_types as $pt ) {
			$updated_meta = sprintf( "updated_%s_meta", $pt );
			remove_action( $updated_meta, array( $cred_notification_post, 'updated_meta' ), 20, 4 );
		}
	}
}