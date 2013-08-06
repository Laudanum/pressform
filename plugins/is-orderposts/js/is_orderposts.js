//	is_orderposts.js
// <![CDATA[

//			var ajax_url = "/is_orderposts_update.php";
//			var ajax_url = orderposts_path + "/is_orderposts_update.php";

//	bug in scriptaculous means that ghosting isn't supported in IE7
//	http://github.com/sanjeevk/scriptaculous/commit/47fa18a622fabc87248b193fff833393d7c2982e
			do_ghost = true;
//			if ( Prototype.Browser.IE )
//				do_ghost = false;
			

			function is_order_init() {
				if ( document.getElementById('isorderposts') ) {
//					alert('isorderposts');
					Sortable.create('isorderposts', {
						overlap: 'horizontal', 
						constraint: false,
						ghosting: false,
					    onUpdate:is_update_attachments
					});
				}
	
				if ( document.getElementById('the-list') ) {
//	disable sorting for the media library page
					if ( location.href.indexOf('upload.php') > 0 || location.href.indexOf('media-upload.php') > 0) {
						return;
					}
					found = 0;
/*
//	switch to jQuery as ie8 breaks get elements by tag name
					container = document.getElementById('the-list');

					tags = container.getElementsByTagName('tr');
					for ( i = 0; i < tags.length; i++ ) {
						if ( tags[i].id.indexOf('post') == 0 || tags[i].id.indexOf('page') == 0 ) {
							tags[i].id = findreplace(tags[i].id, '-','_');
							found++;
						}
					}
*/					

					jQuery('tr[id^="post-"]').each(
						function() {
							jQuery(this).attr('id', findreplace(jQuery(this).attr('id'), '-','_'));
							found++;
						}
					);
					
					if ( found ) {
						jQuery('#the-list').sortable(
							{
								change : function(event, ui) {
									jQuery('img.is_orderposts_icon').attr('src', admin_path + '/images/generic.png');
								}
							}
						);
					}
/*					
						Sortable.create("the-list", { 
							tag:"tr", 
							ghosting: do_ghost,
//						    onUpdate: is_update_posts
						});
					}
*/
				}
	
//	<div id="media-items">
//	<div id='media-item-4' class='media-item child-of-3 preloaded'><div id='media-upload-error-4'></div><div class='filename'></div><div class='progress'><div 

//	disable sorting for the media pages ( its builtin )
/*
				if ( document.getElementById('media-items') ) {
					found = 0;
					container = document.getElementById('media-items');
//					alert(container);
					tags = container.getElementsByTagName('div');
					for ( i = 0; i < tags.length; i++ ) {
//						alert(tags[i].id);
					
						if ( tags[i].id.indexOf('media') == 0 || tags[i].id.indexOf('page') == 0 ) {
							tags[i].id = findreplace(tags[i].id, '-','_');
							found++;
						}
					}
					
					if ( found ) {
						Sortable.create("media-items", { 
							tag:"div", 
							ghosting: do_ghost,
						    onUpdate: is_update_posts
						});
					}
				}
*/
				
			}
		
		
			function is_setuptable(child) {
//	compensate for missing id's on edit post table tbody tag
				node = document.getElementById(child);
				p = getFirstAncestorByTagName(node, 'tbody');

				if ( ! p.id )
					p.setAttribute('id', 'the-list');					
			}
			
			
			function getFirstAncestorByTagName(target,tag) {
				var parent = target;
	    		while (parent = parent.parentNode) {
					if ( parent.tagName.toLowerCase() == tag.toLowerCase() ) {
			            return parent;
					}
				}
				return null;
			}


			function getFirstAncestorByClassName(target,className) {
				var parent = target;
				while (parent = parent.parentNode) {
					if (hasClassName(parent,className)) {
						return parent;
					}
				}
				return null;
			}
			
		
			function findreplace(str, find, replace) {
				while ( str.indexOf(find) && str.indexOf(find) > 1) {
					ind = str.indexOf(find);
					str = str.substring(0,ind) + replace + str.substring(ind+replace.length, str.length);
				}
				return str;
			}
			

			function is_update_attachments(container) {

//				var params = Sortable.serialize(container.id) + "&post_id=<?php echo $post_id; ?>";
				var params = Sortable.serialize(container.id) + "&container=" + container.id;
				
				var ajax = new Ajax.Request(ajax_url, {  
					method: "post",  
					parameters: params,
					onLoading: ajaxLoading,
					onLoaded: ajaxLoaded,
					onSuccess: handlerFunc,
					on404: function(t){alert('Error 404: location "' + t.statusText + '" was not found.')},
					onFailure: function(t) { alert('Error ' + t.status + ' -- ' + t.statusText);}
				});
			}
			
			jQuery('img.is_orderposts_icon').click(
				function() {
					
				}
			);

			function is_update_posts(container) {
				jQuery('img.is_orderposts_icon').attr('src', admin_path + '/images/loading.gif');
				data = jQuery('#the-list').sortable('serialize');
				
				jQuery.post(ajaxurl,
					{ 
						action : 'is_orderposts',
						subaction : 'save',
						data : data,
					},
					function(data) {	
						results = data.split("&");
						for ( i = 0; i < results.length; i++ ) {
							pair = results[i].split("=");
							jQuery('#menuorder_' + pair[0]).html(pair[1]);
						}						
						jQuery('img.is_orderposts_icon').attr('src', admin_path + '/images/yes.png');
					}
				);				
				
/*				
				var params = Sortable.serialize(container.id) + "&container=" + container.id;
				
				var ajax = new Ajax.Request(ajax_url, {  
					method: "post",  
					parameters: params,
					onLoading: ajaxLoading,
					onLoaded: ajaxLoaded,
					onSuccess: handlerFunc,
					on404: function(t){alert('Error 404: location "' + t.statusText + '" was not found.')},
					onFailure: function(t) { alert('Error ' + t.status + ' -- ' + t.statusText);}
				});
*/
			}
			
			
			function setNode(id, content) {
				document.getElementById(id).innerHTML = content;
			}
			
			
			var handlerFunc = function(t) {
				if ( t.responseText.toLowerCase().indexOf("err") == 0 ) {
					alert(t.responseText);
					return;
				}
				results = t.responseText.split("&");
				for ( i = 0; i < results.length; i++ ) {
					pair = results[i].split("=");
					setNode("menuorder_" + pair[0], pair[1]);
				}
			}
			
			
			var ajaxLoading = function(t) {
				document.getElementById('progress').src = orderposts_path + "/images/wheel-on.gif";
			}
			
			
			var ajaxLoaded = function(t) {
				document.getElementById('progress').src = orderposts_path + "/images/wheel-off.gif";
			}
			
			
			addLoadEvent(is_order_init);

	
	
//	]]!>
