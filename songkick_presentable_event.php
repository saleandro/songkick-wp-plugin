<?php

class SongkickPresentableEvent {
	function SongkickPresentableEvent($event) {
		$this->event = $event;
	}
	
	function to_html($date_color) {
		$date = $this->date_to_html($date_color);

		if (strtolower($this->event->type) == 'festival') {
			$event_name = $this->event->displayName;
			$venue_name = $this->event->location->city;
		} else {
			$headliners = array();
			foreach ($this->event->performance as $performance) {
				if ($performance->billing == 'headline')
					$headliners[] = $performance->artist->displayName;
			}
			if (empty($headliners))
				$headliners []= $this->event->performance[0];
			$event_name = join(', ', $headliners);
			$venue_name = $this->event->venue->displayName.', '.$this->event->location->city;
		}

		$html  = "$date <span class=\"event-name\"><a href=\"".$this->event->uri."\">$event_name</a>";
		$html .= '<br> <span class="venue">'. htmlentities($venue_name, ENT_QUOTES, SONGKICK_I18N_ENCODING). '</span></span>';
		$html .= '<div style="clear:left"></div>';
		return $html;
	}
	
	/**
     * Construct an HTML block presenting a concert date.
     * @param string $date_color (optional) An override background color, in the #rrggbb form.
     * @return string The HTML block.
     */
	protected function date_to_html($date_color) {
		$date = strtotime($this->event->start->date);

		/*
		 * Localization (l10n) of the date.
		 *
		 * Translation of day and month is leveraged to strftime(), the output
		 * of which is controlled by the locale. The locale must therefore be
		 * set to a value based on WPLANG (WordPress localized language).
		 */
		// Save current locale setting.
		// WARNING: setlocale() is known to not be thread-safe!
		$saved_locale = setlocale(LC_TIME,"0");
		setlocale(LC_TIME, WPLANG.'.UTF-8');
		$day_name = strftime('%a', $date);
		$month_name = strftime('%b', $date);
		// Restore previous locale setting
		setlocale(LC_TIME,$saved_locale);

		// Construct the HTML block presenting the formatted date.
		$override_color = (empty($date_color)) ? '' : 'style="background-color:'.$date_color.'"';
		$str  = '<span class="date-wrapper"><a title="'.date('Y-m-d', $date).'" href="'.$this->event->uri.'">';
		$str .= '  <span class="day-name" '.$override_color.'>'.htmlentities($day_name, ENT_QUOTES, 'UTF-8').'</span>';
		$str .= '  <span class="day-month"><span class="month">'.htmlentities($month_name, ENT_QUOTES, 'UTF-8').'</span>';
		$str .= '  <span class="day">'.date('d', $date).'</span></span>';
		$str .= '  <span class="year">'.date('Y', $date).'</span>';
		$str .= '</a></span>';

		return $str;
	}
		
}
?>