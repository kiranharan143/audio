<?php
include('config.php');
include('functions.php');
$dir = $_GET['dir'];
// Process the url
if ( !empty($url) )
{
	$url = getDirectory($url);
}
// Kill it if there is nothing to do
if ( !empty($dir) &&  !empty($url) )
{
	$trackList = getFiles($dir);
	if ( count($trackList)>0 )
	{
		header('Content-Type: audio/mpegurl');
		header('Content-Disposition: attachment; filename=playlist.m3u');
		foreach ( $trackList as $track )
		{
			// Append the link to the playlist
			echo $url."/file.php?file=".rawurlencode($track)."\r\n";
		}
	}
}
else
{
	exit;
}
?>