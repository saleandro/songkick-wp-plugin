<?php
$options = get_option(SONGKICK_OPTIONS);
$title = $options['title'];
if (!$title || $title == '') {
    $title = __('Concerts', SONGKICK_TEXT_DOMAIN);
}
$title = htmlentities($title, ENT_QUOTES, SONGKICK_I18N_ENCODING);

extract($this->widget_args);

echo $before_widget;
?>
<div class="songkick-events">
<?php
echo $before_title . $title . $after_title;
?>
<ul class="songkick-events">
    <?php foreach ($this->events as $event) {
    $presentable_event = new SongkickPresentableEvent($event);
    $presentable_event->template = 'songkick-widget_event.php';
    ?>
    <li><?php echo $presentable_event->to_html($this->no_calendar_style, $this->date_color);?></li>
    <?php } // end foreach ?>
</ul>
</div>
<?php echo $this->powered_by_songkick($this->logo); ?>
<?php echo $after_widget; ?>
