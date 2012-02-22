<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jsapara
 * Date: 12-02-14
 * Time: 12:08 PM
 * To change this template use File | Settings | File Templates.
 */

class SongkickPresentable {

    public $border_color = '#878787';
    public $template = null;

    protected function render($template, $pass = array()) {
        extract($pass);
        $located_template = locate_template(array($template),false,false);
        ob_start();
        if ( !empty($located_template) ) {
            require($located_template);
        } else {
            ob_start();
            require(dirname(__FILE__) . '/templates/' . $template);
        }
        return ob_get_clean();
    }

    protected function event_name($event) {
        if ($this->is_festival($event)) {
            return $event->displayName;
        } else {
            $headliners = array();
            foreach ($event->performance as $performance) {
                if ($performance->billing == 'headline')
                    $headliners[] = $performance->artist->displayName;
            }
            if (empty($headliners))
                $headliners[] = $event->performance[0];
            return join(', ', $headliners);
        }
    }

    protected function is_festival($event) {
        return (strtolower($event->type) == 'festival');
    }

    protected function event_url($event,$page=false) {
        $options = get_option(SONGKICK_OPTIONS);

        if (!isset($options['show_events_locally']) || $options['show_events_locally'] == false) {
            return $event->uri;
        } else {
            if ($page !== false ) {
                global $wp;
                //$current_url = remove_query_arg(array('page','page_id','skp'), add_query_arg($wp->query_string, '', home_url($wp->request)));
                $current_url = site_url();
                $current_url = add_query_arg(sprintf("page_id=%s",$page),'',$current_url);
                $current_url = $this->current_url(sprintf("event_id=%s", $this->event->id),$current_url);
                return $current_url;
            }
            return $this->current_url(sprintf("event_id=%s", $event->id));
        }
    }

    protected function venue_to_html($event, $new_line = ", ") {
        $venue = '<span itemprop="location" itemscope itemtype="http://schema.org/Place" class="venue">';
        if ($event->venue->id) {
            $venue .= '<span itemprop="name">' . htmlentities($event->venue->displayName, ENT_QUOTES, SONGKICK_I18N_ENCODING) . '</span>' . $new_line;
        }
        $venue .= '<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
        $venue .= '<span itemprop="addressLocality">' . $event->location->city . '</span>';
        $venue .= '</span>';
        if ($event->location->lat) {
            $venue .= '<span itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates">';
            $venue .= '  <meta itemprop="latitude" content="' . $event->location->lat . '" />';
            $venue .= '  <meta itemprop="longitude" content="' . $event->location->lng . '" />';
            $venue .= '</span>';
        }
        $venue .= '</span>';
        return $venue;
    }

    protected function powered_by_songkick($logo) {
        $text = __('Concerts by Songkick', SONGKICK_TEXT_DOMAIN);
        $html = "<a class='powered-by' href='http://www.songkick.com/'>";
        $html .= "<img src='" . site_url('/wp-content/plugins/songkick-concerts-and-festivals/' . $logo) . "' title='" . htmlentities($text, ENT_QUOTES, SONGKICK_I18N_ENCODING) . "' alt='" . htmlentities($text, ENT_QUOTES, SONGKICK_I18N_ENCODING) . "' /></a>";
        return $html;
    }

    function date() {
        return strtotime($this->event->start->date);
    }

    /**
     * Construct an HTML block presenting a concert date.
     * @param string $date_color (optional) An override background color, in the #rrggbb form.
     * @return string The HTML block.
     */
    protected function date_to_html($event, $no_calendar_style, $date_color) {
        /*
         * Localization (l10n) of the date.
         *
         * Translation of day and month is leveraged to strftime(), the output
         * of which is controlled by the locale. The locale must therefore be
         * set to a value based on WPLANG (WordPress localized language).
         */
        // Save current locale setting.
        // WARNING: setlocale() is known to not be thread-safe!
        $date = $this->date();
        $saved_locale = setlocale(LC_TIME, "0");
        setlocale(LC_TIME, WPLANG . '.UTF-8');
        $day_name = strftime('%a', $date);
        $month_name = strftime('%b', $date);
        // Restore previous locale setting
        setlocale(LC_TIME, $saved_locale);

        // Not happy doing this, but the calendar styling is easily broken by the blog's or other plugin's styling.
        $css = array();
        if ($no_calendar_style) {
            $date_color = null;
            $css['year'] = '';
            $css['day'] = '';
            $css['month'] = '';
            $css['day-month'] = '';
            $css['date-wrapper'] = '';
            $css['a-date-wrapper'] = '';
            $css['day-name'] = '';
        } else {
            $css['year'] = 'font-size:1.6em;line-height:1em;';
            $css['day'] = 'display:block;font-size:1.8em;margin: 0px;margin-top: 2px;padding: 0px;';
            $css['month'] = 'font-size:1.4em;margin: 0px;margin-bottom: 2px;padding: 0px;';
            $css['day-month'] = 'border: 1px solid ' . $this->border_color . ';display:block;padding-bottom:4px;padding-top:3px;line-height:1.1em;';
            $css['date-wrapper'] = 'font-size:7px;font-weight:bold;margin-right:10px;color:' . $this->border_color . ';float:left;text-align:center;width:34px;margin-left:0px;line-height:1.1em;';
            $css['a-date-wrapper'] = 'text-decoration: none;color:' . $this->border_color;
            $css['day-name'] = 'background-color: #303030;color:#FFFFFF;display:block;font-size:7px;line-height:10px;padding-bottom:1px;padding-top:2px;text-shadow:1px 1px rgba(0, 0, 0, 0.6);text-transform:uppercase;';
        }

        // Construct the HTML block presenting the formatted date.
        $override_color = (empty($date_color)) ? '' : ';background-color:' . $date_color;
        $str = '<meta itemprop="startDate" content="' . date('c', $date) . '">';
        $str .= '<span class="date-wrapper" style="' . $css['date-wrapper'] . '"><a style="' . $css['a-date-wrapper'] . '" title="' . date('Y-m-d', $date) . '" href="' . $this->event_url($event) . '">';
        $str .= '  <span class="day-name" style="' . $css['day-name'] . $override_color . '">' . htmlentities($day_name, ENT_QUOTES, 'UTF-8') . '</span>';
        $str .= '  <span class="day-month" style="' . $css['day-month'] . '"><span class="month" style="' . $css['month'] . '">' . htmlentities($month_name, ENT_QUOTES, 'UTF-8') . '</span>';
        $str .= '  <span class="day" style="' . $css['day'] . '">' . date('d', $date) . '</span></span>';
        $str .= '  <span class="year" style="' . $css['year'] . '">' . date('Y', $date) . '</span>';
        $str .= '</a></span>';

        return $str;
    }
}