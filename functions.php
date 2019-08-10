<?php
// Global vars keep the same function from being called multiple times
$globalGetDirectory = array();
$globalGetFiles = array();
$globalGetFolderList = array();
$globalGetLastSlash = array();
$globalGetTracksCount = array();

// Compare root path to web root
function getWebRoot($dir)
{
	global $debug;
	$root = realpath($dir);
	$docroot = realpath($_SERVER['DOCUMENT_ROOT']);
	$test = strpos($root,$docroot);
	if ( $test === false )
	{
		// The files are not web accessible, use file.php
		$belowRoot = true;
	}
	else
	{
		// The files are web accessible, link directly
		$belowRoot = false;
	}
	if ( !empty($debug) )
	{
		$log = "getWebRoot(".$dir.")\n----------\n";
		$log .= "root = ".$root."\n";
		$log .= "docroot = ".$docroot."\n";
		if ( $belowRoot==true )
		{
			$log .= "belowRoot = true\n\n";
		}
		else
		{
			$log .= "belowRoot = false\n\n";
		}
		debugLog($log);
	}
	return $belowRoot;
}

// Find the last slash in the directory path
function getLastSlash($dir)
{
	global $debug, $globalGetLastSlash;
	if ( !isset($globalGetLastSlash[$dir]) )
	{
		$lastslash = strrpos($dir, '/');
		if ( !empty($debug) )
		{
			$log =  "getLastSlash(".$dir.")\n----------\n";
			$log .= "lastslash = ".$lastslash."\n\n";
			debugLog($log);
		}
		$globalGetLastSlash[$dir] = $lastslash;
	}
	else
	{
		$lastslash = $globalGetLastSlash[$dir];
	}
	return $lastslash;
}

// Escape square brackets from paths
function cleanPath($dir)
{
	$dir = str_replace('[', '\[', $dir);
	$dir = str_replace(']', '\]', $dir);
	$dir = str_replace('\[', '[[]', $dir);
	$dir = str_replace('\]', '[]]', $dir);
	$dir = str_replace('&', '\&', $dir);
	return $dir;
}

// Get the list of files in this directory
function getFiles($dir)
{
	global $types, $debug, $globalGetFiles;
	if ( !isset($globalGetFiles[$dir]) )
	{
		$dir = cleanPath($dir);
		$files = array();
		$list = array();
		
		// Loop through each of the files in this directory
		$files = glob($dir.'/*');
		foreach ( $files as $file )
		{
			if ( !empty($debug) && empty($log) )
			{
				$log = "getFiles(".$dir.")\n----------\n";
			}
			// Check each file against the list of types in config.php
			foreach ( $types as $type )
			{
				// Lower case the file extension to be sure
				if ( substr(strtolower($file),-3) == $type )
				{
					$list[] = $file;
					if ( !empty($debug) )
					{
						$log .= "list[] = ".$file."\n";
					}
				}
			}
		}
		unset($files);
		// Alphabetize files in case insensitive order
		natcasesort($list);
		if ( !empty($debug) )
		{
			$log .= "\n";
			debugLog($log);
		}
		$globalGetFiles[$dir] = $list;
	}
	else
	{
		$list = $globalGetFiles[$dir];
	}
	return $list;
}

// Get the list of directories
function getFolderList($dir)
{
	global $debug, $globalGetFolderList;
	if ( !isset($globalGetFolderList[$dir]) )
	{
		$directories = array();
		$directories = glob($dir.'/*', GLOB_ONLYDIR);
		
		// Alphabetize directories
		natcasesort($directories);
		if ( !empty($debug) )
		{
			$log = "getFolderList(".$dir.")\n----------\n";
			$log .= "count(directories) = ".count($directories)."\n\n";
			debugLog($log);
		}
		$globalGetFolderList[$dir] = $directories;
	}
	else
	{
		$directories = $globalGetFolderList[$dir];
	}
	return $directories;
}

// Get and treat the current player directory
function getDirectory($dir)
{
	global $debug, $globalGetDirectory;
	if ( !isset($globalGetDirectory[$dir]) )
	{
		if ( !empty($debug) )
		{
			$log = "getDirectory(".$dir.")\n----------\n";
		}
		// Check for and remove trailing slash
		$lastslash = getLastSlash($dir);	
		if ( $lastslash == strlen($dir)-1 )
		{
			$newdir = substr($dir,0,strlen($dir)-1);
		}
		else
		{
			$newdir = $dir;
		}
		if ( !empty($debug) )
		{
			$log .= "dir = ".$newdir."\n\n";
			debugLog($log);
		}
		$globalGetDirectory[$dir] = $newdir;
	}
	else
	{
		$newdir = $globalGetDirectory[$dir];
	}
	return $newdir;
}

// Get a random playlist link
function getRandomPlaylistLink($dir)
{
	global $debug, $root;
	if ( !empty($debug) )
	{
		$log = "getRandomPlaylistLink(".$dir.")\n----------\n";
	}
	if ( $dir == $root || getFoldersCount($dir)>0 || getTracksCount($dir)>0 )
	{
		$path = '?dir='.$dir.'&playmode=random';
		if ( !empty($debug) )
		{
			$path .= '&debug=log';
		}
		$linkText = '<a href="'.$path.'" title="Random Playlist"><span class="glyphicon glyphicon-random pull-right breadcrumb"></span></a>';
		if ( !empty($debug) )
		{
			$log .= "dir is root or has more than one folder, add link";
		}
		return $linkText;
	}
	else
	{
		if ( !empty($debug) )
		{
			$log .= "dir is not root or has one or less folders, do not add link";
		}
		return '';
	}
}

// Build the breadcrumbs trail, found under the player
function getBreadcrumbs($dir)
{
	global $root, $bcI, $m3u, $debug, $playMode;
	// Find last slash in root dir
	$lastslash = getLastSlash(getDirectory($root));
	// Treat that the as the home directory
	$start = substr($dir,$lastslash);
	// Get the list of subdirectories in the home directory
	$directories = explode('/',$start);
	// Count the list of subdirectories
	$count = count($directories);
	$breadcrumbs = '<ol class="breadcrumb">';
	// Global $bcI is called later in displayDirectories() to determine depth of directories
	$bcI = 0;
	$path = '';
	foreach ( $directories as $directory )
	{
		// Only count actual directories
		if ( !empty($directory) )
		{
			$path = '?dir='.rawurlencode(substr($dir,0,strpos($dir,$directory)+strlen($directory)));
			if ( !empty($debug) )
			{
				$path .= '&debug=log';
			}
		}
		// Make breadcrumb links for all directories
		$breadcrumbs .= '<li><a href="'.$path.'" title="Return to this directory">'.$directory.'</a>';
		// Add a play all tracks link if this is the current directory
		if ( $bcI == $count-1 && $playMode!='random' )
		{
			// Add a play all tracks button if there are playable tracks in this directory
			$count = getTracksCount($dir);
			if ( $count > 0 )
			{
				$breadcrumbs .= '&nbsp;&nbsp;/&nbsp;&nbsp;<a href="?dir='.rawurlencode($dir).'&playmode=folder';
				if ( !empty($debug) )
				{
					$breadcrumbs .= '&debug=log';
				}
				$breadcrumbs .= '" title="Play all tracks in this folder"><span class="glyphicon glyphicon-play"></span></a>';
				// If m3u streaming is enabled, add a stream all tracks button as well
				if ( $m3u==true )
				{
					$breadcrumbs .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="playlist.php?dir='.rawurlencode($dir).'" title="Stream all tracks in this folder (m3u)" target="_blank"><span class="glyphicon glyphicon-list"></span></a>';
				}
			}
		}
		$breadcrumbs .= '</li>';
		$bcI++;
	}
	if ( $playMode == 'random' )
	{
		$breadcrumbs .= '<li>Random Playlist</li>';
	}
	$breadcrumbs .= '</ol>';
	if ( !empty($debug) )
	{
		$log = "getBreadcrumbs(".$dir.")\n----------\n";
		$log .= "breadcrumbs = ".$breadcrumbs."\n\n";
		debugLog($log);
	}
	return $breadcrumbs;
}

// Get the straight list of subdirectories in the current directory for the main list
function getDirectories($dir)
{
	global $debug;
	$directories = getFolderList($dir);
	// Only bother with it if there are any subdirectories here
	if (count($directories)>0)
	{
		$getDirectories = '<div class="list-group">';
		foreach($directories as $directory)
		{
			// Only bother with it if there are playable tracks or subdirectories
			if ( getTracksCount($directory)>0 || getFoldersCount($directory)>0 )
			{
				$getDirectories .= '<a href="?dir='.rawurlencode($directory);
				if ( !empty($debug) )
				{
					$getDirectories .= '&debug=log';
				}
				$getDirectories .= '" class="list-group-item" title="Open this directory">'.substr($directory,strrpos($directory,"/")+1).'</a>';
			}
		}
		unset($directories);
		$getDirectories .= '</div>';
	}
	else
	{
		$getDirectories = '';
	}
	if ( !empty($debug) )
	{
		$log = "getDirectories(".$dir.")\n----------\n";
		$log .= "getDirectories = ".$getDirectories."\n\n";
		debugLog($log);
	}
	return $getDirectories;
}

// Get the grouped list of subdirectories in the current directory for the main list
function getGroupedDirectories($dir)
{
	global $debug;
	$directories = getFolderList($dir);
	// Only bother with it if there are any subdirectories here
	if (count($directories)>0)
	{
		$lastLetter = "";
		$thisLetter = "";
		$getDirectories = '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
		foreach($directories as $directory)
		{
			// Only bother with it if there are playable tracks or subdirectories
			if ( getTracksCount($directory)>0 || getFoldersCount($directory)>0 )
			{
				// Get the first character of the directory name for grouping
				$thisLetter = strtoupper(substr($directory,strrpos($directory,"/")+1,1));
				$thisLetterID = $thisLetter;
				// Group all non-alpha characters into #
				if ( preg_match('/^[A-Za-z]/',$thisLetter)==0 )
				{
					$thisLetter = "#";
					$thisLetterID = "XX";
				}
				// Determine if a new group should start
				if ( $thisLetter != $lastLetter )
				{
					// Only close the prior group when there is one to close
					if ( !empty($lastLetter) )
					{
						$getDirectories .= '</div></div></div>';
					}
						// Start a new group
						$getDirectories .= '
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="heading'.$thisLetterID.'">
									<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$thisLetterID.'" aria-expanded="true" aria-controls="collapse'.$thisLetterID.'">
										<h4 class="panel-title">';
										$getDirectories .= strtoupper($thisLetter);
										$getDirectories .= '
										</h4>
									</a>
								</div>
								<div id="collapse'.$thisLetterID.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading'.$thisLetterID.'">
									<div class="list-group">';
				}
				$getDirectories .= '<a href="?dir='.rawurlencode($directory);
				if ( !empty($debug) )
				{
					$getDirectories .= '&debug=log';
				}
				$getDirectories .= '" class="list-group-item" title="Open this directory">'.substr($directory,strrpos($directory,"/")+1).'</a>';
				$lastLetter = $thisLetter;
			}
		}
		unset($directories);
		$getDirectories .= '</div></div></div>';
	}
	else
	{
		$getDirectories = '';
	}
	if ( !empty($debug) )
	{
		$log = "getGroupedDirectories(".$dir.")\n----------\n";
		$log .= "getDirectories = ".$getDirectories."\n\n";
		debugLog($log);
	}
	return $getDirectories;
}

// Decide whether to use straight directory list or grouped
function displayDirectories($dir)
{
	// $bcI is the iterator from getBreadcrumbs()
	global $group, $bcI, $debug;
	if ( !empty($debug) )
	{
		$log = "displayDirectories(".$dir.")\n----------\n";
	}
	if ( $group==true && $bcI==2 )
	{
		$getDirectories = getGroupedDirectories($dir);
		if ( !empty($debug) )
		{
			$log .= "getDirectories = getGroupedDirectories(".$dir.")\n\n";
		}
	}
	else
	{
		$getDirectories = getDirectories($dir);
		if ( !empty($debug) )
		{
			$log .= "getDirectories = getDirectories(".$dir.")\n\n";
		}
	}
	if ( !empty($debug) )
	{
		debugLog($log);
	}
	return $getDirectories;
}

// Get the number of tracks in the selected directory
function getTracksCount($dir)
{
	global $debug, $globalGetTracksCount;
	if ( !isset($globalGetTracksCount[$dir]) )
	{
		$count = count(getFiles($dir));
		if ( !empty($debug) )
		{
			$log = "getTracksCount(".$dir.")\n----------\n";
			$log .= "count = ".$count."\n\n";
			debugLog($log);
		}
		$globalGetTracksCount[$dir] = $count;
	}
	else
	{
		$count = $globalGetTracksCount[$dir];
	}
	return $count;
}

// Get the number of subdirectories in the selected directory
function getFoldersCount($dir)
{
	global $debug;
	$count = count(getFolderList($dir));
	if ( !empty($debug) )
	{
		$log = "getFoldersCount(".$dir.")\n----------\n";
		$log .= "count = ".$count."\n\n";
		debugLog($log);
	}
	return $count;
}

// Format track link
function getTrackLink($dir,$track)
{
	global $stream, $debug;
	if ( $stream )
	{
		$trackLink = 'file.php?file='.rawurlencode($dir.'/'.$track);
	}
	else
	{
		$trackLink = $dir.'/'.$track;
	}
	if ( !empty($debug) )
	{
		$log = "getTrackLink(".$dir.",".$track.")\n----------\n";
		$log .= "trackLink = ".$trackLink."\n\n";
		debugLog($log);
	}
	return $trackLink;
}

// Get the list of tracks in the current directory with playlist links
function getTracksPlaylist($dir)
{
	global $download;
	$i = 0;
	$files = getFiles($dir);
	$getTracks = formatHtmlPlaylist($dir,$files);
	return $getTracks;
}

// Get a formatted playlist for a single track
function getTrackPlaylist($dir,$track)
{
	global $debug;
	// Deliver a single track to the player
	$trackLink = getTrackLink($dir,$track);
	$playlist = 'playlist: [{file:"'.$trackLink.'",type:';
	// Add the file type
	$playlist .= '"'.strtolower(substr($track,-3)).'"';
	$playlist .= '}]';
	if ( !empty($debug) )
	{
		$log = "getTrackPlaylist(".$dir.",".$track.")\n----------\n";
		$log .= "playlist = ".$playlist."\n\n";
		debugLog($log);
	}
	return $playlist;
}

// Get a formatted playlist for a list of tracks
function getFolderPlaylist($dir)
{
	$files = getFiles($dir);
	$playlist = formatJsPlaylist($files);
	return $playlist;
}

// Generate a random playlist of tracks
function getRandomPlaylist($rdir)
{
	global $debug, $download, $rand_count;
	if ( !empty($debug) )
	{
		$log = "getRandomPlaylist(".$rdir.")\n----------\n";
	}
	$tracks = getAllTracks($rdir);
	$playlist = formatJsPlaylist($tracks);
	$getTracks = formatHtmlPlaylist($rdir,$tracks);
	$return = array('playlist'=>$playlist,'tracks'=>$getTracks);
	if ( !empty($debug) )
	{
		debugLog($log);
	}
	return $return;
}

// Get all tracks from the parent directory and subdirectories
function getAllTracks($dir)
{
	global $debug, $rand_count;
	// Load all folders into an array
	$folders = getFolderList($dir);
	// Add current directory as well
	$folders[] = $dir;
	// Loop through all folders
	if ( !empty($debug) )
	{
		$log = "getAllTracks(".$dir.")\n----------\n";
	}
	if ( is_array($folders) )
	{
		shuffle($folders);
	}
	// there are more folders than $rand_count, pick $rand_count random folders
	if ( count($folders)>$rand_count )
	{
		if ( !empty($debug) )
		{
			$log .= "There are ".count($folders)." folders, which is more than ".$rand_count."\n";
		}
		$i = 0;
		while ( $i < $rand_count )
		{
			$directories[] = $folders[$i];
			if ( !empty($debug) )
			{
				$log .= "Added folder ".$folders[$i]."\n";
			}
			$i++;
		}
		unset($i);
	}
	elseif ( count($folders)>0 ) // there are fewer folders than $rand_count, then pick all the root folders
	{
		if ( !empty($debug) )
		{
			$log .= "There are ".count($folders)." folders, which is fewer than ".$rand_count."\n";
		}
		foreach ( $folders as $folder ) // get all subdirectories from the folders
		{
			// only look for subdirectories if not the playlist root directory
			if ( $folder != $dir )
			{
				$directories[] = $folder;
				$newfolders = getFolderList($folder);
				if ( is_array($newfolders) )
				{
					foreach ( $newfolders as $newfolder )
					{
						$directories[] = $newfolder;
						if (!empty($debug))
						{
							$log .= "Adding subdirectory ".$newfolder."\n";
						}
					}
					unset($newfolders);
				}
			}
			else
			{
				$directories[] = $dir;
			}
		}
		unset($folders);
	}
	$count_directories = count($directories);
	if ( is_array($directories) && $count_directories>0)
	{
		shuffle($directories);
	}
	if ( $count_directories >= $rand_count )
	{
		// loop through the first rand_count shuffled directories
		$i = 0;
		while ( $i < $rand_count )
		{
			$foundtracks = array();
			$tracks_here = getFiles($directories[$i]);
			// there are tracks here
			if ( is_array($tracks_here) && count($tracks_here)>0 )
			{
				if ( !empty($debug) )
				{
					$log .= "There are ".count($tracks_here)." tracks in folder ".$directories[$i]."\n";
				}
				foreach ( $tracks_here as $track )
				{
					$foundtracks[] = $track;
				}
				unset($tracks_here);
			}
			else // look in subdirectories
			{
				$dirs = getFolderList($directories[$i]);
				if ( is_array($dirs) )
				{
					foreach ( $dirs as $fldr )
					{
						// don't look for tracks in subdirectories if this is the playlist root directory
						if ( $fldr != $dir )
						{
							$files = getFiles($fldr);
							if ( is_array($files) && count($files)>0 )
							{
								foreach ( $files as $file )
								{
									$foundtracks[] = $file;
								}
								unset($files);
							}
						}
					}
					unset($dirs);
				}
				if ( !empty($debug) )
				{
					$log .= "There are no tracks in folder ".$directories[$i].". Found ".count($foundtracks)." in subdirectories.\n";
				}
			}
			// shuffle found tracks and then pick the first one
			if ( is_array($foundtracks) && count($foundtracks)>0 )
			{
				shuffle($foundtracks);
				$tracks[] = $foundtracks[0];
				$i++;
				if ( !empty($debug) )
				{
					$log .= "Added track ".$foundtracks[0]."\n";
				}
				unset($foundtracks);
			}
			else
			{
				if ( !empty($debug) )
				{
					$log .= "There were no tracks in ".$directories[$i].", so moving on.\n";
				}
			}
		}
		unset($directories);
	}
	elseif ( is_array($directories) && ($count_directories-1) > 0 )
	{
		if ( !empty($debug) )
		{
			$log .= "There are ".$count_directories." directories here, which is fewer than ".$rand_count.".\n";
		}
		$foundtracks = array();
		foreach ( $directories as $directory )
		{
			$tracks_here = getFiles($directory);
			$count_tracks = count($tracks_here);
			// there are tracks here
			if ( is_array($tracks_here) && $count_tracks>0 )
			{
				if ( !empty($debug) )
				{
					$log .= "There are ".$count_tracks." tracks in folder ".$directory."\n";
				}
				foreach ( $tracks_here as $track )
				{
					$foundtracks[] = $track;
				}
				unset($count_tracks);
				unset($tracks_here);
			}
		}
		unset($directories);
		// shuffle found tracks and then pick the first one
		if ( is_array($foundtracks) && count($foundtracks)>0 )
		{
			shuffle($foundtracks);
			$i = 0;
			while ( $i < $rand_count )
			{
				$tracks[] = $foundtracks[$i];
				if ( !empty($debug) )
				{
					$log .= "Added track ".$foundtracks[$i]."\n";
				}
				$i++;
			}
			unset($i);
			unset($foundtracks);
		}
		else
		{
			if ( !empty($debug) )
			{
				$log .= "There were no tracks in ".$directory.", so moving on.\n";
			}
		}
		unset($directory);
	}
	else
	{
		// there are no directories here, get all tracks
		$all_tracks = getFiles($dir);
		$count_tracks = count($all_tracks);
		if ( is_array($all_tracks) && $count_tracks>0 )
		{
			shuffle($all_tracks);
			if ( !empty($debug) )
			{
				$log .= "There are ".$count_tracks." tracks in this directory.\n";
			}
			if ( $count_tracks < $rand_count )
			{
				$rand_count = $count_tracks;
			}
			$i = 0;
			while ( $i < $rand_count )
			{
				$tracks[] = $all_tracks[$i];
				$i++;
			}
			unset($i);
			unset($all_tracks);
			unset($count_tracks);
		}
		else
		{
			if ( !empty($debug) )
			{
				$log .= "There are no tracks in this directory.\n";
			}
		}
	}
	if ( !empty($debug) )
	{
		debugLog($log."\n");
	}
	return $tracks;
}

// Format the javascript playlist
function formatJsPlaylist($files)
{
	$playlist = 'playlist: [';
	if ( is_array($files) && count($files)>0 )
	{
		foreach ($files as $file)
		{
			// Strip out the path, leaving only the file name
			$track = substr($file,strrpos($file,'/')+1);
			$dir = substr($file,0,strrpos($file,'/'));
			$trackLink = getTrackLink($dir,$track);
			$playlist .= '{file:"'.$trackLink.'",type:';
			$playlist .= '"'.strtolower(substr($track,-3)).'"},';
		}
		unset($files);
	}
	// Strip the last comma off the playlist
	$playlist = substr($playlist,0,strlen($playlist)-1);
	$playlist .= ']';
	if ( !empty($debug) )
	{
		$log = "getFolderPlaylist(".$dir.")\n----------\n";
		$log .= "playlist = ".$playlist."\n\n";
		debugLog($log);
	}
	return $playlist;
}

// Centralized generate playlist function
function formatHtmlPlaylist($dir,$files)
{
	global $download, $m3u, $debug, $playMode, $root;
	// Track number
	$i = 0;
	// Loop through each file in the playlist
	if ( is_array($files) && count($files)>0 )
	{
		// Playlist is formatted differently when download links are enabled
		if ( $download )
		{
			$getTracks = '<ul class="list-group">';
		}
		else
		{
			$getTracks = '<div class="list-group">';
		}
		foreach ($files as $file)
		{
			// Strip out the path, leaving only the file name
			$track = substr($file,strrpos($file,'/')+1);
			
			// Add class and ID to <a> link instead of the <li>
			$class = '';
			if ( !$download )
			{
				$class = 'class="list-group-item" id="'.$i.'"';
			}
			
			// Links are formatted differently for different playmodes
			switch($playMode)
			{
				case "":
				case "track":
					$linkFront = '<a href="?dir='.rawurlencode($dir).'&playmode=track&track='.rawurlencode($track).'&trackno='.$i;
					// This playmode loads a new url to play a track, so if debugging is on, keep it on
					if ( !empty($debug) )
					{
						$linkFront .= '&debug=log';
					}
					$linkFront .= '" '.$class.' title="Play this track only">';
					break;
				case "random":
				case "folder":
					$linkFront = '<a href="#" '.$class.' onclick="jwplayer().playlistItem('.$i.');" title="Jump to this track in playlist">';
					break;
			}
			if ( $download )
			{
				$getTracks .= '<li id="'.$i.'" class="list-group-item track">';
				// Add download link
				$trackLink = getTrackLink($dir,$track);
				$getTracks .= '<a href="'.$trackLink.'&delivery=download" title="Download this track" target="_blank"><span class="glyphicon glyphicon-floppy-save"></span></a>&nbsp;&middot;&nbsp;';
			}
			$getTracks .= $linkFront;
			if ( $playMode=='track' || empty($playMode))
			{
				$getTracks .= '<span class="glyphicon glyphicon-play"></span> ';
			}
			if ( $playMode=='random')
			{
				$path = substr($file,strlen($dir)+1);
				$getTracks .= str_replace('/',' > ',$path);
			}
			else
			{
				$getTracks .= $track;
			}
			$getTracks .= '</a>';
			if ( $download )
			{
				$getTracks .= '</li>';
			}
			$i++;
		}
		unset($files);
		if ( $download )
		{
			$getTracks .= '</ul>';
		}
		else
		{
			$getTracks .= '</div>';
		}
	}
	else
	{
		$getTracks = '';
	}
	if ( !empty($debug) )
	{
		$log = "formatHtmlPlaylist(".$dir.")\n----------\n";
		$log .= "getTracks = ".$getTracks."\n\n";
		debugLog($log);
	}
	return $getTracks;
}

// Create log entry
function debugLog($log)
{
	// Open or create the log file
	$file = fopen(date('Ymd').'debug.log','a') or die ('Music Library & Player directory is not writable. Debug logging cannot be completed.');
	// Write the log entry
	fwrite($file,$log);
	fclose($file);
}
?>