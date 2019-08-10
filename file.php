<?php
include('config.php');

// File Delivery setting is pulled from config for normal music playback, 
// but clicking the Download link will always override to 'download' 
if ( !empty($_GET['delivery']) )
{
	switch ( $_GET['delivery'] )
	{
		case 'download':
			$delivery = 'download';
			break;
		case 'stream':
			$delivery = 'stream';
			break;
		// If not one of the two actual delivery methods, then revert back to config file setting
		default:
			break;
	}
}

$file = rawurldecode($_GET['file']);
$ext = strtolower(substr($file,-3));
$lastslash = strrpos($file,'/');
$filename = substr($file,$lastslash+1,strlen($file)-$lastslash);

// Only serve up files that exist and are in the list of supported types
if (file_exists($file) && in_array($ext, $types))
{
	// Handle the supported file types
	switch ($ext)
	{
		case 'aac':
			header('Content-Type: audio/x-aac');
			break;
		case 'm4a':
		case 'f4a':	
			header('Content-Type: audio/m4a');
			break;
		case 'mp3':
			header('Content-Type: audio/mpeg');
			break;
		case 'ogg':
		case 'oga':
			header('Content-Type: audio/ogg');
			break;
		// If the file type isn't in the list of supported types, then kill it
		default:
			exit;
	}
	// Use appropriate file delivery method
	if ( $delivery == 'download' )
	{
		header('Content-length: ' . filesize($file));
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		readfile($file);
	}
	elseif ( $delivery == 'stream' )
	{
		$strContext = stream_context_create(
			array(
				'http'=>array(
					'method'=>'GET',
					'header'=>'Accept-language: en\r\n'
				)
			)
		);
		header('Content-Transfer-Encoding: binary');
		header('Pragma: no-cache');
		header('icy-br: 128');
		$fpOrigin = fopen($file, 'rb', false, $strContext);
		while ( !feof($fpOrigin))
		{
			$buffer = fread($fpOrigin,4096);
			echo $buffer;
			flush();
		}
		fclose($fpOrigin);
	}
	else
	{
		// Don't do anything if unexpected delivery method
		exit;
	}
}
?>