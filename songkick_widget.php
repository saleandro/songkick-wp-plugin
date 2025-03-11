<?php

add_action('widgets_init', function( ) { return register_widget( "SongkickConcertsWidget" ); } );

class SongkickConcertsWidget extends WP_Widget {

    protected $widget = array(
        'description' => 'Display events for a Songkick user, artist, venue, or metro area.',
        'name' => 'Songkick',
        'id'   => 'songkick-concerts-widget',
        'fields' => array(
            array(
                'name' => 'Title',
                'id'   => 'title',
                'type' => 'text'
            ),
            array(
                'name' => 'Songkick ID',
                'id'   => 'songkick_id_type',
                'type' => 'select',
                'options' => array( 'user' => 'username', 'artist' => 'artist id', 'venue' => 'venue id', 'metro_area' => 'metro area id')
            ),
            array(
                'name' => '',
                'id' => 'songkick_id',
                'type' => 'text',
                'desc' => ' Required'
            ),
            array(
                'name' => 'Attendance',
                'id'   => 'attendance',
                'desc' => 'For users only',
                'type' => 'select',
                'options' => array( 'all' => 'all', 'im_going' => 'I’m going', 'i_might_go' => 'I might go')
            ),
            array(
                'name' => 'Show past events (gigography)?',
                'id'   => 'gigography',
                'desc' => 'For users and artists only',
                'type' => 'checkbox'
            ),
            array(
                'name' => 'Number of events to show',
                'id'   => 'number_of_events',
                'desc' => 'Max. 100',
                'type' => 'text'
            ),
            array(
                'name' => 'Hide if empty',
                'id'   => 'hide_if_empty',
                'type' => 'checkbox'
            ),
            array(
                'name' => 'Background color for date',
                'id'   => 'date_color',
                'desc' => 'CSS color value',
                'type' => 'text'
            ),
            array(
                'name' => 'Songkick logo',
                'id'   => 'logo',
                'type' => 'select',
                'options' => array( 'songkick-logo.png' => 'white background', 'songkick-logo-black.png' => 'black background')
            ),
            array(
                'std'  => '<p>For more options and a disclaimer, check our <a href="options-general.php?page=songkick-concerts-and-festivals">Settings page.</a></p>',
                'type' => 'custom'
            ),

        )
    );

    function __construct() {
        parent::__construct( $id = $this->widget['id'],
                             $name = $this->widget['name'],
                             $options = array( 'description' => $this->widget['description']) );
    }

    function form($instance) {
        if (empty($this->widget['fields'])) return false;

        $key = 'widget-' . $this->widget['id'];
        
        $form_submitted = (isset($_POST[$key]) && isset($_POST[$key][$_POST['widget_number']]));
        if ($form_submitted) {
            if (!current_user_can('manage_options')) {
                wp_die("No permission to view this page");
            }
            check_admin_referer('songkick_nonce');

            $this->update($_POST[$key][$_POST['widget_number']], $instance);
        }

        echo wp_kses_post(wp_nonce_field('songkick_nonce'));
        
        foreach ($this->widget['fields'] as $field) {
            $meta = false;
            if (isset($field['id']) && array_key_exists($field['id'], $instance))
                @$meta = esc_attr($instance[$field['id']]);

            if ($field['type'] != 'custom' && $field['type'] != 'metabox') {
                echo '<p><label for="', esc_attr($this->get_field_id($field['id'])),'">';
            }
            if (isset($field['name']) && $field['name']) echo esc_html($field['name']),':';

            switch ($field['type']) {
                case 'text':
                    echo '<input type="text" name="', esc_attr($this->get_field_name($field['id'])), '" id="', esc_attr($this->get_field_id($field['id'])), '" value="', esc_attr(($meta ? $meta : @$field['std'])), '" class="vibe_text" />',
                    '<br/><span class="description">', esc_html(@$field['desc']), '</span>';
                    break;
                case 'textarea':
                    echo '<textarea class="vibe_textarea" name="', esc_attr($this->get_field_name($field['id'])), '" id="', esc_attr($this->get_field_id($field['id'])), '" cols="60" rows="4" style="width:97%">', esc_html(($meta ? $meta : @$field['std'])), '</textarea>',
                    '<br/><span class="description">', esc_textarea(@$field['desc']), '</span>';
                    break;
                case 'select':
                    echo '<select class="vibe_select" name="', esc_attr($this->get_field_name($field['id'])), '" id="', esc_attr($this->get_field_id($field['id'])), '">';

                    foreach ($field['options'] as $value => $option) {
                        $selected_option = ( $value ) ? $value : $option;
                        echo '<option', ($value ? ' value="' . esc_attr($value) . '"' : ''), ($meta == $selected_option ? ' selected="selected"' : ''), '>', esc_html($option), '</option>';
                    }

                    echo '</select>',
                    '<br/><span class="description">', esc_html(@$field['desc']), '</span>';
                    break;
                case 'radio':
                    foreach ($field['options'] as $option) {
                        echo '<input class="vibe_radio" type="radio" name="', esc_attr($this->get_field_name($field['id'])), '" value="', esc_attr($option['value']), '"', ($meta == $option['value'] ? ' checked="checked"' : ''), ' />',
                        esc_html($option['name']);
                    }
                    echo '<br/><span class="description">', esc_html(@$field['desc']), '</span>';
                    break;
                case 'checkbox':
                    echo '<input type="hidden" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '" /> ',
                         '<input class="vibe_checkbox" type="checkbox" name="', esc_attr($this->get_field_name($field['id'])), '" id="', esc_attr($this->get_field_id($field['id'])), '"', $meta ? ' checked="checked"' : '', ' /> ',
                    '<br/><span class="description">', esc_html(@$field['desc']), '</span>';
                    break;
                case 'custom':
                    echo wp_kses_post($field['std']);
                    break;
            }

            if ($field['type'] != 'custom' && $field['type'] != 'metabox')  {
                echo '</label></p>';
            }
        }
        return true;
    }

    function update($new_instance, $old_instance) {
        $instance = wp_parse_args($new_instance, $old_instance);

        $instance['songkick_id']      = trim(wp_strip_all_tags(stripslashes($instance['songkick_id'])));
        $instance['songkick_id_type'] = trim(wp_strip_all_tags(stripslashes($instance['songkick_id_type'])));
        $instance['attendance']       = wp_strip_all_tags(stripslashes($instance['attendance']));

        $instance['title']            = wp_strip_all_tags(stripslashes($instance['title']));
        $instance['hide_if_empty']  = ($instance['hide_if_empty'] === 'on');
        $instance['gigography']     = ($instance['gigography'] === 'on');
        $instance['logo']           = wp_strip_all_tags(stripslashes($instance['logo']));
        $instance['date_color']     = wp_strip_all_tags(stripslashes($instance['date_color']));
        $instance['show_pagination'] = false; # TODO: needs styling for widget

        $max_number_events = 100;
        $limit             = (int)$instance['number_of_events'];
        if ($limit > $max_number_events) $limit = $max_number_events;
        $instance['number_of_events'] = $limit;

        update_option(SONGKICK_CACHE, null);

        return $instance;
    }

    function widget($args, $instance) {
        extract($args);
        $this->widget['number'] = $this->number;

        $default_options = get_option(SONGKICK_OPTIONS);
        if ($default_options)
            $instance = array_merge($default_options, $instance);
        if (!$instance['songkick_id'] or $instance['songkick_id'] == '')
            $instance['songkick_id'] = $default_options['songkick_id'];
        if (!$instance['songkick_id_type'] or $instance['songkick_id_type'] == '')
            $instance['songkick_id_type'] = $default_options['songkick_id_type'];
        if (!$instance['title'] or $instance['title'] == '')
            $instance['title'] = $default_options['title'];
        if (!$instance['attendance'])
            $instance['attendance'] = $default_options['attendance'];
        if (!$instance['hide_if_empty'])
            $instance['hide_if_empty'] = $default_options['hide_if_empty'];
        if (!$instance['gigography'])
            $instance['gigography'] = $default_options['gigography'];
        if (!$instance['logo'])
            $instance['logo'] = $default_options['logo'];
        if (!$instance['date_color'])
            $instance['date_color'] = $default_options['date_color'];
        if (!$instance['number_of_events'])
            $instance['number_of_events'] = $default_options['number_of_events'];

        $instance['apikey'] = $default_options['apikey'];
        try {
            $hide_if_empty = $instance['hide_if_empty'];
            $instance['show_pagination'] = false;

            $sk = new SongkickPresentableEvents($instance);

            if ($hide_if_empty && $sk->no_events()) {
                echo '';
            } else {
                wp_enqueue_style('songkick_concerts', '/wp-content/plugins/songkick-concerts-and-festivals/songkick_concerts.css', array(), '1.0',) ;

                $title = $instance['title'];
                if (!$title || $title == '') {
                    $title = __('Concerts', 'songkick-concerts-and-festivals');
                }
                $title = htmlentities($title, ENT_QUOTES, SONGKICK_I18N_ENCODING);
                $title = apply_filters('widget_title', $title);

                echo wp_kses_post($before_widget);
                echo '<div class="songkick-events">';
                if ($title)
                    echo wp_kses_post($before_title . $title . $after_title);
                echo $sk->to_html();
                echo '</div>';
                echo wp_kses_post($after_widget);
            }
        } catch (Exception $e) {
            $msg = 'Error on ' . get_bloginfo('wpurl') . ' while trying to display Songkick Concerts plugin: ' . $e->getMessage();
            error_log($msg, 0);
            return '';
        }
    }
}

?>