<?php
$spotifyid               = 'b44ea42e462c4d9688be7e5558f17950';
$spotifysecret           = '53016f8e1a484f1789b0dd13a70999dc';
$spotifyauthorizeurl = 'https://accounts.spotify.com/authorize';
$spotifycurrentlyplaying = 'https://api.spotify.com/v1/jasonskinner/player/currently-playing';
$spotifybearer = 'BQBA7F5VH5-ya6dVPsvzDgLhm-odLtX6j_CMlDnS4FFq5AA0PYYkZSCNKCRn9f5L0ZDVL0JA0po5VkPbrSHugIwqPaCthZRjwpJD7yzaps0ycbT69WM-zRhNEwiOzNMmJ34nmxG_6xhyrXZ1KJbjwuoSzw';
$spotifymethod = 'GET';

$authorize_request = wp_remote_get($spotifyauthorizeurl . '?client_id=' . $spotifyid . '&response_type=code&scope=user-read-currently-playing');
$spotifyaccesstoken = $authorize_request;



var_dump( $authorize_request );


?>
<hr>
<?php

//$spotifyheaders = array(
//		'Authorization' => 'Bearer ' . $spotifybearer
//);
//
//$spotifyrequest = array(
//		'headers' => $spotifyheaders,
//		'method' => $method
//);
//
////if ( $method == 'GET' && ! empty( $args ) && is_array( $args ) ) {
////	$spotifycurrentlyplaying = add_query_arg( $args, $url );
////} else {
////	$request['body'] = json_encode( $args );
////}
//
//$response = wp_remote_request( $spotifycurrentlyplaying, $spotifyrequest );
//
//var_dump( $response );

//var_dump( $response );

//$spotifyresponse         = wp_remote_get( $spotifycurrentlyplaying, array(
//	'headers' => array(
//		'client_id'     => $spotifyid,
//		'client_secret' => $spotifysecret,
//		'bearer' => 'BQBA7F5VH5-ya6dVPsvzDgLhm-odLtX6j_CMlDnS4FFq5AA0PYYkZSCNKCRn9f5L0ZDVL0JA0po5VkPbrSHugIwqPaCthZRjwpJD7yzaps0ycbT69WM-zRhNEwiOzNMmJ34nmxG_6xhyrXZ1KJbjwuoSzw'
//	)
//)
//);



//check status

//decode json





