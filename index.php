<?php
if ( !file_exists('config.php') )
{
	header('location: setup.php');
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Music Mania | Enjoy music with lyrics</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Latest compiled and minified CSS -->
<link href='//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' rel='stylesheet'>
<link href='style.css' rel='stylesheet'>
<?php
include('config.php');
/* Link to the jwplayer script */
?>
<script type='text/javascript' src='jwplayer<?=$jwversion;?>/jwplayer.js'></script>
<?php
if ( $jwversion == 7 )
{
	?>
	<script>jwplayer.key="<?=$jwplayer_key;?>";</script>
	<link href='mlp-skin.css' rel='stylesheet'>
	<?php
}
?>
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src='//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js'></script>
<script src='//oss.maxcdn.com/respond/1.4.2/respond.min.js'></script>
<![endif]-->
<?php
// Change $debugging option in config.php to true then 
// add &debug=log or ?debug=log to the end of the url query string
// Load a page without the added &debug=log to disable logging
// Change $debugging option in config.php back to false
if ( isset($_GET['debug']) && $_GET['debug']=='log' && $debugging == true )
{
	$debug = 'log';
}
include('functions.php');
if ( isset($_GET['playmode']) )
{
	$playMode = rawurlencode($_GET['playmode']);
	$container_class = 'container-fluid container-fluid-noplayer';
}
else
{
	// Apply a different container class for player vs. no player
	$container_class = 'container-fluid';
}
if ( isset($_GET['track']) )
{
	$track = rawurlencode($_GET['track']);
}
if ( isset($_GET['trackno']) )
{
	$trackno = rawurlencode($_GET['trackno']);
}
// Determine if direct links to files are OK or if stream is required
$stream = getWebRoot($root);
?>
</head>
<body>
<?php
// Process the current directory from query string
$dir = (isset($_GET['dir'])?rawurldecode($_GET['dir']):null);
if ( empty($dir) )
{
	$dir = $root;
}
// Remove the trailing slash if necessary
$dir = getDirectory($dir);
?>
     
<div id='container' class='<?=$container_class;?>'>
	<?php
	// Remove black player bar at top of the page if nothing to play
	if ( !empty($playMode) )
	{
		?>
		<div id='playerrow' class='row playerrow navbar-fixed-top'>                                      
                    <img style="margin-left: 20px" height="95px" width="140px" float="left" src="logo.png" alt="MusicMania">
                    <div id='playerCell' class='col-md-8 col-md-offset-2'>
				<div id='myElement'></div> <!--This is the player container-->
			</div>
		</div>
		<?php
	}
	?>
	<div class='row'>
		<br>
		<div class='col-md-8 col-md-offset-2'>
			<?php
			echo getRandomPlaylistLink($dir);
			echo getBreadcrumbs($dir);
			?>
		</div>
	</div>
	<?php
	if ( $playMode!='random')
	{
		$directories = displayDirectories($dir);
		if ( !empty($directories) )
		{
			?>
			<div class='row'>
				<div class='col-md-8 col-md-offset-2' id='main'>
					<br>
					<?=$directories;?>
				</div>
			</div>
			<?php
		}
	}
	else
	{
		echo '<br>';
	}
	if ( ($playMode == 'track' || $playMode == 'folder' || empty($playMode) ) && !empty($dir) )
	{
		$tracks = getTracksPlaylist($dir);
	}
	elseif ( $playMode == 'random' && !empty($dir) )
	{
		$random = getRandomPlaylist($dir);
		$tracks = $random['tracks'];
	}
	if ( !empty($tracks) )
	{
		?>
		<div class='row'>
			<div class='col-md-8 col-md-offset-2'>
				<?=$tracks;?>
			</div>
		</div>
		<?php
	}
	else
	{
		echo '<br>';
	}
	?>
</div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src='//code.jquery.com/jquery-3.1.1.min.js'></script>
<script src='//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>
<?php
if ( $playMode == 'track' && !empty($track) )
{
	$playlist = getTrackPlaylist($dir,$track);
}
elseif ( $playMode == 'folder' && !empty($dir) )
{
	// Deliver a multiple track playlist to the player
	$playlist = getFolderPlaylist($dir);
}
elseif ( $playMode == 'random' )
{
	// Get the playlist returned from getRandomPlaylist
	$playlist = $random['playlist'];
}
// Only bother with the player if there is something to play
if ( !empty($playMode) )
{
	?>
	<script type='text/javascript'>
		// Set the width of the player to match the list
		var playerWidth = getPlayerWidth();
		// Instantiate the player
		jwplayer('myElement').setup({
			height: 40,
			width: playerWidth,
			autostart: true,
			controls: true,
			<?=$skin;?>
			<?php
			if ( isset($primary) )
			{
				echo $primary;
			}
			?>
			<?=$repeat;?>
			<?=$playlist;?>
		});
		// Force the player width once it gets going
		// Version 6
		if ( <?=$jwversion;?>==6 )
		{
			jwplayer().onPlay(function(){
				setPlayerWidth();
			});
		}
		else
		{
			jwplayer().on('play',function(){
				setPlayerWidth();
			});
		}
		
		if ( <?=$jwversion;?>==6 )
		{
			jwplayer().onPlaylistItem(function(e){
				showNowPlaying(e);
			});
		}
		else
		{
			jwplayer().on('playlistItem',function(e){
				showNowPlaying(e);
			});
		}
		
		// Fill the width of the container with the player
		setPlayerWidth();
		// Get the width of the folder/track list
		function getPlayerWidth()
		{
			var playerWidth = $('.list-group').width();
			// Set a minimum width for the player
			if ( playerWidth < 430 )
			{
				playerWidth = 430;
			}
			return playerWidth;
		}
		function setPlayerWidth()
		{
			var playerWidth = getPlayerWidth();
			jwplayer().resize(playerWidth,40);
			scrollToTrack(jwplayer().getPlaylistIndex());
		}
		function showNowPlaying(e) 
		{
			// Show the play button icon on the current track
			var text = $('#'+e.index).html();
			// Loop through all <li> elements and remove the blue background
			$('.list-group-item').each(function(){
				// Remove the blue background from the last played item
				if ( $(this).hasClass('active') )
				{
					var thistext = $(this).html();
					$(this).removeClass('active');
				}
			});
			<?php
			// Add the blue background to the currently playing item
			if ( $playMode == 'track' )
			{
				?>
				var trackno = <?=$trackno;?>;
				$('#'+trackno).addClass('active');
				<?php
			}
			elseif ( $playMode == 'folder' || $playMode == 'random' )
			{
				?>
				var trackno = e.index;
				$('#'+e.index).addClass('active');
				<?php
			}
			?>
			scrollToTrack(trackno);
		}
		// Get position of the player, current playing track, and window
		function scrollToTrack(trackno)
		{
			var trackRow = document.getElementById(trackno);
			var trackPos = trackRow.getBoundingClientRect();
			var playerHeight = $('#playerrow').height();
			var windowHeight = $(window).height();
			// Scroll up to show currently playing track
			if (trackPos.top<playerHeight)
			{
				var scrollBy = playerHeight-trackPos.top+10;
				$('html, body').animate({scrollTop: '-='+scrollBy},500);
			}
			// Scroll down to show currently playing track
			else if (trackPos.bottom>windowHeight)
			{
				var scrollBy = trackPos.bottom-windowHeight;
				$('html, body').animate({scrollTop: '+='+scrollBy},500);
			}
		}
	</script>
	<?php
}
?>
<iframe frameborder="0" margin="0" padding="0" height="100%" width="100%" overflow="" src="https://lyricsmania.com"></iframe>
</body>
</html>