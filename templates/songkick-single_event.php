<div class="songkick-single-event" itemscope itemtype="http://schema.org/Event">
    <?php echo $this->date_to_html($this->event, $this->no_calendar_style, $this->date_color); ?>
    <span class="event-name"><a itemprop="url" href="<?php echo $this->event->uri; ?>"><span
        itemprop="name"><?php echo $this->event_name($this->event); ?></span></a></span>

    <div class="venue">
        <h4>Venue</h4>
        <?php echo $this->venue_to_html($this->event, ' '); ?>
    </div>
    <?php if (!empty($this->event->performance)): ?>
    <div class="line-up">
        <h4>Line-up</h4>
        <ul>
            <?php foreach ($this->event->performance as $performance): ?>
            <li class="<?php echo $performance->billing;?>">
                <a href="<?php echo $performance->artist->uri; ?>"><?php echo $performance->displayName; ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>