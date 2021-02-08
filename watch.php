<?php
include("header.php");
$share_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
if(!isset($_GET["v"])){
	die();
} else {
	$vid = $_GET["v"];
}

function limit_echo($x, $length)
{
  if(strlen($x)<=$length)
  {
    echo $x;
  }
  else
  {
    $y=substr($x,0,$length) . '...';
    echo $y;
  }
}

if(($_GET["player"]) == 2){
	$player = 2;
} else {
	if(($_GET["player"]) == 1){
	$player = 1;
} else {
	if(($_GET["player"]) == 0){
	$player = 0;
} else {
	$player = null;
}
}
}

$VideoName = "No title.";
$VideoDesc = "No description.";
$Uploader = "Unknown";
$UploadDate = "Unknown";
$loaded = 0;

function makeClickableLinks($s) {
  return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
}

function makeClickableLinksLimit($s) {
  return preg_replace('@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@', '<a href="$1" target="_blank">$1</a>', $s);
}

$vidfetch = mysqli_query($connect, "SELECT * FROM videodb WHERE VideoID='". $vid ."'");
$vdf = mysqli_fetch_assoc($vidfetch);
//do not show anything if the video dosent exist
if (!isset($vdf['VideoID'])) {
echo "<script>window.location.replace('/?vexist=0');</script>";
die();
} else {
$VideoID = $vdf['VideoID'];
}
$Uploader = htmlspecialchars($vdf['Uploader']); // get all video information
$VideoName = htmlspecialchars($vdf['VideoName']);
$ViewCount = $vdf['ViewCount'];
$PreUploadDate = htmlspecialchars($vdf['UploadDate']);
$VideoDesc = nl2br(htmlspecialchars($vdf['VideoDesc']));
$VideoCategory = htmlspecialchars($vdf['VideoCategory']);
$VideoFile = $vdf['VideoFile'];
$DateTime = new DateTime($PreUploadDate);

$userfetch = mysqli_query($connect, "SELECT * FROM users WHERE username='". $Uploader ."'"); // calls for channel info
$udf = mysqli_fetch_assoc($userfetch);

if(isset($_SESSION["username"])){
$localfetch = mysqli_query($connect, "SELECT * FROM users WHERE username='". $_SESSION["username"] ."'"); // calls for channel info
$ldf = mysqli_fetch_assoc($localfetch);

$vidnew = $ldf["videos_watched"] + 1;

$updateQuery = "UPDATE users SET videos_watched='". $vidnew ."' WHERE username='". $_SESSION["username"] ."'";
mysqli_query($connect,$updateQuery);
}
$PreRegisteredOn = $udf['registeredon'];
$DateTime2 = new DateTime($PreRegisteredOn);
$RegisteredOn = $DateTime2->format('F j, Y');
$UploadDate = $DateTime->format('F j, Y');

$sql= mysqli_query($connect, "SELECT * FROM comments ORDER BY commentid DESC");

$commentcount = 0;

//$viewnew = $ViewCount + 1;

//$updateQuery = "UPDATE videodb SET ViewCount='". $viewnew ."' WHERE VideoID='". $vid ."'";
//mysqli_query($connect,$updateQuery);

//$ViewCount = $viewnew;

if(!$VideoDesc) {
	$VideoDesc = "<i>No description...</i>";
}

$commentcount = 0;

while ($searchcomments = mysqli_fetch_assoc($sql)) { // get comments for video
$usercommentlist = htmlspecialchars($searchcomments['user']); // commente
$datecommentlist = $searchcomments['date']; // comment date
$messagecommentlist = htmlspecialchars($searchcomments['comment']); // actual text for comment
$idcommentlist = $searchcomments['id']; // comment id, to get descending order to work
$hidden = $searchcomments['hidden']; // hidden comments are for deleted videos

if ($idcommentlist == $vid AND $hidden != 1) {
$commentcount++; // count the amount of comments
}
}
?>
<html>
<title><?php echo $VideoName ?> - PokTube</title>
<meta name="title" content="<?php echo $VideoName ?> - PokTube">
<meta name="description" content="<?php echo $VideoDesc ?>">
<link rel="stylesheet" href="styles.css" type="text/css"> <!-- iirc this is old 2005 css.
it's a shitty idea to mix 2017 bitview css and old july 2005 css, but this will do for now.
i actually don't fucking care because this will be replaced with a XP/KDE3 style later on.
-pf94 1/24/2021 -->
<body><table width="950" cellpadding="0" cellspacing="0" border="0" align="center">
	<tbody><tr>
		<td bgcolor="#FFFFFF" style="padding-bottom: 25px;">
<script>
	function getFormVars(form) 
	{	var formVars = new Array();
		for (var i = 0; i < form.elements.length; i++)
		{
			var formElement = form.elements[i];
			formVars[formElement.name] = formElement.value;
		}
		return urlEncodeDict(formVars);
	}

	function showCommentReplyForm(form_id, reply_parent_id, is_main_comment_form) {
		if(!CheckLogin()) 
			return false;
		printCommentReplyForm(form_id, reply_parent_id, is_main_comment_form);
	}
	function printCommentReplyForm(form_id, reply_parent_id, is_main_comment_form) {

		var div_id = "div_" + form_id;
		var reply_id = "reply_" + form_id;
		var reply_comment_form = "comment_form" + form_id;
		
		if (is_main_comment_form)
			discard_visible="style='display: none'";
		else
			discard_visible="";
		
		var innerHTMLContent = '\
		<div style="padding-bottom: 5px; font-weight: bold; color: #444; display: none;">Comment on this video:</div>\
		<form name="' + reply_comment_form + '" id="' + reply_comment_form + '" method="post" action="comment_servlet" >\
			<input type="hidden" name="video_id" value="pTrJLz2Nwsg">\
			<input type="hidden" name="add_comment" value="">\
			<input type="hidden" name="form_id" value="' + reply_comment_form + '">\
			<input type="hidden" name="reply_parent_id" value="' + reply_parent_id + '">\
			<textarea tabindex="2" name="comment" cols="55" rows="3"></textarea>\
			<br>\
			Attach a video:\
			<select name="field_reference_video">\
				<option value="">- Your Videos -</option>\
				<option value="">- Your Favorite Videos -</option>\
			</select>\
			<input type="button" name="add_comment_button" \
								value="Post Comment" \
								onclick="postThreadedComment(\'' + reply_comment_form + '\');">\
			<input type="button" name="discard_comment_button"\
								value="Discard" ' + discard_visible + '\
								onclick="hideCommentReplyForm(\'' + form_id + '\',false);">\
		</form></div>';
		if(!is_main_comment_form) {
			toggleVisibility(reply_id, false);
		}
		toggleVisibility(div_id, true);
		setInnerHTML(div_id, innerHTMLContent);
	}
</script>
<div align="center" style="padding-bottom: 10px;">





</div>

<table width="895" align="center" cellpadding="0" cellspacing="0" border="0">
	<tr valign="top">
		<td width="515" style="padding-right: 15px;">
			<br>
		<div id="watch-vid-title" class="title">
			<h1><?php echo $VideoName ?></h1>
		</div>
		<!---<div style="font-size: 13px; font-weight: bold; text-align:center;">
		<a href="#">Share</a>
		// <a href="#comment">Comment</a>
		// <a href="#" target="invisible" onclick="return FavoritesHandler();">Add to Favorites</a>
		// <a href="#">Contact Me</a>
		</div>-->	<tbody><tr valign="top">
		<td width="510" style="padding-right: 15px;">
		<br>
			<div width="640" height="380">
				<iframe style='outline: 0px solid transparent;' src='./player.php?v=<?php echo $VideoFile; ?>&player=<?php echo $player; ?>' width='650' height='380' frameBorder='0' scrolling='no' debug='true'></iframe>
			</div>
		</div>
		
			<table style="margin-left: 8px;margin-right: 8px;" width="480" cellspacing="0" cellpadding="0" border="0" align="center">
			<tr>
				<td>
					<div class="watchDescription"><?php echo $VideoDesc ?>					<div class="watchAdded" style="margin-top: 5px;">
										</div>
					</div>
					<div><img style="float: left; margin: 0px 5px 10px 0px; padding: 5px 0px 0px 0px;" src="content/profpic/<?php echo $Uploader?>.png" onerror="this.src='img/profiledef.png'" class="thumb" width="48" height="48">
					<div class="label" style="padding: 5px 0px 0px 0px;"><?php echo $Uploader ?></div></div>
					<div class="watchAdded">
					Uploaded: <?php echo $UploadDate ?>
					</div>
				</td>
			</tr>
		</table>

		<table width="400" cellpadding="0" cellspacing="0" border="0" align="center">
	
					</div>
			</td>
		</tr>
	</tbody></table>

<!-- google_ad_section_end -->

	<!-- watchTable -->
		
		<div style="padding: 15px 0px 10px 0px;">
		<table width="98%" align="center" cellpadding="0" cellspacing="0" border="0" bgcolor="#E5ECF9">
			<tr>
				<td><img src="img/box_login_tl.gif" width="5" height="5"></td>
				<td width="100%"><img src="img/pixel.gif" width="1" height="5"></td>
				<td><img src="img/box_login_tr.gif" width="5" height="5"></td>
			</tr>
			<tr>
				<form name="linkForm" id="linkForm">
				<td><img src="img/pixel.gif" width="5" height="1"></td>
				<td align="center">
		
				<div style="font-size: 11px; color: #000; padding: 5px 0px 5px 0px;">Share this video! Copy and paste this link:</div>
				<div style="font-size: 11px; padding-bottom: 15px;">
				<input name="video_link" type="text" onclick="javascript:document.linkForm.video_link.focus();document.linkForm.video_link.select();" value="<?php echo $share_link; ?>" size="50" readonly="true" style="font-size: 10px; text-align: center;">
				</div>
				
				</td>
				<td><img src="img/pixel.gif" width="5" height="1"></td>
				</form>
			</tr>
			<tr>
				<td><img src="img/box_login_bl.gif" width="5" height="5"></td>
				<td><img src="img/pixel.gif" width="1" height="5"></td>
				<td><img src="img/box_login_br.gif" width="5" height="5"></td>
			</tr>
		</table>
		</div>

<br>
<a name="comment"></a>

		<div style="padding-bottom: 5px; font-weight: bold; color: #444;">Comment on this video:</div>
				<div id="div_main_comment">		<div style="padding-bottom: 5px; font-weight: bold; color: #444; display: none;">Comment on this video:</div>		<form name="comment_formmain_comment" id="comment_formmain_comment" method="post" action="comment.php"><input type="hidden" name="video_id" value="<?php echo $vid; ?>"><textarea tabindex="2" name="comment" cols="78" rows="3"></textarea>			<br>			<input type="submit" name="add_comment_button" value="Post Comment" onclick="postThreadedComment(&#39;comment_formmain_comment&#39;);">			<input type="button" name="discard_comment_button" value="Discard" style="display: none" onclick="hideCommentReplyForm(&#39;main_comment&#39;,false);">		</form></div>
		
		
<br>
		
<table width="650">
<tbody><tr>
<td>
	<table class="commentsTitle" width="100%">
	<tbody><tr>
		<td>Comments (<?php echo $commentcount; ?>): </td>
	</tr></tbody></table>
</td>
</tr>
<?php
$sql= mysqli_query($connect, "SELECT * FROM comments ORDER BY commentid DESC");

$count = 0;

while ($searchcomments = mysqli_fetch_assoc($sql)) { // get comments for video
$usercommentlist = htmlspecialchars($searchcomments['user']); // commente
$datecommentlist = $searchcomments['date']; // comment date
$messagecommentlist = htmlspecialchars($searchcomments['comment']); // actual text for comment
$idcommentlist = $searchcomments['id']; // comment id, to get descending order to work
$hidden = $searchcomments['hidden']; // hidden comments are for deleted videos

if ($idcommentlist == $vid AND $hidden != 1) {
echo "<tr>
<td>
      			<a name='8n9OjARLLDs'>
					<table class='parentSection' cellspacing='0' cellpadding='0' id='comment_8n9OjARLLDs' width='100%' style='margin-left: 0px'>
					<tbody><tr valign='top'>
						<td>
<!-- google_ad_section_start -->
		".$messagecommentlist."
<!-- google_ad_section_end -->
			<div class='userStats'>
				<a href='profile.php?user=".$usercommentlist."'>".$usercommentlist."</a>
				 - (".$datecommentlist.")
			</div>
	<div id='div_comment_form_id_8n9OjARLLDs'></div>
							</td>
					</tr>
				</tbody></table>
			</a></td>
</tr>";
$count++; // count the amount of comments
}
}
if($count == 0) {
	echo "<tr><td><center><p style='padding: 10px; font-size: 15px;'>No comments found.</p></center></td></tr>"; // echos "no comments found" if no comments
}
?>
</tbody></table>
		

		</td>
		<td width="280">
				<div style="padding-bottom: 10px;">
					<table width="280" align="center" cellpadding="0" cellspacing="0" border="0" bgcolor="#EEEEEE">
						</table>
		</div>
			
			<table width="280" align="center" cellpadding="0" cellspacing="0" border="0" bgcolor="#CCCCCC">
				<tbody><tr>
					<td><img src="./img/box_login_tl.gif" width="5" height="5"></td>
					<td><img src="./img/pixel.gif" width="1" height="5"></td>
					<td><img src="./img/box_login_tr.gif" width="5" height="5"></td>
				</tr>
				<tr>
					<td><img src="./img/pixel.gif" width="5" height="1"></td>
					<td width="270">
					<div class="moduleTitleBar">
					<table width="270" cellpadding="0" cellspacing="0" border="0">
						<tbody><tr valign="top">

							<td><div class="moduleFrameBarTitle">Videos from <?php echo $Uploader ?></div></td>
							<td align="right"><div style="font-size: 11px; margin-right: 5px;"></div></td>
						</tr>
					</tbody></table>
					</div>

							<div id="side_results" name="side_results">
					<?php				
$x = 1; 
$sql = mysqli_query($connect, "SELECT * FROM videodb ORDER by `UploadDate` DESC"); //instructions for sql

while ($fetch = mysqli_fetch_assoc($sql)) { //go forward with instructions
if ($x == 9) {
	break;
}
$idvideolist = $fetch['VideoID'];
$namevideolist = htmlspecialchars($fetch['VideoName']);
$uploadervideolist = htmlspecialchars($fetch['Uploader']); // get recommendations information
$viewsvideolist = $fetch['ViewCount'];

if ($uploadervideolist == $Uploader && $idvideolist !== $vid) {
echo "<div class='moduleFrameEntry'>
<table width='235' cellpadding='0' cellspacing='0' border='0'>
							<tbody><tr valign='top'>
								<td width='90'>
									<a href='watch.php?v=".$idvideolist."&player=$player' class='bold' target='_parent'><img src='./content/thumbs/".$idvideolist.".png' onerror=\"this.src='img/default.png'\" class='moduleEntryThumb' width='80' height='60'></a></td>
								<td>
									<div class='moduleFrameTitle'><a href='watch.php?v=".$idvideolist."' target='_parent'>".$namevideolist."</a></div>
									<div class='moduleFrameDetails'>
										by <a href='profile.php?user=".$uploadervideolist."' target='_parent'>".$uploadervideolist."</a>
									</div>
									<div class='moduleFrameDetails'>
										Views: ".$viewsvideolist."<br>
									</div>
		
								</td>
							</tr>
						</tbody></table>
					</div>";
$x++;
}
}
?>

					</td>
					<td><img src="./img/pixel.gif" width="5" height="1"></td>
				</tr>
				<tr>
					<td><img src="./img/box_login_bl.gif" width="5" height="5"></td>
					<td><img src="./img/pixel.gif" width="1" height="5"></td>
					<td><img src="./img/box_login_br.gif" width="5" height="5"></td>
				</tr>
			</tbody></table>
			
			<br/>
			
			<table width="280" align="center" cellpadding="0" cellspacing="0" border="0" bgcolor="#CCCCCC">
				<tbody><tr>
					<td><img src="./img/box_login_tl.gif" width="5" height="5"></td>
					<td><img src="./img/pixel.gif" width="1" height="5"></td>
					<td><img src="./img/box_login_tr.gif" width="5" height="5"></td>
				</tr>
				<tr>
					<td><img src="./img/pixel.gif" width="5" height="1"></td>
					<td width="270">
					<div class="moduleTitleBar">
					<table width="270" cellpadding="0" cellspacing="0" border="0">
						<tbody><tr valign="top">

							<td><div class="moduleFrameBarTitle">Recommended Videos</div></td>
							<td align="right"><div style="font-size: 11px; margin-right: 5px;"></div></td>
						</tr>
					</tbody></table>
					</div>

							<div id="side_results" name="side_results">
					<?php
$sql = mysqli_query($connect, "SELECT * FROM videodb ORDER BY rand() DESC LIMIT 8"); //instructions for sql

while ($fetch = mysqli_fetch_assoc($sql)) { //go forward with instructions
$idvideolist = $fetch['VideoID'];
$namevideolist = htmlspecialchars($fetch['VideoName']);
$uploadervideolist = htmlspecialchars($fetch['Uploader']); // get recommendations information
$viewsvideolist = $fetch['ViewCount'];

echo "<div class='moduleFrameEntry'>
<table width='235' cellpadding='0' cellspacing='0' border='0'>
							<tbody><tr valign='top'>
								<td width='90'>
									<a href='watch.php?v=".$idvideolist."&player=$player' class='bold' target='_parent'><img src='./content/thumbs/".$idvideolist.".png' onerror=\"this.src='img/default.png'\" class='moduleEntryThumb' width='80' height='60'></a></td>
								<td>
									<div class='moduleFrameTitle'><a href='watch.php?v=".$idvideolist."' target='_parent'>".$namevideolist."</a></div>
									<div class='moduleFrameDetails'>
										by <a href='profile.php?user=".$uploadervideolist."' target='_parent'>".$uploadervideolist."</a>
									</div>
									<div class='moduleFrameDetails'>
										Views: ".$viewsvideolist."<br>
									</div>
		
								</td>
							</tr>
						</tbody></table>
					</div>";
}
?>

					</td>
					<td><img src="./img/pixel.gif" width="5" height="1"></td>
				</tr>
				<tr>
					<td><img src="./img/box_login_bl.gif" width="5" height="5"></td>
					<td><img src="./img/pixel.gif" width="1" height="5"></td>
					<td><img src="./img/box_login_br.gif" width="5" height="5"></td>
				</tr>
			</tbody></table>
		
		</td>
	</tr>
</tbody></table>


		</td>
	</tr>
</tbody></table>
<?php include('footer.php') ?></body></html>
