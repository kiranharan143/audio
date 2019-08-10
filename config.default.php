<?php
// Debugging: Enable to allow "&debug=log" to be passed in the URL, which creates verbose debug.log file
// Example: $debugging = true;
$debugging = false;

// The relative root where music resides, trailing slash will be removed
// Example: $root = '../../../backup/music'; (below web root)
// Example: $root = 'assets/music'; (above web root)
$root = '';

// URL of this Music Library & Player script with port number, if necessary
// Only required for m3u playlists. Trailing slash will be removed
// Example: $url = 'https://thejamesmachine.com';
// Example: $url = 'https://mynas.dyndns.org:8080/music';
$url = '';

// Group initial directory list by first letter (#, A, B, C...)
// Example: $group = false;
$group = false;

// Add download link along side play links
// Example: $download = true;
$download = false;

// Add M3U stream link along side play links
// Warning: If your web directory is password protected, most devices will not play an m3u
// Example: $m3u = false;
$m3u = false;

// Supported audio file types - array of file extensions
// Comment out any that you don't want to include. Must be lower case!
$types[] = 'aac';
$types[] = 'm4a';
$types[] = 'f4a';
$types[] = 'mp3';
$types[] = 'ogg';
$types[] = 'oga';

// File delivery: Download or Stream
// Download is simple and JWPlayer shows progress bar and scrub/shuttle knob
// Stream is more compatible with Android and iOS, but JWPlayer shows "Live Stream" and no progress bar
// Example: $delivery = 'download';
$delivery = 'stream';

// Player repeat, requires the trailing comma
// Example: $repeat = 'repeat: true,';
$repeat = 'repeat: false,';

// Number of tracks to include in random playlist
// If there are fewer tracks available, this amount will be adjusted automatically.
$rand_count = 20;

// JWPlayer version
// Version 6 can be used without a license key
// Version 7 requires you sign up for a free account to get a license key
// at https://www.jwplayer.com/sign-up/ and choose self-hosted option
// Example: $jwversion = 6;
// Example: $jwversion = 7;
$jwversion = 6;

// JWPlayer version specific settings
if ( $jwversion == 6 )
{
	// Player default, requires the trailing comma
	// Don't worry, jwplayer automatically reverts if primary is not possible
	// Example: $player = 'primary: "flash",';
	$player = 'primary: "flash",';

	// Player skin, requires the trailing comma if using a skin
	// Leave empty to use default, small player
	// mlp is a "custom" skin that doubles the size of the control bar for small screens
	// Example: $skin = 'skin: "jwplayer6/mlp/mlp.xml",';
	$skin = 'skin: "jwplayer6/mlp/mlp.xml",';

	// Player key
	// Leave empty in Version 6
	$jwplayer_key = '';
}
elseif ( $jwversion == 7 )
{
	// Player default
	// Leave empty in Version 7
	$player = '';

	// Player skin, requires the trailing comma if using a skin
	// Leave empty to use default, small player
	// mlp is a "custom" skin that doubles the size of the control bar for small screens
	$skin = 'skin: {name: "MLP"},';

	// Player key
	// $jwplayer_key = 'P0FgzfAFbLEuegWaWLPdlec+1JHYXAiuoDcqaQ==';
	$jwplayer_key = '';
}
?>