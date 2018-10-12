<?php
/**
 * REST API: MYFS_REST_User_Controller class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

/**
 * //Core class to access post types via the REST API.
 *
 * @since 4.7.0
 *
 * @see WP_REST_Controller
 */
class MYFS_REST_User_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 */
	public function __construct() {
		$this->namespace = 'myfs-app/v1';
		$this->rest_base = 'users';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 4.7.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(
					'group_id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
			),
			//'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/profile-fields', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_profile_schema' ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/reg-form', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_reg_form' ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/reset-password', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'reset_password' ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			//'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<id>[\d]+)/block', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'block_user' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'unblock_user' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			//'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Checks whether a given request has permission to read types.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( is_user_logged_in() )
			return true;
		else
			return new WP_Error( 'user_not_logged_in', __( 'You need to be logged in to access this.' ), array( 'status' => 400 ) );
	}

	/**
	 * Checks whether a given request has permission to read types.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * Checks whether a given request has permission to read types.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {

		if ( !is_user_logged_in() )
			return new WP_Error( 'user_not_authorized', __( 'You don\'t have permission to do this.' ), array( 'status' => 400 ) );

		if ( $request['id'] != get_current_user_id() && !current_user_can( 'administrator' ) )
			return new WP_Error( 'user_not_authorized', __( 'You don\'t have permission to do this.' ), array( 'status' => 400 ) );

		return true;
	}

	/**
	 * Checks whether a given request has permission to read types.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {

		if ( !is_user_logged_in() )
			return new WP_Error( 'user_not_authorized', __( 'You don\'t have permission to do this.' ), array( 'status' => 400 ) );

		if ( $request['id'] != get_current_user_id() && !current_user_can( 'administrator' ) )
			return new WP_Error( 'user_not_authorized', __( 'You don\'t have permission to do this.' ), array( 'status' => 400 ) );

		return true;
	}

	/**
	 * 
	 * 
	 * @param array $request Options for the function.
	 * @return 
	 */
	public function block_user( $request ) {
		$user_id = get_current_user_id();
		
		$blocked = get_user_meta( $user_id, 'blocked_user', false );

		if ( !empty( $blocked ) && in_array( $request['id'], $blocked ) )
			return new WP_Error( 'duplicate_block', __( 'You have already blocked this user.' ), array( 'status' => 400 ) );
			
		add_user_meta( $user_id, 'blocked_user', $request['id'] );
/*

		$reports = array();
		$user_id = get_current_user_id();
		$previous = bp_activity_get_meta( $request['id'], "report", true );

		if ( $previous ) {
			$reports = unserialize( $previous );

			if ( array_key_exists( $user_id, $reports ) )
				return new WP_Error( 'duplicate_report', __( 'You have already reported this activity.' ), array( 'status' => 400 ) );

			$reports = unserialize( $previous );

			$reports[$user_id] = $request['report'];

			bp_activity_update_meta( $request['id'], "report", serialize( $reports ) );
		} else {
			$reports[$user_id] = $request['report'];
			bp_activity_add_meta( $request['id'], "report", serialize( $reports ) );
		}

		return new WP_REST_Response( array_pop( self::get_activity_from_params("single", 1, null, null, $request['id'], 'all', null) ), 200 );
*/
		return true;
	}

	/**
	 * 
	 * 
	 * @param array $request Options for the function.
	 * @return 
	 */
	public function unblock_user( $request ) {
		$user_id = get_current_user_id();
		
		$blocked = delete_user_meta( $user_id, 'blocked_user', $request['id'] );
/*
		if ( !empty( $blocked ) && in_array( $request['id'], $blocked ) )
			return new WP_Error( 'duplicate_block', __( 'You have already blocked this user.' ), array( 'status' => 400 ) );
			
		add_user_meta( $user_id, 'blocked_user', $request['id'] );


		$reports = array();
		$user_id = get_current_user_id();
		$previous = bp_activity_get_meta( $request['id'], "report", true );

		if ( $previous ) {
			$reports = unserialize( $previous );

			if ( array_key_exists( $user_id, $reports ) )
				return new WP_Error( 'duplicate_report', __( 'You have already reported this activity.' ), array( 'status' => 400 ) );

			$reports = unserialize( $previous );

			$reports[$user_id] = $request['report'];

			bp_activity_update_meta( $request['id'], "report", serialize( $reports ) );
		} else {
			$reports[$user_id] = $request['report'];
			bp_activity_add_meta( $request['id'], "report", serialize( $reports ) );
		}

		return new WP_REST_Response( array_pop( self::get_activity_from_params("single", 1, null, null, $request['id'], 'all', null) ), 200 );
*/
		return true;
	}

	/**
	 *
	 *
	 * @since
	 *
	 * @param
	 * @return
	 */
	public function update_item( $request ) {

		if ( !empty( $_FILES ) ) {
			$_POST['action'] = 'bp_avatar_upload';
			$bp = buddypress();

			if ( ! isset( $bp->avatar_admin ) ) {
				$bp->avatar_admin = new stdClass();
				$bp->avatar_admin->step = 'upload-image';
			}

			add_filter('xprofile_avatar_upload_dir', array($this, 'set_xprofile_avatar_upload_dir'), 10, 1);

			$result = bp_core_avatar_handle_upload($_FILES, 'xprofile_avatar_upload_dir');

			$args = array(
				'item_id'       => bp_loggedin_user_id(),
				'original_file' => bp_get_avatar_to_crop_src(),
				'crop_x'        => 0,
				'crop_y'        => 0,
				'crop_w'        => 1000,
				'crop_h'        => 1000
			);
			$result = bp_core_avatar_handle_crop( $args );
		}

		xprofile_set_field_data( 2,  $request['id'],  $_POST['field_2'], false );
		xprofile_set_field_data( 12,  $request['id'],  $_POST['field_12'], false );

		//return new WP_REST_Response( $result, 200 );
		return new WP_REST_Response( array_pop ( $this->get_users( $request ) ), 200 );
	}

	public function set_xprofile_avatar_upload_dir( $params ) {
		if ( empty( $user_id ) ) {
			$user_id = bp_loggedin_user_id();
		}

		// Failsafe against accidentally nooped $directory parameter.
		if ( empty( $directory ) ) {
			$directory = 'avatars';
		}

		$path      = bp_core_avatar_upload_path() . '/' . $directory. '/' . $user_id;
		$newbdir   = $path;
		$newurl    = bp_core_avatar_url() . '/' . $directory. '/' . $user_id;
		$newburl   = $newurl;
		$newsubdir = '/' . $directory. '/' . $user_id;

		/**
		 * Filters the avatar upload directory for a user.
		 *
		 * @since 1.1.0
		 *
		 * @param array $value Array containing the path, URL, and other helpful settings.
		 */
		return array(
			'path'    => $path,
			'url'     => $newurl,
			'subdir'  => $newsubdir,
			'basedir' => $newbdir,
			'baseurl' => $newburl,
			'error'   => false
		);
	}

	/**
	 * return new WP_REST_Response( new WP_Error( 'duplicate_email' , __( 'That email is already in use.' ) , 400 );
	 * new WP_Error( 'duplicate_email' , __( 'That email is already in use.' ), array( 'status' => 400 ) );
	 * @since
	 *
	 * @param
	 * @return
	 */
	public function reset_password( $request ) {
		$errors = new WP_Error();

		if ( empty( $request['user_login'] ) || ! is_string( $request['user_login'] ) ) {
			//$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.'));
			return new WP_Error( 'empty_username' , __( 'Enter a username or email address.' ), array( 'status' => 400 ) );
		} elseif ( strpos( $request['user_login'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( wp_unslash( $request['user_login'] ) ) );
		} else {
			$login = trim($request['user_login']);
			$user_data = get_user_by('login', $login);
		}

		if ( empty( $user_data ) )
			return new WP_Error( 'invalid_email' , __( 'Those credentials are not recognized.' ), array( 'status' => 400 ) );

		/**
		 * Fires before errors are returned from a password reset request.
		 *
		 * @since 2.1.0
		 * @since 4.4.0 Added the `$errors` parameter.
		 *
		 * @param WP_Error $errors A WP_Error object containing any errors generated
		 *                         by using invalid credentials.
		 */
		do_action( 'lostpassword_post', $errors );

		if ( $errors->get_error_code() )
			return new WP_REST_Response( $errors , 400 );

		if ( !$user_data ) {
			return new WP_Error( 'invalid_credentials' , __( 'Those credentials are not recognized.' ), array( 'status' => 400 ) );
			//$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or email.'));
			//return new WP_REST_Response( $errors , 400 );
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$key = get_password_reset_key( $user_data );

		if ( is_wp_error( $key ) ) {
			return $key;
		}

		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		/* translators: %s: site name */
		$message .= sprintf( __( 'Site Name: %s'), $site_name ) . "\r\n\r\n";
		/* translators: %s: user login */
		$message .= sprintf( __( 'Username: %s'), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		/* translators: Password reset email subject. %s: Site name */
		$title = sprintf( __( '[%s] Password Reset' ), $site_name );

		/**
		 * Filters the subject of the password reset email.
		 *
		 * @since 2.8.0
		 * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
		 *
		 * @param string  $title      Default email title.
		 * @param string  $user_login The username for the user.
		 * @param WP_User $user_data  WP_User object.
		 */
		$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

		/**
		 * Filters the message body of the password reset mail.
		 *
		 * If the filtered message is empty, the password reset email will not be sent.
		 *
		 * @since 2.8.0
		 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
		 *
		 * @param string  $message    Default mail message.
		 * @param string  $key        The activation key.
		 * @param string  $user_login The username for the user.
		 * @param WP_User $user_data  WP_User object.
		 */
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

		if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
			return new WP_Error( 'email_failed' , __( 'Email not sent.\nPossible reason: your host may have disabled the mail() function.' ), array( 'status' => 400 ) );
			//$errors->add('email_failed', __("Email not sent.\nPossible reason: your host may have disabled the mail() function."));
			//return new WP_REST_Response( $errors , 400 );
		}

		return new WP_REST_Response( true, 200 );
	}

	/**
	 *
	 *
	 * @since
	 *
	 * @param
	 * @return
	 */
	public function delete_item( $request ) {
		$ghost = array_pop( $this->get_users( $request ) );

    	require_once( ABSPATH.'wp-admin/includes/user.php' );
		if ( !wp_delete_user( $request['id'] ) )
			$ghost = false;

		return new WP_REST_Response( $ghost, 200 );
	}

	/**
	 *
	 *
	 * @since
	 *
	 * @param
	 * @return
	 */
	public function create_item( $request ) {
		$required = array( 1,5,13,14,15,16,25,28,36,45,56,59,62,65,75,92,98,111,203,343,346,349,350,351 ); //24,44,55,81,91,  2,3,4,5,6,7,8,9,10,11,12
		$result = array();
//75, 98,
		if ( !empty($request['field_343'] ) ) {
			if ( $request['field_343'] === 'Yes' ) {
				$required = array_diff( $required, array( 346,349,350,351 ) );
			} else if ( $request['field_343'] === 'No' ) {
				$required = array_diff( $required, array( 25 ) );

				if ( strpos($request['field_346'], 'No') !== false )
					$required = array_diff( $required, array( 5,15,16,28,36,45,56,59,62,65,75,92,98,111,203,349,350,351 ) );
			}
		}

		$request['field_1'] = $request['field_13'].' '.$request['field_14'];
		$request['field_5'] = $request['field_203'];

		foreach ( $required as $req ) {
			if ( empty( $request['field_'.$req] ) ) {
				return new WP_Error( 'missing_required_registration_field' , __( 'This is a required field ['.$req.'].' ), array( 'status' => 400 ));
			}
		}

		//VERIFY PASSWORD
		if ( empty( $request['signup_password'] ) )
			return new WP_Error( 'password_empty' , __( 'Please enter a password.' ), array( 'status' => 400 ) );
		else if ( $request['signup_password'] !== $request['signup_password_confirm'] )
			return new WP_Error( 'password_mismatch' , __( 'Passwords do not match.' ), array( 'status' => 400 ) );

		//VERIFY EMAIL
		if ( !filter_var( $request['signup_email'], FILTER_VALIDATE_EMAIL ) )
			return new WP_Error( 'invalid_email' , __( 'That is not a valid email address.' ), array( 'status' => 400 ) );

		if ( email_exists( $request['signup_email'] ) )
			return new WP_Error( 'duplicate_email' , __( 'That email is already in use.' ), array( 'status' => 400 ) );

		if ( count( $result ) == 0 ) {

			//these are not actually required but they need to be added to the list to be saved and we are now passed req validation
			$required = array_merge($required, array( 24,44,55,81,91 ) );

			$base = preg_replace( '/[^A-Za-z\-]/', '', strtolower( trim( $request['field_13'] ).'-'.trim( $request['field_14'] ) ) );
		    $username = $base;
		    $count = 2;

		    while ( username_exists( $username ) ) {
		    	$username = $base.'-'.$count++;
		    }

		    $result = wp_create_user( $username, $request['signup_password'], $request['signup_email'] );
			if ( !is_wp_error( $result) ) {
				foreach ( $required as $req ) {
					if ( is_string( $request['field_'.$req] ) ) {
						$value = addslashes( $request['field_'.$req] );
					} else if ( is_array( $request['field_'.$req] ) ) {
						$value = (array) $request['field_'.$req];
					} else {
						$value = $request['field_'.$req];
					}
					$set_field = xprofile_set_field_data(  $req,  $result,  $value, false );
				}
				add_user_meta( $result, 'app_reg', true, true );
			}
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Gets groups members
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	function get_group_members ( $group_id ) {
		global $members_template;

		$result = array();

		if ( bp_group_has_members( array( 'group_id' => $group_id, 'exclude_admins_mods' => false, 'per_page'=>0 ) ) ) {
			while ( bp_group_members() ) {
				bp_group_the_member();
				$new_member = new stdClass();
				$new_member->id = $members_template->member->user_id;
				$new_member->name = $members_template->member->display_name;
				$new_member->nicename = $members_template->member->user_nicename;
				$new_member->first_name = ( xprofile_get_field_data( 13, $new_member->id  ) ) ?: explode(' ', $new_member->name, 2)[0];
				$new_member->last_name = ( xprofile_get_field_data( 14, $new_member->id  ) ) ?: explode(' ', $new_member->name, 2)[1];
				$new_member->avatar = myfs_core_prepare_avatar_url ( get_avatar_url( $members_template->member->user_id ) );
				$new_member->location = xprofile_get_field_data( 12, $new_member->id );
				$new_member->about = strip_tags( xprofile_get_field_data( 2, $new_member->id ) );
				if ( $members_template->member->last_activity != null )
					$new_member->last_activity = strtotime($members_template->member->last_activity);
				else 
					$new_member->last_activity = strtotime( get_userdata( $new_member->id )->user_registered );
				
				$result[] = $new_member;
			}
		}

		return $result;
	}

	/**
	 * Gets users hurr durr
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	function get_users ( $request ) {
		global $members_template;

		$result = array();
		$args = array(
			'type'=> 'alphabetical',
		);

		if ( isset( $request['id'] ) )
			$args['include'] = array( $request['id'] );

		$members = new BP_User_Query( $args );

		foreach ($members->results as $current) {
			if ($current->last_activity != null) {
				$new_member = new stdClass();
				$new_member->id = $current->ID;
				$new_member->name = $current->display_name;
				$new_member->nicename = $current->user_nicename;
				$new_member->first_name = ( xprofile_get_field_data( 13, $current->ID  ) ) ?: explode(' ', $current->display_name, 2)[0];
				$new_member->last_name = ( xprofile_get_field_data( 14, $current->ID  ) ) ?: explode(' ', $current->display_name, 2)[1];
				$new_member->avatar = myfs_core_prepare_avatar_url( get_avatar_url( $current->ID ) );
				$new_member->location = xprofile_get_field_data( 12, $current->ID );
				$new_member->about = strip_tags( xprofile_get_field_data( 2, $current->ID ) );
				$new_member->last_activity = strtotime( $current->last_activity );
				$result[] = $new_member;
			}
		}

		return $result;
	}

	/**
	 * Gets users hurr durr
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	function get_items ( $request ) {
		if ( !empty( $request['group_id'] ) )
			$result = self::get_group_members( $request['group_id'] );
		else
			$result = self::get_users( $request );

		return new WP_REST_Response( $result, 200 );
	}

	function get_profile_schema( $request ) {
		global $field;
		$result = array();
		$ignore = array( 1,2,3,4,5,6,7,8,9,10,11,12 );

		if ( bp_is_active( 'xprofile' ) ) :

			if ( bp_has_profile( array( 'fetch_field_data' => false ) ) ) :

				while ( bp_profile_groups() ) :

					bp_the_profile_group();

					while ( bp_profile_fields() ) :
						bp_the_profile_field();

						if ( !in_array( $field->id, $ignore ) ) {
							$current = new stdClass();
							$current->id = $field->id;
							$current->type = $field->type;
							$current->name = stripcslashes($field->name);
							$current->description = $field->description;
							$current->is_required = $field->is_required;
							$current->field_order = $field->field_order;
							$current->options = null;

							if ($field->id == 1)
								$current->hidden = true;
							else
								$current->hidden = false;

							$children = $field->get_children();
							if ( count ( $children ) ) {
								$current->options = array();

								foreach ( $children as $child ) {
									$new_option = new stdClass();
									$new_option->id = $child->id;
									$new_option->name = stripcslashes($child->name);
									$new_option->option_order = $child->option_order;

									$current->options[] = $new_option;
								}
							}

							$result[] = $current;
						}
					endwhile;
				endwhile;
			endif;
		endif;

		return new WP_REST_Response( $result, 200 );
	}

	function get_reg_form( $request ) {
		ob_start(); ?>
<div id="buddypress" class="container">
<?php do_action( 'bp_before_register_page' ); ?>
<div class="page" id="register-page">
<h4>Register</h4>
<form action="" name="signup_form" id="signup_form" class="standard-form" method="post" enctype="multipart/form-data" novalidate>
<?php if ( bp_is_active( 'xprofile' ) ) : ?>
<?php do_action( 'bp_before_signup_profile_fields' ); ?>
<div class="register-section" id="profile-details-section">
<?php /* Use the profile field loop to render input fields for the 'base' profile field group */ ?>
<?php 
global $profile_template;
global $group;
if ( bp_is_active( 'xprofile' ) ) : 
if ( bp_has_profile( array( 'fetch_field_data' => false ) ) ) : 
//var_dump($profile_template);
while ( bp_profile_groups() ) : 
bp_the_profile_group(); 
//var_dump($group);
if (in_array( $group->name, array('age', 'guardian' ) ) ) :
echo "<div class='xprofile-group-".$group->name."'>";
while ( bp_profile_fields() ) : 
bp_the_profile_field(); 
//13 & 14 are for everyone, 25 is adults only, 349,350,351 are child only,the rest are consenting only
$field_classes = 'editfield';
switch ( bp_get_the_profile_field_id() ) {
case 343 :
case 13 :
case 14 :
break;
case 346 :
$field_classes .= ' minor';
break;
case 349 :
case 350 :
case 351 :
$field_classes .= ' consent-minor';
break;
case 25 :
$field_classes .= ' adult';
break;
default :
$field_classes .= ' adult-consent-minor';
break;
}
//$field_classes .= ( bp_get_the_profile_field_id() == 25 ) ? 'consent_only adult_only' : ( !in_array( bp_get_the_profile_field_id(), array( 13, 14 ) ) ) ? "consent_only" : "";
?>

<div<?php bp_field_css_class( $field_classes ); ?>>
<?php if ( bp_get_the_profile_field_id() == 346 ) : ?>
<p><br /><img src="/wp-content/uploads/avatars/8/5794f050ceb088689c7fd8c88ed47bb2-bpfull.jpg" alt="bruce" /><br />
Bruce MacFadden<br />
University of Florida<br />
Florida Museum of Natural History<br /><br />
Hello,<br />
My name is Bruce MacFadden and I am a professor at the University of Florida. I represent a small group
of researchers who are building and studying myFOSSIL in order to learn about how people understand
paleontology when they use social technologies, like apps and websites. If you decide to participate, you
will be asked to answer a few questions about your experiences and interests concerning science and
social media. This will take about 5 minutes.<br /><br />
There are no known risks to participation, and most people actually enjoy talking about their
experiences with technology. You do not have to be in this study if you don&#39;t want to and you can quit
the study at any time. Other than our research team, no one will know your answers, including your
teachers or your classmates. If you don&#39;t like a question, you don&#39;t have to answer it and, if you ask, your
answers will not be used in the study.<br /><br />

If you are willing to participate, we must also get an OK from one of your parents or guardians.<br /></p>
<?php endif; ?>

<?php
$field_type = bp_xprofile_create_field_type( bp_get_the_profile_field_type() );
$field_type->edit_field_html();

do_action( 'bp_custom_profile_edit_fields_pre_visibility' );
?>

<?php do_action( 'bp_custom_profile_edit_fields' ); ?>

</div>

<?php endwhile;
echo "</div>"; 
endif;
endwhile;
endif;
endif;
do_action( 'bp_signup_profile_fields' ); ?>
</div><!-- #profile-details-section -->
<?php do_action( 'bp_after_signup_profile_fields' ); ?>
<?php endif; ?>			
<div class="register-section" id="basic-details-section">
<?php /***** Basic Account Details ******/ ?>
<label for="signup_username"><?php _e( 'User Name', 'buddypress' ); ?> <?php _e( '(required)', 'buddypress' ); ?></label>
<?php do_action( 'bp_signup_username_errors' ); ?>
<input type="text" name="signup_username" id="signup_username" value="<?php bp_signup_username_value(); ?>" />
<label for="signup_email"><?php _e( 'Your Email', 'buddypress' ); ?> <?php _e( '(required)', 'buddypress' ); ?></label>
<?php do_action( 'bp_signup_email_errors' ); ?>
<input type="text" name="signup_email" id="signup_email" value="<?php bp_signup_email_value(); ?>" />
<label for="signup_password"><?php _e( 'Choose a Password', 'buddypress' ); ?> <?php _e( '(required)', 'buddypress' ); ?></label>
<?php do_action( 'bp_signup_password_errors' ); ?>
<input type="password" name="signup_password" id="signup_password" value="" class="password-entry" />
<div id="pass-strength-result"></div>
<label for="signup_password_confirm"><?php _e( 'Confirm Password', 'buddypress' ); ?> <?php _e( '(required)', 'buddypress' ); ?></label>
<?php do_action( 'bp_signup_password_confirm_errors' ); ?>
<input type="password" name="signup_password_confirm" id="signup_password_confirm" value="" class="password-entry-confirm" />

<?php do_action( 'bp_account_details_fields' ); ?>

</div><!-- #basic-details-section -->
<?php do_action( 'bp_after_account_details_fields' ); ?>
<?php /***** Extra Profile Details ******/ ?>
<?php if ( bp_is_active( 'xprofile' ) ) : ?>
<?php do_action( 'bp_before_signup_profile_fields' ); ?>
<div class="register-section" id="profile-details-section">
<?php /* Use the profile field loop to render input fields for the 'base' profile field group */ ?>
<?php if ( bp_is_active( 'xprofile' ) ) : if ( bp_has_profile( array( 'fetch_field_data' => false ) ) ) : while ( bp_profile_groups() ) : bp_the_profile_group();
if (!in_array( $group->name, array('age', 'guardian' ) ) ) : ?>
<?php while ( bp_profile_fields() ) : bp_the_profile_field();  
//13 & 14 are for everyone, 25 is adults only, 349,350,351 are child only,the rest are consenting only
$field_classes = 'editfield';
switch ( bp_get_the_profile_field_id() ) {
case 343 :
case 13 :
case 14 :
break;
case 346 :
$field_classes .= ' minor';
break;
case 349 :
case 350 :
case 351 :
$field_classes .= ' consent-minor';
break;
case 25 :
$field_classes .= ' adult';
break;
default :
$field_classes .= ' adult-consent-minor';
break;
}
//$field_classes .= ( bp_get_the_profile_field_id() == 25 ) ? 'consent_only adult_only' : ( !in_array( bp_get_the_profile_field_id(), array( 13, 14 ) ) ) ? "consent_only" : "";
?>
<div<?php bp_field_css_class( $field_classes ); ?>>
<?php if (bp_get_the_profile_field_id() == 25) : ?>
<br />&nbsp;<br />
<label>Description and Informed Consent</label>
<br/>
<textarea readonly rows="10" cols="40" style="width:100%">
Study Description

FOSSIL is creating a national network of fossil clubs and professional paleontologists. The project team is very interested in your experiences, interests, needs, and feedback about the development and effectiveness of FOSSIL in creating a network for amateur and professional paleontologists and providing opportunities for members to learn science, contribute to science, and promote informal science learning. Participant feedback informs the on-going project development and implementation of FOSSIL. 

Procedure: 
If you choose to participate in this study, you are allowing us to use information that is collected during the normal, iterative development activities for FOSSIL. The following types of information may be collected:

Survey: 
A variety of means may be used to document your perspective, experiences, interests, and needs.

Artifacts: 
A variety of artifacts may be collected to document your perspective, experiences, interests, and needs. These may include but are not limited to communication artifacts.

Online archives: 
Comments and responses that are posted on the myFOSSIL website. 

Observations: 
Observation data in the form of field notes, video recordings, or audio recordings may be collected to document your interactions during focus groups, workshops, or the annual FOSSIL meeting. 

Interviews: 
Interviews lasting approximately 20 minutes may be conducted to gain insight into your perspective, experiences, interests, and needs. Interviews will be audio or video recorded and transcribed. Interviews will focus on the following kinds of questions:
Describe your participation in FOSSIL.
What elements of FOSSIL are most effective in creating a network among amateur and professional paleontologists? Please explain.
How do digitized collections impact the practices of the network? 
How does your participation in FOSSIL influence your science learning, contributions to the science of paleontology, and promotion of informal science education? 
What types of components should we include in the myFOSSIL website? Please explain. 
Are the FOSSIL programmatic activities and training effective? Please explain. 
Is the communication among FOSSIL participants effective? Please explain. 
How do the FOSSIL amateur and professional paleontologists share practices and skills?
How does networking the clubs improve the outreach, resources, and capabilities of the individual clubs? 
An anonymous coding scheme will be applied to all information collected and prior to analysis. Data will be analyzed and reported in an aggregated fashion and participants will not be identified by name in any reports of our research.

Risks and Benefits of Participation:
There are risks involved in all research and evaluation studies. However, minimal risk is envisioned for participating in this project. You will not be identified by name in any reports of this research; pseudonyms will be used. There are no direct benefits for participating in the FOSSIL project research and evaluation. However, future participants may experience benefits in the form of a more effective FOSSIL project in providing a network for amateur and professional paleontologists that fosters science learning, contributions to the science of paleontology, and informal science learning. 

Time Required and Compensation:
The study will occur over the course of the FOSSIL project and we anticipate requiring a total of 60 minutes annually of your time to complete the surveys. There will be no compensation for participating in this study.

Confidentiality:
All information gathered in this study will be kept confidential to the extent provided by law. No reference will be made in written or oral materials that could link you to this study. All physical records will be stored in a locked file cabinet in the Principal Investigator's office. Only the researchers will have access to the information we collect online. There is a minimal risk that security of any online data may be breached, but since no identifying information will be collected, and the online host uses several forms of encryption and other protections, it is unlikely that a security breach of the online data will result in any adverse consequence for you. When the study is completed and the data have been analyzed, the information will be shredded and/or electronically erased.

Voluntary Participation:
Your participation is strictly voluntary. Non-participation or denied consent to collect some or all of the data listed above will not affect your participation in FOSSIL. In addition, you may request at any time that your data not to be included.

Contact Information:
If you have any questions or concerns about the study, you may contact Dr. Kent Crippen at kcrippen@coe.ufl.edu or (352) 273-4222 or Dr. Betty Dunckel at bdunckel@flmnh.ufl.edu. For questions regarding your rights as a research participant in this study you may contact the UFIRB Office, Box 112250, University of Florida, Gainesville, FL 32611-2250; ph (352) 392-0433.

Participant Consent:
I have read the above information and agree to participate in this study. I am at least 18 years of age.			

</textarea>

<br/>
<a href="<?php echo site_url('FOSSIL-Project-Description-Informed-Consent.pdf'); ?>" download>Download the Project Description and Informed Consent Document</a>




<?php endif; ?>

<?php
$field_type = bp_xprofile_create_field_type( bp_get_the_profile_field_type() );
$field_type->edit_field_html();

do_action( 'bp_custom_profile_edit_fields_pre_visibility' );
?>

<?php do_action( 'bp_custom_profile_edit_fields' ); ?>

</div>

<?php endwhile; endif; ?>

<?php endwhile; ?>


<input type="hidden" name="signup_profile_field_ids" id="signup_profile_field_ids" value="<?php bp_the_profile_field_ids(); ?>" />
<?php endif; endif; ?>



<?php do_action( 'bp_signup_profile_fields' ); ?>

</div><!-- #profile-details-section -->

<?php do_action( 'bp_after_signup_profile_fields' ); ?>

<?php endif; ?>


<?php do_action( 'bp_before_registration_submit_buttons' ); ?>

<div class="submit">
<br/>
<input type="submit" name="signup_submit" id="signup_submit" value="<?php esc_attr_e( 'Register', 'buddypress' ); ?>" />
</div>

<?php do_action( 'bp_after_registration_submit_buttons' ); ?>

<?php wp_nonce_field( 'bp_new_signup' ); ?>

<br/>




<?php if ( 'completed-confirmation' == bp_get_current_signup_step() ) : ?>
<?php do_action( 'template_notices' ); ?>
<?php do_action( 'bp_before_registration_confirmed' ); ?>

<?php if ( bp_registration_needs_activation() ) : ?>
<p><?php _e( 'You have successfully created your account! To begin using this site you will need to activate your account via the email we have just sent to your address.', 'buddypress' ); ?></p>
<?php else : ?>
<p><?php _e( 'You have successfully created your account! Please log in using the username and password you have just created.', 'buddypress' ); ?></p>
<?php endif; ?>

<?php do_action( 'bp_after_registration_confirmed' ); ?>

<?php endif; // completed-confirmation signup step ?>

<?php do_action( 'bp_custom_signup_steps' ); ?>

</form>
<p>By registering for the myFOSSIL site you are agreeing to our <a href="https://www.myfossil.org/terms-of-service/" target="_BLANK">Terms of Service</a>.</p>

</div>

<?php do_action( 'bp_after_register_page' ); ?>

</div><!-- #buddypress --><?php 
	$html = ob_get_clean();
	return new WP_REST_Response( $html, 200 );
	}
}
