<?php

class SongkickPresentableEvent {
    function SongkickPresentableEvent($event) {
        $this->event = $event;
        $this->border_color = '#878787';
    }
    
    function to_html($no_calendar_style, $date_color) {
        $date = $this->date_to_html($no_calendar_style, $date_color);

        $html  = '<div itemscope itemtype="http://schema.org/Event">';
        $html .= $date;
        $html .= '<span class="event-name"><a itemprop="url" href="'.$this->event_url().'"><span itemprop="name">'.$this->event_name().'</span></a>';
        $html .= '<br> '.$this->venue_to_html(). '</span>';
        $html .= '<div style="clear:left"></div>';
        $html .= '</div>';
        return $html;
    }

    function event_url() {
        return $this->event->uri;
    }

    function event_name() {
        if ($this->is_festival()) {
            return $this->event->displayName;
        } else {
            $headliners = array();
            foreach ($this->event->performance as $performance) {
                if ($performance->billing == 'headline')
                    $headliners[] = $performance->artist->displayName;
            }
            if (empty($headliners))
                $headliners[] = $this->event->performance[0];
            return join(', ', $headliners);
        }
    }

    function venue_to_html() {
        $venue = '<span itemprop="location" itemscope itemtype="http://schema.org/Place" class="venue">';
        if ($this->event->venue->id) {
            $venue .= '<span itemprop="name">'.htmlentities($this->event->venue->displayName, ENT_QUOTES, SONGKICK_I18N_ENCODING).'</span>, ';
        }
        $venue .= '<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
        $venue .= '<span itemprop="addressLocality">'.$this->event->location->city.'</span>';
        $venue .= '</span>';
        if ($this->event->location->lat) {
            $venue .= '<span itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates">';
            $venue .= '  <meta itemprop="latitude" content="'.$this->event->location->lat.'" />';
            $venue .= '  <meta itemprop="longitude" content="'.$this->event->location->lng.'" />';
            $venue .= '</span>';      
        }
        $venue .= '</span>';
        return $venue;
    }
    
    function date() {
        return strtotime($this->event->start->date);
    }

    protected function is_festival() {
        return (strtolower($this->event->type) == 'festival');
    }

    /**
     * Construct an HTML block presenting a concert date.
     * @param string $date_color (optional) An override background color, in the #rrggbb form.
     * @return string The HTML block.
     */
    protected function date_to_html($no_calendar_style, $date_color) {
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
        $saved_locale = setlocale(LC_TIME,"0");
        setlocale(LC_TIME, WPLANG.'.UTF-8');
        $day_name = strftime('%a', $date);
        $month_name = strftime('%b', $date);
        // Restore previous locale setting
        setlocale(LC_TIME, $saved_locale);

        // Not happy doing this, but the calendar styling is easily broken by the blog's or other plugin's styling.
        $css = array();
        if ($no_calendar_style) {
            $date_color = null;
            $css['year']           = '';
            $css['day']            = '';
            $css['month']          = '';
            $css['day-month']      = '';
            $css['date-wrapper']   = '';
            $css['a-date-wrapper'] = '';
            $css['day-name']       = '';
        } else {
            $css['year']           = 'font-size:1.6em;line-height:1em;';
            $css['day']            = 'display:block;font-size:1.8em;margin: 0px;margin-top: 2px;padding: 0px;';
            $css['month']          = 'font-size:1.4em;margin: 0px;margin-bottom: 2px;padding: 0px;';
            $css['day-month']      = 'border: 1px solid '.$this->border_color.';display:block;padding-bottom:4px;padding-top:3px;line-height:1.1em;';
            $css['date-wrapper']   = 'font-size:7px;font-weight:bold;margin-right:10px;color:'.$this->border_color.';float:left;text-align:center;width:34px;margin-left:0px;line-height:1.1em;';
            $css['a-date-wrapper'] = 'text-decoration: none;color:'.$this->border_color;
            $css['day-name']       = 'background-color: #303030;color:#FFFFFF;display:block;font-size:7px;line-height:10px;padding-bottom:1px;padding-top:2px;text-shadow:1px 1px rgba(0, 0, 0, 0.6);text-transform:uppercase;';
        }

        // Construct the HTML block presenting the formatted date.
        $override_color = (empty($date_color)) ? '' : ';background-color:'.$date_color;
        $str = '<meta itemprop="startDate" content="'.date('c', $date).'">';
        $str .= '<span class="date-wrapper" style="'.$css['date-wrapper'].'"><a style="'.$css['a-date-wrapper'].'" title="'.date('Y-m-d', $date).'" href="'.$this->event_url().'">';
        $str .= '  <span class="day-name" style="'.$css['day-name'].$override_color.'">'.htmlentities($day_name, ENT_QUOTES, 'UTF-8').'</span>';
        $str .= '  <span class="day-month" style="'.$css['day-month'].'"><span class="month" style="'.$css['month'].'">'.htmlentities($month_name, ENT_QUOTES, 'UTF-8').'</span>';
        $str .= '  <span class="day" style="'.$css['day'].'">'.date('d', $date).'</span></span>';
        $str .= '  <span class="year" style="'.$css['year'].'">'.date('Y', $date).'</span>';
        $str .= '</a></span>';

        return $str;
    }

}
?>