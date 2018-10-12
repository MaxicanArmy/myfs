<?php
/**
 * myFOSSIL Common Functions.
 *
 * @package myFOSSIL
 * @subpackage Functions
 * @since 1.0.0
 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Prepend https:// || http:// to urls
 *
 * @since 1.0.0
 *
 */
function myfs_core_prepend_https( $url ) {
	if ( ! preg_match( '%^https?://%i', $url ) ) {
		if ( preg_match( '%^//%i', $url ) ) {
			$url = preg_replace( '%^//%i', 'https://', $url );
		} else {
			$url = 'https://'.$url;
		}
	}

	return $url;
}

/**
 * Prepend https:// || http:// to urls
 *
 * @since 1.0.0
 *
 */
function myfs_core_prepare_avatar_url( $url ) {
	if ( ! preg_match( '%^https?://%i', $url ) ) {
		if ( preg_match( '%^//%i', $url ) ) {
			$url = preg_replace( '%^//%i', 'https://', $url );
		} else {
			$url = 'https://'.$url;
		}
	}

	return html_entity_decode($url);
}

function myfs_send_push_notifications( $args = array() ) {
	$badge_num = get_user_meta( $args['recipient'], 'badge_num', true);
	$badge_num = empty( $badge_num ) ? '1' : ++$badge_num;

	$result = array();
	#API access key from Google API's Console
	//define( 'API_ACCESS_KEY', 'AAAAD-RS3PU:APA91bF0U8jrvy-asQd6bsJ1yXLDvLedWfxaS2BGXiCeeAD_A18FWSdPoKGCrxOZ1NM_1CfHn-2nLXrQom3PG2HCEdewmRYiZrfmGgDQewAk9cbrcUVITJZfnLpBJK7buOz0lT5t_To3' );
	$notification = array(
		'body' => $args['body'],
		'title' => $args['title'],
		'badge' => $badge_num,
	);

	$tokens = get_user_meta( $args['recipient'], 'fcm_token' );

	if ( is_array( $tokens ) && !empty( $tokens ) ) {
		foreach ($tokens as $token) {

			$data = array(
				'to' => $token,
				'notification' => $notification,
			);

			$json_data = json_encode($data);
			//FCM API end-point
			$url = 'https://fcm.googleapis.com/fcm/send';
			//api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
			$server_key = 'AAAAD-RS3PU:APA91bF0U8jrvy-asQd6bsJ1yXLDvLedWfxaS2BGXiCeeAD_A18FWSdPoKGCrxOZ1NM_1CfHn-2nLXrQom3PG2HCEdewmRYiZrfmGgDQewAk9cbrcUVITJZfnLpBJK7buOz0lT5t_To3';
			//header with content_type api key
			$headers = array(
			    'Content-Type:application/json',
			    'Authorization:key='.$server_key
			);
			//CURL request to route notification to FCM connection server (provided by Google)
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
			$r = curl_exec($ch);

			$r = json_decode(str_replace('\"', '"', $r));

			if ( $r->failure == 1 && $r->results[0]->error == 'InvalidRegistration' ) {
				delete_user_meta( $args['recipient'], 'fcm_token', $token );
			} else {
				$result[] = $r;
			}

			curl_close($ch);
		}			
	}
	$update_badge = update_user_meta( $args['recipient'], 'badge_num', $badge_num );

	return count( $result );
}