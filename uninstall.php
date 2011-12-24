<?php

if (!defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
    exit();

delete_option('songkick-concerts');
delete_option('songkick-concerts-cache');

?>