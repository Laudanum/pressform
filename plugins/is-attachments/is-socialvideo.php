<?php
/**
 * @package is-attachments
 * @author Mr Snow
 * @version 0.8
 */

	function is_upload_tab($tabs='') {
		$tabs['social_video'] = __('Social video');
		return $tabs;
	}

/*	
	function wp_upload_tabs($array) {

		$tab = array(
    		'social' => array('From Social', 'upload_files', 'is_upload_social_content', 0)
		);

    	return array_merge($array,$tab);
	}

*/	
	
	
	function media_upload_social_video() {
	
		if ( isset($_POST['social_video_save']) ) {
//	http://www.prelovac.com/vladimir/improving-security-in-wordpress-plugins-using-nonces
//	http://othersideofthepillow.com/tutorials/wordpress/plugins/admin-nonce/
//	why we can't use our own nonce names i've no idea
			check_admin_referer('media-form');

			$errors = media_upload_social_video_handler();
//			print_r($errors);
//			exit;
			
			if ( $errors )
				return wp_iframe('media_upload_social_video_form', $errors);
			else
				return wp_iframe('media_upload_gallery', $errors);
		} else {	
			return wp_iframe('media_upload_social_video_form', $errors);
		}
	}
	
	
	function media_upload_social_video_handler() {
		if ( ! empty($_POST['social_video']['href'] ) ) { 
			$urls = $_POST['social_video']['href'];
			$title = $_POST['social_video']['title'];
			
			$urls = split("\n", $urls);
			$errors = array();
			
			foreach ( $urls as &$url ) {
				$url = trim($url);
//				print $url;
				if ( ! $url )
					continue;
				else if ( strstr($url, 'youtube') )
					array_push($errors, media_upload_youtube_handler($url, $title));
				else if ( strstr($url, 'vimeo') )
					array_push($errors, media_upload_vimeo_handler($url, $title));
				else
					array_push($errors, "No social videos discovered");
			}
		} else {
			$errors = array("Please enter a valid URL");
			return $errors;
		}
	}
	
	
	function media_upload_vimeo_handler($url, $title='') {
		$parent_id = $_REQUEST['post_id'];

		$pattern = "/\/(\d+)$/";

		preg_match($pattern, $url, $matches);
		
		if ( count($matches) < 2 )
			return "No Vimeo ID";
		
	//	http://vimeo.com/api/v2/video/video_id.output
		$id = $matches[1];
	
//		print "<pre>";
//		print $id;
		$json = json_decode(file_get_contents("http://vimeo.com/api/v2/video/$id.json"), TRUE);
//		print_r($json);
//		print "</pre>";

		$media = $json[0];
		$video = $media['url'];
		
		$description = $media['description'];
		if ( ! $title )
			$title = $media['title'];
		$tags = explode(', ', $media['tags']);
		$duration = $media['duration'];

		$thumbnail = $media['thumbnail_large'];
		
//		$ratings = $json['entry']['gd$rating'];
//		$statistics = $json['entry']['yt$statistics'];
//		$comments_xml = $json['entry']['gd$comments'];		
		
		$statistics = array();

		$statistics['likes'] = $media['stats_number_of_likes'];
		$statistics['plays'] = $media['stats_number_of_plays'];
		$statistics['favourites'] = $media['stats_number_of_comments'];
		
		$attachments = array();
		
		$attachments['video'] = new _attachment();
		$attachments['video']->set('post_title', $title);
		$attachments['video']->set('post_content', $description);
		$attachments['video']->set('guid', $video);
		$attachments['video']->set('post_mime_type', 'video/x-vimeo');
		$attachments['video']->set('post_parent', $parent_id);
		
		$attachments['video']->meta['social-type'] = 'Vimeo';
		if ( $ratings )
			$attachments['video']->meta['ratings'] = $ratings;
		if ( $statistics )
			$attachments['video']->meta['statistics'] = $statistics;
		if ( $comments_xml )
			$attachments['video']->meta['comments_xml'] = $comments_xml;
		
		$attachments['poster'] = new _attachment();
		$attachments['poster']->set('post_title', $title . ' poster');
		$attachments['poster']->set('guid', $thumbnail);
		$attachments['poster']->set('post_mime_type', 'image/jpeg');
		$attachments['poster']->set('post_parent', $parent_id);
		$attachments['poster']->meta['roles']['poster'] = true;

//		print_r($attachments);
		
		$errors = _insert_social($attachments);
		return $errors;
		
	}
	
	
	function media_upload_youtube_handler($url, $title='') {
/*
//	http://code.google.com/apis/youtube/2.0/developers_guide_protocol_video_entries.html

	xml
	curl http://gdata.youtube.com/feeds/api/videos/QVM-7JM4lyk
	json
	curl http://gdata.youtube.com/feeds/api/videos/QVM-7JM4lyk?alt=json

	http://code.google.com/apis/youtube/2.0/reference.html#youtube_data_api_tag_entry
	http://code.google.com/apis/youtube/2.0/reference.html#youtube_data_api_tag_media:group
	http://code.google.com/apis/youtube/2.0/reference.html#youtube_data_api_tag_media:thumbnail
*/
		$parent_id = $_REQUEST['post_id'];
		
//	how do we get HD ?
//	what if yt:format=5 is missing ?

//	http://www.youtube.com/watch?v=QVM-7JM4lyk&feature=email		
		$pattern = "/[\?\&]v=([\w\d\-]+)\&*/";

		preg_match($pattern, $url, $matches);
				
		if ( count($matches) < 2 )
			return "No YouTube ID";
		
		
		$id = $matches[1];
		$json = json_decode(file_get_contents("http://gdata.youtube.com/feeds/api/videos/$id?alt=json"), TRUE);
		
//	the container for all the media for this video		
		$media = $json['entry']['media$group'];
		
		$videos = $media['media$content']; //	flash, 3gp
//		print_r($videos);
		
//	by default use the first video
		$video = $videos[0];
//	unless we can find the default
		foreach ( $videos as $v ) {
			if ( $v['isDefault'] )
				$video = $v;
		}
		
//		yt$format, duration, isDefault

/*		
[media$description]
 [media$keywords]
 [media$thumbnail]
 [media$title]
 [yt$duration]
 */

		$description = $media['media$description']['$t'];
		if ( ! $title )
			$title = $media['media$title']['$t'];
		$tags = explode(', ', $media['media$keywords']['$t']);
		$duration = $media['yt$duration']['seconds'];

		$thumbnails = $media['media$thumbnail'];
//	by default use the first thumbnail
		$thumbnail = $thumbnails[0];
//	unless we can find a bigger one
		foreach ($thumbnails as $t) {
			if ( $t['width'] > 120 ) {
				$thumbnail = $t;
				break;
			}
		}
		
//		http://gdata.youtube.com/feeds/api/videos/NPUoQW1eIHQ/comments

		$ratings = $json['entry']['gd$rating'];
		$statistics = $json['entry']['yt$statistics'];
		$comments_xml = $json['entry']['gd$comments'];
		
//		print "title $title<br />description $description<br />";
//		print_r($video);
		
		$attachments = array();
		
		$attachments['video'] = new _attachment();
		$attachments['video']->set('post_title', $title);
		$attachments['video']->set('post_content', $description);
//		$attachments['video']->set('guid', $video['url']);
//	use the normal youtube url as longtail player supports that too 
		$attachments['video']->set('guid', $url);
		$attachments['video']->set('post_mime_type', 'video/x-youtube');
		$attachments['video']->set('post_parent', $parent_id);
		
//	place the api's url in meta instead ( see comment above ) 
		$attachments['video']->meta['url'] = $video['url'];
		$attachments['video']->meta['social-type'] = 'YouTube';
		$attachments['video']->meta['id'] = $id;
		if ( $ratings )
			$attachments['video']->meta['ratings'] = $ratings;
		if ( $statistics )
			$attachments['video']->meta['statistics'] = $statistics;
		if ( $comments_xml )
			$attachments['video']->meta['comments_xml'] = $comments_xml;
		
		$attachments['poster'] = new _attachment();
		$attachments['poster']->set('post_title', $title . ' poster');
		$attachments['poster']->set('guid', $thumbnail['url']);
		$attachments['poster']->set('post_mime_type', 'image/jpeg');
		$attachments['poster']->set('post_parent', $parent_id);
		$attachments['poster']->meta['roles']['poster'] = true;
		$attachments['poster']->meta['pid'] = $id;

		$errors = _insert_social($attachments);
		return $errors;
		
	}
	
	
	function _insert_social($attachments) {
//	insert the video into the database
//		print_r($attachments['video']->post);
		
		$attach_id = wp_insert_attachment($attachments['video']->post, null, $attachments['video']->post['post_parent']);

		$attach_data = wp_generate_attachment_metadata($attach_id, '');
//	the metadata needs updating as wp_insert_attachment will try and thumbnail the images
		if ( ! is_array($attach_data) )
			$attach_data = array();
		array_push($attach_data, $attachments['video']->meta);
		wp_update_attachment_metadata($attach_id, $attach_data);
		
//		print "video $attach_id done<br />";
//		print_r($attach_data);

//	get the poster on to our server
		$poster = $attachments['poster']->post['guid'];
//	create a directory
//		$dir_name = get_option('upload_path') . '/' . $attachments['video']->post['post_parent']; 
		$dir = wp_upload_dir();
		$dir_name = $dir['path'];

		if ( ! is_dir($dir_name) )
			mkdir($dir_name) or die("Could not create directory " . $dir_name);
		$dest = $dir_name . '/' . $attachments['video']->post['post_parent'] . '_' . $attachments['video']->meta['id'] . '_' . _get_filename($poster);
//	copy the file to the server
		$data = file_get_contents($poster); 
		$file = fopen($dest, "w+"); 
		fputs($file, $data);
		fclose($file);

//	only parent it to the video attachment if we're using attachments as parents
	
		global $options;
//		print "video parent is " . $attachments['video']->post['post_parent'] . "<br />";
		if ( $options['parent_menu_attachments'] )
			$pid = $attach_id;
		else
			$pid = $attachments['video']->post['post_parent'];
			
//		print ("parent is $pid<br />");
		
//	add it to the database
		$poster_id = wp_insert_attachment($attachments['poster']->post, $dest, $pid);

		$attach_data = wp_generate_attachment_metadata($poster_id, $dest);
		$attach_data = array_merge($attach_data, $attachments['poster']->meta);
		wp_update_attachment_metadata($poster_id, $attach_data);
		 
		return;
	}
	
	
	function media_upload_social_video_form($errors) {
		media_upload_header();
		$post_id 	= intval($_REQUEST['post_id']);
		$form_action_url = get_option('siteurl') . "/wp-admin/media-upload.php?type={$_POST['type']}&tab=social_video&post_id=$post_id";
?>

<form enctype="multipart/form-data" method="post" action=<?=$form_action_url?>" class="media-upload-form type-form validate" id="video-form">
<input type="hidden" name="post_id" id="post_id" value="<?=$post_id?>" />
<?php wp_nonce_field('media-form'); ?>
		<h3 class="media-title">Add social videos from URLs</h3>
<div id="media-items">
<div class="media-item media-blank">

	<table class="describe"><tbody>
		<tr>
			<th valign="top" scope="row" class="label">
				<span class="alignleft"><label for="social_video[href]">Video URL</label></span>
				<span class="alignright"><abbr title="required" class="required">*</abbr></span>
			</th>
			<td class="field">
				<textarea id="social_video[href]" name="social_video[href]" type="text" aria-required="true" style='height : 100px;'></textarea>
			</td>
		</tr>
		<tr><td></td><td class="help">Copy &amp; paste URLs - one per line</td></tr>
		<tr>
			<th valign="top" scope="row" class="label">
				<span class="alignleft"><label for="social_video[title]">Title</label></span>
				<span class="alignright"></span>
			</th>
			<td class="field"><input id="social_video[title]" name="social_video[title]" value="" type="text" aria-required="true"></td>
		</tr>
		<tr><td></td><td class="help">Link text, e.g. &#8220;Lucy on YouTube&#8220;</td></tr>
		<tr>
			<td></td>
			<td>
				<input type="submit" class="button" name="social_video_save" value="Select video" />
			</td>
		</tr>
	</tbody></table>
</div>
</div>
</form>

<?php

	}
	

	function is_getSocialURI($uri, $id=null) {
//	disable proplayer - it breaks shopp sessions
//		if ( strpos($uri, 'vimeo') ) {
//			$uri = is_proplayer("[pro-player]" . $uri . "[/pro-player]", $id);
//		} 
		return $uri;
	}
	

	function is_proplayer($content='[pro-player]http://www.vimeo.com/2128745[/pro-player]', $id=null) {
	
//		new ProPlayer > ContentHandler > addFileAttributes > VideoFactory > createVideoSource
//		$videoURL = $this->contentHandler->getVideoUrl($match)								
									$proplayer = new ProPlayer();
								//	$content = '[pro-player]http://www.vimeo.com/2128745[/pro-player]';
								//	print $proplayer->PLAYER_CODE_PATTERN;
								//	print "<pre>";
									preg_match_all($proplayer->PLAYER_CODE_PATTERN, trim($content), $matches);
									
							//		print_r($matches[0][0]);
									$proplayer->setDefaults();
									$proplayer->setAttributes($matches[0][0]);	
					
//					print_r($proplayer);
									$videoURL = $proplayer->contentHandler->getVideoUrl($matches[0][0]);
								//	$url = $proplayer->contentHandler->getVideoUrl("");
								//	print $videoURL;
								//	proplayer($match);
						
						
									global $wp_query;
							//		$id = $wp_query->post->ID."-0";
									if ( ! $id )
										$id = rand(0,64000);
								//	print "id - $id<br />";
									$fileAttributes = $proplayer->contentHandler->addFileAttributes($id, $videoURL, $proplayer->type, $proplayer->previewImage);
								//	print_r($fileAttributes);
									$additionalOptions = $proplayer->getAdditionalPlayerOptions($videoURL);
								//	print_r($additionalOptions);
									$proplayer->initializePlayer();
							
								//	print_r($proplayer);
									
								//	$entries = $proplayer->contentHandler->playlistController->entries;
								/*	
									foreach ( $entries as $entry ) {
										if ( is_array($entry) ) {
											print "array 0 <br />";
											foreach ( $entry as $e ) {
												print "array 1<br />";
												if ( $e->url )
													$url = $e->url;
											}
										}
									}
								*/	
									$url = get_bloginfo('wpurl') . "/wp-content/plugins/proplayer/playlist-controller.php?pp_playlist_id=$id&sid=" . strtotime("now");
								//	print "$url<br>";
									
								//	echo $proplayer->buildPlayer($match = $matches[0][0], $id = $id);
									
									return $url;
									
									print "
									<script type='text/javascript'>
		obj = jQuery('#target');
		_w = 460
		_h = 320
template_directory = '/wordpress/wp-content/themes/gridfocus/';
target='target';
		var so = new SWFObject(template_directory + 'libs/mediaplayer/player.swf','flvplayer',_w,_h,'8');
//		alert(1);
		so.addParam('allowscriptaccess','always');
		so.addParam('allowfullscreen','true');
		so.addVariable('width', _w);
		so.addVariable('height', _h);
//		so.addVariable('shownavigation','false');
		so.addVariable('backcolor', '000000');
		so.addVariable('frontcolor', 'ffffff');
		so.addVariable('lightcolor', 'f63a1b');
		so.addVariable('controlbar', 'over');
		so.addVariable('autostart', 'true');
		so.addVariable('displayclick', 'fullscreen');
//		so.addVariable('image', img);
		so.addVariable('file', '$url');
//		so.addVariable('javascriptid','flvplayer');
//		so.addVariable('enablejs','true');
//		alert(100);
		so.write(target); 
									</script>
									
									";
									
	}

  function is_socialFormatter($attachment, $size='medium') {
    if ( strpos($attachment->mime_type, 'vimeo') > 0 ) {
      return is_VimeoFormatter($attachment, $size);
    } else {
      return $attachment->mime_type;
    }
    
  }
  
  function is_VimeoFormatter($attachment, $size='medium') {
    $id = end(explode("/", $attachment->src));
    return "
      <iframe src='http://player.vimeo.com/video/$id?title=0&byline=0&portrait=0' width='400' height='225' frameborder='0'></iframe>
    ";
  }

?>