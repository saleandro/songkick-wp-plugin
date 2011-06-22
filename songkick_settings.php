<?php

function songkick_widget_settings() {
	echo '<a href="options-general.php?page=songkick-concerts-and-festivals">Please go to the plugin\'s settings page.</a>';
}

function songkick_admin_settings() {
	$options = get_option(SONGKICK_OPTIONS);
		if (!is_array($options)) {
		$options = array(
			'title'         => '',
			'songkick_id'   => '',
			'songkick_id_type' => 'user',
			'apikey'        => '',
			'attendance'    => 'all',
			'hide_if_empty' => false,
			'number_of_events' => 10,
			'logo'          => 'songkick-logo.png',
			'date_color'    => '#303030',
		    'shortcode_number_of_events' => 50,
		    'shortcode_logo'          => 'songkick-logo.png',
    		'shortcode_date_color'    => '#303030'
		);
	}

	if (current_user_can('manage_options') && isset($_POST['songkick_submit']) && $_POST['songkick_submit']) {
		$options['username']         = null;
		$options['songkick_id']      = strip_tags(stripslashes($_POST['songkick_id']));
		$options['songkick_id_type'] = strip_tags(stripslashes($_POST['songkick_id_type']));
		$options['attendance']       = strip_tags(stripslashes($_POST['songkick_attendance']));
		$options['apikey']           = strip_tags(stripslashes($_POST['songkick_apikey']));

		$options['title']          = strip_tags(stripslashes($_POST['songkick_title']));
		$options['hide_if_empty']  = ($_POST['songkick_hide_if_empty'] === 'on');
		$options['logo']           = strip_tags(stripslashes($_POST['songkick_logo']));
		$options['date_color']     = strip_tags(stripslashes($_POST['songkick_date_color']));
		$limit = (int)$_POST['songkick_number_of_events'];
		if ($limit > 50) $limit = 50;
		$options['number_of_events'] = $limit;

		$options['shortcode_logo']           = strip_tags(stripslashes($_POST['shortcode_songkick_logo']));
		$options['shortcode_date_color']     = strip_tags(stripslashes($_POST['shortcode_songkick_date_color']));
		$limit = (int)$_POST['songkick_shortcode_number_of_events'];
		if ($limit > 50) $limit = 50;
		$options['shortcode_number_of_events'] = $limit;

		update_option(SONGKICK_CACHE,   null);
		update_option(SONGKICK_OPTIONS, $options);
	}

	if ($options['username']) {
		$songkick_id_type = 'user';
		$songkick_id      = htmlspecialchars($options['username'], ENT_QUOTES);
	} else {
		$songkick_id_type = htmlspecialchars($options['songkick_id_type'], ENT_QUOTES);
		$songkick_id      = htmlspecialchars($options['songkick_id'], ENT_QUOTES);
	}
	$title            = htmlspecialchars($options['title'], ENT_QUOTES);
	$apikey           = htmlspecialchars($options['apikey'], ENT_QUOTES);

	$attendance       = htmlspecialchars($options['attendance']);
	$hide_if_empty    = ($options['hide_if_empty']) ? 'checked="checked"' : '';
	$songkick_logo    = htmlspecialchars($options['logo'], ENT_QUOTES);
	$date_color       = htmlspecialchars($options['date_color'], ENT_QUOTES);
	$number_of_events = htmlspecialchars($options['number_of_events']);

	$shortcode_songkick_logo    = htmlspecialchars($options['shortcode_logo'], ENT_QUOTES);
	$shortcode_date_color       = htmlspecialchars($options['shortcode_date_color'], ENT_QUOTES);
	$shortcode_number_of_events = htmlspecialchars($options['shortcode_number_of_events']);

	echo '<div class="wrap" id="songkick_concerts_and_festivals_settings">
		     <div id="icon-options-general" class="icon32"></div>
   		     <h2>Songkick Concerts and Festivals Settings</h2>';

	echo '<p class="description">Add [songkick_concerts_and_festivals] anywhere in a content to get your list of events.</p>';
	echo '<p class="description">You can also add the Songkick widget to your template.</p>';

	echo '<form method="post">';
	echo '<h3>Main settings</h3>';

	echo '<table class="form-table">';
	echo '<tr><th><label for="songkick_apikey">' . 'Songkick API Key' . '</label></th>';
	echo '<td><input id="songkick_apikey" name="songkick_apikey" type="text" value="'.$apikey.'" />';
	echo '<span class="description">Required. <a href="http://developer.songkick.com">Request one from Songkick</a></span>';
	echo '</td></tr>';

	echo '<tr><th><label for="songkick_id_type">' . 'Songkick ID' . '</label></th>';
	echo '<td><select id="songkick_id_type" name="songkick_id_type">';
	echo '    <option value="user" '.(($songkick_id_type == 'user') ? ' selected' : '').'>username</option>';
	echo '    <option value="artist" '.(($songkick_id_type == 'artist') ? ' selected' : '').'>artist id</option>';
	echo '  </select>';
	echo '  <input size="15" id="songkick_id" name="songkick_id" type="text" value="'.$songkick_id.'" />';
	echo '<span class="description">Required. Either a username or an artist id.</span>';
	echo '</td></tr>';

	echo '<tr><th><label for="songkick_attendance">' . 'Attendance' . '</label></th>';
	echo '<td><select id="songkick_attendance" name="songkick_attendance">';
	echo '    <option value="all" '.(($attendance == 'all') ? ' selected' : '').'>all</option>';
	echo '    <option value="im_going" '.(($attendance == 'im_going') ? ' selected' : '').'>I’m going</option>';
	echo '    <option value="i_might_go" '.(($attendance == 'i_might_go') ? ' selected' : '').'>I might go</option>';
	echo '  </select>';
	echo '<span class="description">For users only.</span>';
	echo '</td></tr>';

	echo '<tr><td colspan="2">You can also specify different user and artist ids when using the shortcode function. ';
	echo ' <br>For users:&nbsp;&nbsp;<code>[songkick_concerts_and_festivals songkick_id=your_username &nbsp;songkick_id_type=user]</code>';
	echo ' <br>For artists: <code>[songkick_concerts_and_festivals songkick_id=your_artist_id songkick_id_type=artist]</code>';
	echo '</td></tr>';

	echo '</table>';

	echo '<br><h3>Shortcode settings</h3>';
	echo '<table class="form-table">';
	echo '<tr><th><label for="songkick_shortcode_number_of_events">Number of events to show</label></th>';
	echo '<td><input id="songkick_shortcode_number_of_events" name="songkick_shortcode_number_of_events" type="text" value="'.$shortcode_number_of_events.'" /> ';
	echo '<span class="description"> Max. 50</span>';
	echo '</td></tr>';
	echo '<tr><th><label for="shortcode_songkick_logo">' . 'Songkick logo' . '</label></th>';
	echo '<td><select id="shortcode_songkick_logo" name="shortcode_songkick_logo">';
	echo '    <option value="songkick-logo.png" '.(($shortcode_songkick_logo == 'songkick-logo.png') ? ' selected' : '').'>' .
					'white background' . '</option>';
	echo '    <option value="songkick-logo-black.png" '.(($shortcode_songkick_logo == 'songkick-logo-black.png') ? ' selected' : '').'>' .
					'black background' . '</option>';
	echo '  </select>';
	echo '</td></tr>';
	echo '<tr><th><label for="shortcode_songkick_date_color">' . 'Background color for date:' . '</label></th>';
	echo '<td><input id="shortcode_songkick_date_color" name="shortcode_songkick_date_color" type="text" value="'.$shortcode_date_color.'" />';
	echo '</td></tr>';
	echo '</table>';

	echo '<br><h3>Widget settings</h3>';

	echo '<table class="form-table">';
	echo '<tr><th><label for="songkick_title">' . 'Title:' . '</label></th>';
	echo '<td><input id="songkick_title" name="songkick_title" type="text" value="'.$title.'" />';
	echo '</td></tr>';

	echo '<tr><th><label for="songkick_number_of_events">Number of events to show</label></th>';
	echo '<td><input id="songkick_number_of_events" name="songkick_number_of_events" type="text" value="'.$number_of_events.'" /> ';
	echo '<span class="description"> Max. 50</span>';
	echo '</td></tr>';

	echo '<tr><th><label for="songkick_hide_if_empty">Hide if there are no events?</label></th>';
	echo '<td><input id="songkick_hide_if_empty" name="songkick_hide_if_empty" type="checkbox" '.$hide_if_empty.' /> ';
	echo '</td></tr>';

	echo '<tr><th><label for="songkick_logo">' . 'Songkick logo' . '</label></th>';
	echo '<td><select id="songkick_logo" name="songkick_logo">';
	echo '    <option value="songkick-logo.png" '.(($songkick_logo == 'songkick-logo.png') ? ' selected' : '').'>' .
					'white background' . '</option>';
	echo '    <option value="songkick-logo-black.png" '.(($songkick_logo == 'songkick-logo-black.png') ? ' selected' : '').'>' .
					'black background' . '</option>';
	echo '  </select>';
	echo '</td></tr>';

	echo '<tr><th><label for="songkick_date_color">' . 'Background color for date:' . '</label></th>';
	echo '<td><input id="songkick_date_color" name="songkick_date_color" type="text" value="'.$date_color.'" />';
	echo '</td></tr>';

	echo '</table>';

	echo '<p class="submit"><input type="submit" class="button-primary" name="songkick_submit" value="Save Changes" /></p>';
	echo '</form></div>';
}

?>