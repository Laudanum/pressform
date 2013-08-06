<?php
/**
 * @package is-attachments
 * @author Mr Snow
 * @version 0.81
 */

class AttachmentsWidget extends WP_Widget {
 /**
  * Declares the HelloWorldWidget class.
  *
  */
	function AttachmentsWidget(){
		$widget_ops = array('classname' => 'widget_is_attachments', 'description' => __( "Attachments gallery") );
		$control_ops = array('width' => 300, 'height' => 300);
		$this->WP_Widget('is_attachments', __('Attachments gallery'), $widget_ops, $control_ops);
	}

  /**
	* Displays the Widget
	*
	*/
	function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
		
//		$lineOne = empty($instance['lineOne']) ? 'Hello' : $instance['lineOne'];
//		$lineTwo = empty($instance['lineTwo']) ? 'World' : $instance['lineTwo'];
	
		$mimetypes = split(',', empty($instance['mimetypes']) ? 'image' : $instance['mimetypes']);
		$size = empty($instance['size']) ? 'medium' : $instance['size'];


		# Before the widget
		echo $before_widget;

		# The title
		if ( $title )
		echo $before_title . $title . $after_title;

		# Make the Hello World Example widget
//		echo '<div style="text-align:center;padding:10px;">' . $lineOne . '<br />' . $lineTwo . "</div>";
		
		foreach ( $mimetypes as $mimetype ) {
//			print "<h3>$mimetype</h3>";
			echo "<ul class='attachments $mimetype'>";
			is_drawImageGallery($post_id=null, $size=$size, $mimetype=trim($mimetype));
			echo "</ul>";
		}

		# After the widget
		echo $after_widget;
  }

  /**
	* Saves the widgets settings.
	*
	*/
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['mimetypes'] = strip_tags(stripslashes($new_instance['mimetypes']));
		$instance['size'] = strip_tags(stripslashes($new_instance['size']));
	//	$instance['lineTwo'] = strip_tags(stripslashes($new_instance['lineTwo']));

		return $instance;
	}

  /**
	* Creates the edit form for the widget.
	*
	*/
	function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=>'', 'lineOne'=>'Hello', 'lineTwo'=>'World') );

		$title = htmlspecialchars($instance['title']);
		$mimetypes = htmlspecialchars($instance['mimetypes']);
		$size = htmlspecialchars($instance['size']);
		
		print "current size: $size";
	//	$lineTwo = htmlspecialchars($instance['lineTwo']);

		# Output the options
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('title') . '">' . __('Title:') . ' <input style="width: 250px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';
		# Text line 1
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('mimetypes') . '">' . __('Attachment types:') . ' <input style="width: 200px;" id="' . $this->get_field_id('mimetypes') . '" name="' . $this->get_field_name('mimetypes') . '" type="text" value="' . $mimetypes . '" /></label></p>';
		# Text line 2
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('size') . '">' . __('Size:') . ' <input style="width: 200px;" id="' . $this->get_field_id('size') . '" name="' . $this->get_field_name('size') . '" type="text" value="' . $size . '" /></label></p>';
		//echo '<p style="text-align:right;"><label for="size">Size <select name="size" id="size"><option value="thumbnail">Thumbnail</option><option value="medium">Medium</option><option value="large">Large</option></select></label></p>';
		# drop down
//		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('lineTwo') . '">' . __('Line 2 text:') . ' <input style="width: 200px;" id="' . $this->get_field_id('lineTwo') . '" name="' . $this->get_field_name('lineTwo') . '" type="text" value="' . $lineTwo . '" /></label></p>';
  }

}// END class

/**
  * Register Hello World widget.
  *
  * Calls 'widgets_init' action after the Hello World widget has been registered.
  */
?>