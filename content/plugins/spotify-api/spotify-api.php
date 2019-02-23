<?php

/**
 * Plugin Name: Spotify API
 * Description: Load Spotify API (https://github.com/jwilsson/spotify-web-api-php) via composer
 */

// Maybe we have already pulled our library?
if ( class_exists( 'Plugin_SpotifyAPI' ) ) {
	return;
}

class Plugin_SpotifyAPI {

	public function load() {
		// This will be used as a check if we have already loaded the plugin.
		if ( defined( 'Plugin_SpotifyAPI_Loaded' ) ) { return; }
		define( 'Plugin_SpotifyAPI_Loaded', true );

	}


}

// Initialize the plugin if not already loaded.
add_action( 'init', function(){
	if ( ! defined( 'Plugin_SpotifyAPI_Loaded' ) ) {
		$plugin = new Plugin_SpotifyAPI();
		$plugin->load();
	}
});