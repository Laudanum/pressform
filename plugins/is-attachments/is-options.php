<?php

/**
 * @package is-attachments
 * @author Mr Snow
 * @version 0.8
 */

	global $options;
	$options = get_option('is_attachments_options');

	function do_is_attachments_options() {
//		print_r($_POST);
		
		if ( !empty($_POST['submit'] ) ) {
//			print "updating:";
//			print_r($_POST['is_attachments_options']);
			
			if ( $_POST['is_attachments_options']['parent_menu'] == on )
				$_POST['is_attachments_options']['parent_menu'] = 1;
			else
				$_POST['is_attachments_options']['parent_menu'] = 0;
				
			if ( $_POST['is_attachments_options']['parent_menu_attachments'] == on )
				$_POST['is_attachments_options']['parent_menu_attachments'] = 1;
			else
				$_POST['is_attachments_options']['parent_menu_attachments'] = 0;
			
			update_option('is_attachments_options', $_POST['is_attachments_options']);
		}
		$options = get_option('is_attachments_options');

?>

	<div class="wrap">

<?php if ( !empty($_POST['submit'] ) ) { 
	
?>

<div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>

<?php } ?>
		<h2><?php _e('isEngine Attachments Configuration'); ?></h2>

		<?php
//			print_r($options);
		?>
			<div class="narrow">
<!-- 				<form action="options.php" method="post"> -->
				<form action="" method="post">

<?php settings_fields('is_attachments'); ?>

		<?php
//			print_r($options);
		?>

				<h3>Attachment editor</h3>
				
					<ul>

						<li>
							<h4>Attachment parents</h4>
							<input type='checkbox' name='is_attachments_options[parent_menu]' <? if ( $options['parent_menu'] ) echo 'checked'; ?> /> <label for='parent_menu'>Show parent menu</label>
							<ul>
								<li>
									<input type='checkbox' name='is_attachments_options[parent_menu_attachments]' <? if ( $options['parent_menu_attachments'] ) echo 'checked'; ?>/> <label for='parent_menu_attachments'>Allow parent menu to show other attachments</label>
								</li>
							</ul>
						</li>

						<li>
							<h4>Attachment roles</h4>
				
							These checkboxes add features to particular attachments by way of metadata.
							<ul>
								<li>
									<input name='is_attachments_options[roles][]' value='<?=$options["roles"][0]?>' />
								</li>
								<li>
									<input name='is_attachments_options[roles][]' value='<?=$options["roles"][1]?>' />
								</li>
								<li>
									<input name='is_attachments_options[roles][]' value='<?=$options["roles"][2]?>' />
								</li>
							</ul>
						</li>
						
						<li>
							<h4>Social video</h4>
							
							<ul>
								<li><?php 
								if ( class_exists("ProPlayer")) {
									echo "ProPlayer installed - VIMEO support available<br /><pre>-";
									
									

								}?></li>
							</ul>

									
					</ul>				
				
				
					<ul>
					<li>
					allow bulk reparenting
					</li>
					</ul>
					
				
				
					<input type='submit' name='submit' value='Save options' />

				</form>
			</div> <!-- .narrow -->
		</div> <!-- .wrap -->
<?php		
	}
	
	
?>