<?php
/**
 * @package WordPress
 * @subpackage press
 */
?>

	<hr />
	<div class="push"></div>
</div>

<div id="footer" role="contentinfo">
	<ol>
		<li><strong>Pressform Engineering PTY LTD</strong> : 
		23 Jackson Street (cnr Alice St), Bassendean 6054 Western Australia
		</li>
		<li><strong>Phone:</strong> +61 8 9279 8855</li>
		<li><strong>Fax:</strong> +61 8 9279 9929</li>
		<?php
		$page = get_page_by_title('Contacts');
		$link = get_page_link($page->ID);
		?>
		<li><a href="<?php print $link; ?>">Contacts</a></li>
	</ol>
	<ol class="text">
		<?php
		$page = get_page_by_title('Contact');
		$subscribelink = get_page_link($page->ID);
		?>
		<li>Contact us for an obligation free quote <a href="<?php print $subscribelink; ?>">here</a>, or <a href='mailto:info@pressform.com.au'>info@pressform.com.au</a>, or 08 9279 8855</li>
	</ol>
</div>

<?php /* "Just what do you think you're doing Dave?" */ ?>

		<?php wp_footer(); ?>
</body>
</html>
