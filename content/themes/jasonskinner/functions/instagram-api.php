<?php
function jss_instagram_feed( $count = 4 ) {
	//set transient slug
	$slug = "instagram_feed_self";

	//instagram access token
	$token = '3017494.d3489de.cc7de89f9cca48fcb954f50764143007';

	//get self use id
	$ig_user_id = 'self';

	//setup wp_remote_get
	$remote_wp = wp_remote_get( "https://api.instagram.com/v1/users/" . $ig_user_id . "/media/recent/?count=$count&access_token=" . $token );

	//if it fails return false
	if ( is_wp_error( $remote_wp ) ) {
		return false;
	}

	//get back response
	$instagram_response = json_decode( $remote_wp['body'] );

	set_transient($slug, $instagram_response, HOUR_IN_SECONDS);

	return $instagram_response;
}