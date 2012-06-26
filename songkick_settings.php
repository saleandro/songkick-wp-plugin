<?php

function admin_api_key_error() {
    if ( current_user_can('manage_options' ))
        echo "<div class='error'><p><strong>Your Songkick API key is invalid.</strong> ".sprintf('<a href="%1$s">Go to the settings page to update your key</a>. If you use the default key, you need to upgrade the plugin.', "admin.php?page=songkick-concerts-and-festivals")."</p></div>";
}

function check_api_key() {
    $options = get_option(SONGKICK_OPTIONS);
    $apikey = $options['apikey'];
    try {
        $songkick_events = new SongkickEvents($apikey);
        $songkick_events->test_api_call();
    } catch (InvalidApiKeyException $e) {
        add_action('admin_notices', 'admin_api_key_error');
    }
}

function songkick_admin_settings() {
    $max_number_events = 100;
    $options = get_option(SONGKICK_OPTIONS);
    if (!is_array($options)) {
        $options = array(
            'title'         => '',
            'songkick_id'   => '',
            'songkick_id_type' => 'user',
            'apikey'        => '',
            'attendance'    => 'all',
            'gigography'       => false,
            'hide_if_empty'    => false,
            'show_pagination'  => false,
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
        $options['songkick_id']      = trim(strip_tags(stripslashes($_POST['songkick_id'])));
        $options['songkick_id_type'] = strip_tags(stripslashes($_POST['songkick_id_type']));
        $options['attendance']       = strip_tags(stripslashes($_POST['songkick_attendance']));
        $options['apikey']           = trim(strip_tags(stripslashes($_POST['songkick_apikey'])));

        $options['title']          = strip_tags(stripslashes($_POST['songkick_title']));
        $options['hide_if_empty']  = (isset($_POST['songkick_hide_if_empty']) && $_POST['songkick_hide_if_empty'] === 'on');
        $options['gigography']     = (isset($_POST['songkick_gigography']) && $_POST['songkick_gigography'] === 'on');
        $options['logo']           = strip_tags(stripslashes($_POST['songkick_logo']));
        $options['date_color']     = strip_tags(stripslashes($_POST['songkick_date_color']));
        $limit = (int)$_POST['songkick_number_of_events'];
        if ($limit > $max_number_events) $limit = $max_number_events;
        $options['number_of_events'] = $limit;

        $options['show_pagination']          = (isset($_POST['songkick_show_pagination']) && $_POST['songkick_show_pagination'] === 'on');
        $options['shortcode_logo']           = strip_tags(stripslashes($_POST['shortcode_songkick_logo']));
        $options['shortcode_date_color']     = strip_tags(stripslashes($_POST['shortcode_songkick_date_color']));
        $limit = (int)$_POST['songkick_shortcode_number_of_events'];
        if ($limit > $max_number_events) $limit = $max_number_events;
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
    $gigography       = ($options['gigography'])      ? 'checked="checked"' : '';
    $hide_if_empty    = ($options['hide_if_empty'])   ? 'checked="checked"' : '';
    $show_pagination  = ($options['show_pagination']) ? 'checked="checked"' : '';
    $songkick_logo    = htmlspecialchars($options['logo'], ENT_QUOTES);
    $date_color       = htmlspecialchars($options['date_color'], ENT_QUOTES);
    $number_of_events = htmlspecialchars($options['number_of_events']);

    $shortcode_songkick_logo    = htmlspecialchars($options['shortcode_logo'], ENT_QUOTES);
    $shortcode_date_color       = htmlspecialchars($options['shortcode_date_color'], ENT_QUOTES);
    $shortcode_number_of_events = htmlspecialchars($options['shortcode_number_of_events']);

    echo '<div class="wrap" id="songkick_concerts_and_festivals_settings">
             <div id="icon-options-general" class="icon32"></div>
             <h2>Songkick Concerts and Festivals Settings</h2>';

    echo '<p class="description">Add [songkick_concerts_and_festivals] anywhere in a content to get your list of events. You can also add the Songkick widget to your template.</p>';
    echo '<p class="description">For more information, <a href="http://wordpress.org/extend/plugins/songkick-concerts-and-festivals/">check out the plugin’s page</a>.</p>';

    echo '<form method="post">';
    echo '<h3>Default settings</h3>';

    echo '<table class="form-table">';
    echo '<tr><th><label for="songkick_apikey">' . 'Songkick API Key' . '</label></th>';
    echo '<td><input id="songkick_apikey" name="songkick_apikey" type="text" value="'.$apikey.'" placeholder="Use default key" />';
    echo '<span class="description">Required';
    echo '<br>Please read through <a href="http://www.songkick.com/developer/api-terms-of-use">Songkick’s API terms of use</a>.';
    echo '<br>The default key is non-commercial. If you have a commercial website, <a href="http://developer.songkick.com">request another key from Songkick</a>. </span>';
    echo '</td></tr>';

    echo '<tr><th><label for="songkick_id_type">' . 'Songkick ID' . '</label></th>';
    echo '<td><select id="songkick_id_type" name="songkick_id_type">';
    echo '    <option value="user" '.(($songkick_id_type == 'user') ? ' selected' : '').'>username</option>';
    echo '    <option value="artist" '.(($songkick_id_type == 'artist') ? ' selected' : '').'>artist id</option>';
    echo '    <option value="venue" '.(($songkick_id_type == 'venue') ? ' selected' : '').'>venue id</option>';
    echo '    <option value="metro_area" '.(($songkick_id_type == 'metro_area') ? ' selected' : '').'>metro area id</option>';
    echo '  </select>';
    echo '  <input size="15" id="songkick_id" name="songkick_id" type="text" value="'.$songkick_id.'" />';
    echo '</td></tr>';

    echo '<tr><th><label for="songkick_attendance">' . 'Attendance' . '</label></th>';
    echo '<td><select id="songkick_attendance" name="songkick_attendance">';
    echo '    <option value="all" '.(($attendance == 'all') ? ' selected' : '').'>all</option>';
    echo '    <option value="im_going" '.(($attendance == 'im_going') ? ' selected' : '').'>I’m going</option>';
    echo '    <option value="i_might_go" '.(($attendance == 'i_might_go') ? ' selected' : '').'>I might go</option>';
    echo '  </select>';
    echo '<span class="description">For users only</span>';
    echo '</td></tr>';

    echo '<tr><th><label for="songkick_gigography">Show past events (gigography)?</label></th>';
    echo '<td><input id="songkick_gigography" name="songkick_gigography" type="checkbox" '.$gigography.' /> ';
    echo '<span class="description">For users and artists only</span>';
    echo '</td></tr>';

    echo '<tr><th><label for="songkick_number_of_events">Number of events to show</label></th>';
    echo '<td><input id="songkick_number_of_events" name="songkick_number_of_events" type="text" value="'.$number_of_events.'" /> ';
    echo '<span class="description"> Max. 100</span>';
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

    echo '<tr><th><label for="songkick_title">' . 'Widget title:' . '</label></th>';
    echo '<td><input id="songkick_title" name="songkick_title" type="text" value="'.$title.'" />';
    echo '</td></tr>';

    echo '</table>';

    echo '<br><h3>Default shortcode settings</h3>';
    echo '<table class="form-table">';

    echo '<tr><td colspan="2">You can specify different user, artist, venue, or metro area ids when using the shortcode function. ';
    echo ' <br>For users:&nbsp;&nbsp;<code>[songkick_concerts_and_festivals songkick_id=your_username &nbsp;songkick_id_type=user]</code>';
    echo ' <br>For artists: <code>[songkick_concerts_and_festivals songkick_id=your_artist_id songkick_id_type=artist]</code>';
    echo ' <br>For venues: <code>[songkick_concerts_and_festivals songkick_id=your_venue_id songkick_id_type=venue]</code>';
    echo ' <br>For metro areas: <code>[songkick_concerts_and_festivals songkick_id=your_metro_area_id songkick_id_type=metro_area]</code>';
    echo '</td></tr>';

    echo '<tr><th><label for="songkick_shortcode_number_of_events">Number of events to show</label></th>';
    echo '<td><input id="songkick_shortcode_number_of_events" name="songkick_shortcode_number_of_events" type="text" value="'.$shortcode_number_of_events.'" /> ';
    echo '<span class="description"> Max. 100</span>';
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
    echo '<tr><th><label for="songkick_show_pagination">Show pagination?</label></th>';
    echo '<td><input id="songkick_show_pagination" name="songkick_show_pagination" type="checkbox" '.$show_pagination.' /> ';
    echo '</td></tr>';

    echo ' <tr><td colspan="2">Override shortcode settings: ';
    echo ' <ul><li><code>gigography=true|false</code></li><li><code>number_of_events=integer</code></li><li><code>show_pagination=true|false</code></li></ul></td></tr>';

    echo '</table>';

    echo '<p class="submit"><input type="submit" class="button-primary" name="songkick_submit" value="Save Changes" /></p>';
    echo '</form></div>';
}

add_action('admin_init', 'check_api_key');


?>