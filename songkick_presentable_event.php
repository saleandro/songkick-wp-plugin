<?php

class SongkickPresentableEvent extends SongkickPresentable
{
    function SongkickPresentableEvent($event)
    {
        $this->template = 'songkick-event.php';
        $this->event = $event;
        $this->border_color = '#878787';
    }

    function to_html($no_calendar_style, $date_color)
    {
        return $this->render($this->template, compact('no_calendar_style', 'date_color'));
    }

    function event_url()
    {
        $options = get_option(SONGKICK_OPTIONS);

        if (!isset($options['show_events_locally']) || $options['show_events_locally'] == false) {
            return $this->event->uri;
        } else {
            return $this->current_url(sprintf("event_id=%s", $this->event->id));
        }
    }

    private function current_url($query_string)
    {
        global $wp;
        $current_url = remove_query_arg(array('skp', 'page'), add_query_arg($wp->query_string, '', home_url($wp->request)));
        return add_query_arg($query_string, '', $current_url);
    }


}

?>