<div class="wrap">
	<form method="post" name="add_to_header_form">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>WP Paging</h2>
		
		<p>Add &lt;?php wp_paging(); ?&gt; after <a href="http://codex.wordpress.org/The_Loop">the loop</a> or between the <em>endwhile</em> and <em>else</em> in your theme.
		
		<h3>Live preview</h3>
		<p>The output when page numbers are needed.</p>
		
		<table class="widefat">
			<thead>
				<tr>
					<th colspan="2">Try it out!</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					
					<td><?php wp_paging('total=20'); ?></td>
					<td style="background: #333;"><?php wp_paging('total=20'); ?></td>
				</tr>
			</tbody>
		</table>
		
		<h3>Paging texts</h3>
		<p>Text on the page navigator.</p>
		
		<table class="widefat">
			<thead>
				<tr>
					<th>Title</th>
					<th>Value</th>
					<th>Description</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						Page ? of ?
					</td>
					<td>
						<input type="checkbox" value="true" name="page_of" <?php self::checked_html($options, 'page_of', true); ?> />
						<input type="text" value="<?php self::input_html($options, 'page_text', 'Page'); ?>" name="page_text" />
						<input type="text" value="<?php self::input_html($options, 'of_text', 'of'); ?>" name="of_text" />
						<?php echo wp_paging::select_from_array($options_types['page_of_position'], 'page_of_position', $options['page_of_position']); ?>
					</td>
					<td>
						Translate [page] and [of] to your language.
					</td>
				</tr>
				<tr>
					<td>Prev and next</td>
					<td>
						<input type="checkbox" value="true" name="prev_next" <?php self::checked_html($options, 'prev_next', true); ?> />
						<input type="text" value="<?php self::input_html($options, 'prev_text', 'Prev'); ?>" name="prev_text" />
						<input type="text" value="<?php self::input_html($options, 'next_text', 'Next'); ?>" name="next_text" />
					</td>
					<td>
						Translate [prev] and [next] to your language.
					</td>
				</tr>
				<tr>
					<td>Separator(s)</td>
					<td>
						<input type="text" value="<?php self::input_html($options, 'separator_text', '...'); ?>" name="separator_text" />
					</td>
					<td>
						Translate [...] to your language.
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<input style="float: right;" type="submit" name="submitter" value="<?php _e('Save Changes') ?>" class="button-primary" />
					</td>
				</tr>
			</tbody>
		</table>
		
		<h3>Paging functions</h3>
		<p>How to present the page numbers.</p>
		<table class="widefat">
			<thead>
				<tr>
					<th>Title</th>
					<th>Value</th>
					<th>Description</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Show&nbsp;all&nbsp;pages</td>
					<td><input type="checkbox" value="true" name="show_all" <?php self::checked_html($options, 'show_all', false); ?> /></td>
					<td>
						If the box is checked, it will show all of the pages instead of just a short list of the pages near the current page.<br />
						Default is '<strong>false</strong>' (checked).
					</td>
				</tr>

				<tr>
					<td>Start&nbsp;/&nbsp;end&nbsp;size</td>
					<td><input type="text" value="<?php self::input_html($options, 'end_size', 1); ?>" name="end_size" /></td>
					<td>
						Shows how many numbers there are on each side of the list edges.<br />
						Default is '<strong>1</strong>'.
					</td>
				</tr>
				<tr>
					<td>Middle&nbsp;size</td>
					<td><input type="text" value="<?php self::input_html($options, 'mid_size', 2); ?>" name="mid_size" /></td>
					<td>
						Shows how many numbers there are on each side of the current page.<br />
						Default is <strong>'2'</strong>.
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<input style="float: right;" type="submit" name="submitter" value="<?php _e('Save Changes') ?>" class="button-primary" />
					</td>
				</tr>
			</tbody>
		</table>
		
		<h3>Paging styles</h3>
		<p>Colors and styles.</p>
		<table class="widefat">
			<thead>
				<tr>
					<th>Title</th>
					<th>Value</th>
					<th>Description</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Enable styles</td>
					<td><input type="checkbox" value="true" name="enable_styles" <?php self::checked_html($options, 'enable_styles', true); ?> /></td>
					<td>
						Enable CSS style to be included. It's reqired to use the settings of this table.<br />
						Default is '<strong>true</strong>' (checked).
					</td>
				</tr>
				<tr>
					<td>Enable gradients</td>
					<td><input type="checkbox" value="true" name="enable_gradients" <?php self::checked_html($options, 'enable_gradients', true); ?> /></td>
					<td>
						Gradients are currently only supported by: <em>Safari</em> and <em>Chrome</em>.<br />
						Default is '<strong>true</strong>' (checked).
					</td>
				</tr>
				<tr>
					<td>Enable round corners</td>
					<td><input type="checkbox" value="true" name="enable_rounded" <?php self::checked_html($options, 'enable_rounded', true); ?> /></td>
					<td>
						Round corners are currently only supported by: <em>Firefox</em>, <em>Safari</em> and <em>Chrome</em>.<br />
						Default is '<strong>true</strong>' (checked).
					</td>
				</tr>
				<tr>
					<td>Page background</td>
					<td><?php echo wp_paging::select_from_array($options_types['page_bkg'], 'page_bkg', $options['page_bkg']); ?></td>
					<td>
						The background color on the page numbers when they are inactive.<br />
						Default is '<strong>Bright</strong>'.
					</td>
				</tr>
				<tr>
					<td>Current page background</td>
					<td><?php echo wp_paging::select_from_array($options_types['current_page_bkg'], 'current_page_bkg', $options['current_page_bkg']); ?></td>
					<td>
						The background color on the page numbers when they are active.<br />
						Default is '<strong>Cyan</strong>'.
					</td>
				</tr>
				<tr>
					<td>Behind text color</td>
					<td><?php echo wp_paging::select_from_array($options_types['behind_color'], 'behind_color', $options['behind_color']); ?></td>
					<td>
						The text color placed outside the page numbers.<br />
						Default is '<strong>Dark</strong>'.
					</td>
				</tr>
				
				<tr>
					<td>Alignment</td>
					<td><?php echo wp_paging::select_from_array($options_types['alignment'], 'alignment', $options['alignment']); ?></td>
					<td>
						The alignment of the page numbers.<br />
						Default is '<strong>Left</strong>'.
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<input style="float: right;" type="submit" name="submitter" value="<?php _e('Save Changes') ?>" class="button-primary" />
					</td>
				</tr>
			</tbody>
		</table>
		
		<br />
		
		<table class="widefat">
			<thead>
				<tr>
					<th colspan="2">Delete settings</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>If you need to uninstall WP Paging you can delete the settings saved into the database.</td>
					<td><input type="submit" name="deleter" value="<?php _e('Delete') ?>" class="button-secondary" onclick="return confirm('This will delete all your settings! Are you sure?')" style="float: right;" /></td>
				</tr>
			</tbody>
		</table>
		
		<h3>Information - Advanced</h3>
		<p>For more advanced usage.</p>
		
		<table class="widefat">
			<thead>
				<tr>
					<th>Styles and CSS</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<p>To create an own style, uncheck "<em>Enable styles</em>" (above) and add your own CSS to your styles.css into your themes folder.</p>
						<h3>CSS Classes</h3>
						<ul>
							<li>
								&lt;ul class="page-numbers"&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a class="page-numbers" href="[url]"&gt;[page]&lt;/a&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;span class="page-numbers current"&gt;[current page]&lt;/span&gt;&lt;/li&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;span class="page-numbers dots"&gt;[separator]&lt;/span&gt;&lt;/li&gt;<br />
&lt;/ul&gt;
						<h3>Additional CSS classes</h3>
						<p>Some extra classes are added to the UL element to change its style, but only if "Enable styles" is checked.</p>
					</td>
				</tr>
			</tbody>
		</table>
		<br />
		<table class="widefat">
			<thead>
				<tr>
					<th>Arguments</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<p>You can override the default values as well as the settings, by adding arguments with the function, when adding it to your theme.</p>
						<h3>wp_paging($args)</h3>
						
						$args = array(<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'base' => add_query_arg( 'paged', '%#%' ),<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'total' => $wp_query->max_num_pages,<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'current' => $page,<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'prev_next' => false,<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'prev_text' => 'Prev',<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'next_text' => 'Next',<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'end_size' => 1,<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'mid_size' => 2,<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'show_all' => false,<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'type' => 'list',<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'behind_color' => 'dark',<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'page_bkg' => 'bright',<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'current_page_bkg' => 'cyan',<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'enable_rounded' => true,<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'alignment' => 'left',<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'enable_gradients' => true,<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'separator_text' => '...'<br />
						);<br />
						
						<h3>Example</h3>
						<p>&lt;?php echo <strong>wp_paging('show_all=true&amp;page_bkg=dark');</strong> ?&gt;</p>
						<h3>More information</h3>
						<p>Read more about <a href="">WP Paging</a>.
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>