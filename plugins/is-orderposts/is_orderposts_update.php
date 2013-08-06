<?php
	@require_once('../../../wp-config.php');
	@require_once('../../../wp-admin/admin.php');

	if ( ! isset ($_POST['container']) ) {
//		print "ERROR: No container received.";
//		exit;

		foreach ( $_POST as $post_id => $menu_order ) {
			if ( is_int($post_id) ) {
				$query = "UPDATE $wpdb->posts SET menu_order = $menu_order WHERE ID = $post_id";
				$wpdb->query($query);
				print $menu_order;
			}
		}
		exit;
	} else {

		$step = 10;
		$i = $step;
		$msg = "";
	
		foreach ( $_POST[$_POST['container']] as $post_id ) {
			$query = "UPDATE $wpdb->posts SET menu_order = $i WHERE ID = $post_id";
			$wpdb->query($query);
//		$msg .= "\n$query";
			$msg .= "$post_id=$i&";
			$i += $step;
		}
	
		print "$msg";
	}
?>