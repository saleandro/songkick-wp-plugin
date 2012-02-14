<?php
$options = get_option(SONGKICK_OPTIONS);
$title = $options['title'];
if (!$title || $title == '') {
    $title = __('Concerts', SONGKICK_TEXT_DOMAIN);
}
$title = htmlentities($title, ENT_QUOTES, SONGKICK_I18N_ENCODING);

extract($this->widget_args);

echo $before_widget;
echo '<div class="songkick-events">';
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
<?php
if ($this->show_pagination) {
    $pages = ceil($this->total / $this->number_of_events);
    if ($pages > 1) {
        $min = max($this->page - 2, 2);
        $max = min($this->page + 2, $pages - 1);
        ?>
    <div class="pagination">
        <?php
        if (1 == $this->page) {
            echo "« &nbsp;";
        } else {
            $prev = $this->page - 1;
            echo "<a href=\"" . $this->current_url("skp=$prev") . "\" rel=\"prev\">«</a> &nbsp;";
        }

        echo $this->page_to_html(1);
        if ($min > 2) echo "… &nbsp;";
        for ($i = $min; $i < $max + 1; $i++) {
            echo $this->page_to_html($i);
        }
        if ($max < $pages - 1) echo "… &nbsp;";
        echo $this->page_to_html($pages);
        if ($pages == $this->page) {
            echo "» &nbsp;";
        } else {
            $next = $this->page + 1;
            echo "<a href=\"" . $this->current_url("skp=$next") . "\" rel=\"next\">»</a> &nbsp;";
        }
        ?>
    </div>
    <?php } else { ?>
    <?php echo '<p class="profile-title"><a href="' . $this->songkick_events->profile_url() . '">'; ?>
    <?php echo htmlentities($profile_title, ENT_QUOTES, SONGKICK_I18N_ENCODING) . "</a></p>"; ?>
    <?php
    }
}?>
</div>