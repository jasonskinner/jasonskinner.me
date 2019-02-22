<?php
$spotifyid               = 'b44ea42e462c4d9688be7e5558f17950';
$spotifysecret           = '53016f8e1a484f1789b0dd13a70999dc';
$spotifycurrentlyplaying = 'https://api.spotify.com/v1/jasonskinner/player/currently-playing';
$spotifybearer = 'BQBA7F5VH5-ya6dVPsvzDgLhm-odLtX6j_CMlDnS4FFq5AA0PYYkZSCNKCRn9f5L0ZDVL0JA0po5VkPbrSHugIwqPaCthZRjwpJD7yzaps0ycbT69WM-zRhNEwiOzNMmJ34nmxG_6xhyrXZ1KJbjwuoSzw';


$args = array(
	'client_id' => $spotifyid,
	'client_secret' => $spotifysecret,
);
$headers = array(
	'Authorization' => 'Bearer ' . $spotifybearer,
);

$request = array(
	'headers' => $headers,

);


$response = wp_remote_request( $spotifycurrentlyplaying, $request );

var_dump( $response );

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





