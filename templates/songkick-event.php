<div class="songkick-event">
    <div itemscope itemtype="http://schema.org/Event">
        <?php echo $this->date_to_html($this->event, $no_calendar_style, $date_color); ?>
        <span class="event-name"><a itemprop="url" href="<?php echo $this->event_url($this->event); ?>"><span
            itemprop="name"><?php echo $this->event_name($this->event); ?></span></a>
<br><?php echo $this->venue_to_html($this->event); ?></span>

        <div style="clear:left"></div>
    </div>
</div>