=== Songkick Concerts and Festivals ===
Contributors: saleandro, coox
Tags: songkick, concerts, events, festivals, widget
Requires at least: 2.8.2, PHP 5 or higher
Tested up to: 3.5.1
Stable tag: 0.9.4.4

This plugin lets you display events for a Songkick user, artist, venue, or metro area on your WordPress blog, as a widget or shortcode.

== Description ==

This plugin lets you display upcoming or past events for a Songkick user, artist, venue, or metro area on your WordPress blog.

Events can be displayed by adding the Songkick widget to your template, or by adding the shortcode [songkick_concerts_and_festivals] anywhere in your blog.

= Features =

*   Upcoming events for an artist
*   Past events for an artist
*   Upcoming events for a venue
*   Upcoming events for a user
*   Past events for a user
*   Upcoming events for a metro area. A metro area is a city or a collection of cities that Songkick uses to notify users of concerts near them.
*   Widget or shortcode format
*   Show events for multiple artists, users, venues, or metro areas.
*   Paginated list of events
*   HTML markup with support for events as defined by [Schema.org](http://www.schema.org/)

= Requirements =

*   This plugin uses a non-commercial Songkick API key. If you have a commercial website, you’ll need your own Songkick API key. Please read through [Songkick’s API terms of use](http://www.songkick.com/developer/api-terms-of-use). Apply for a key here: [Songkick API docs](http://www.songkick.com/developer)
*   This plugin requires PHP 5

= Settings =

Go to the Settings page to configure default options for the plugin. You can also specify your settings under Plugins/Widget or via shortcode options.

*   For a user, simply put your username in the admin interface.
*   For an artist, you should use the artist’s Songkick id, as shown in the url for your artist page. For example, the url "http://www.songkick.com/artists/123-your-name" has the id "123".
*   The same goes for metro areas: "http://www.songkick.com/metro_areas/123-city-name" has the id "123".
*   And venues: "http://www.songkick.com/venues/123-venue-name" has the id "123".

= Widget =

Go to the admin Widgets page and simply drag the widget into a sidebar and configure it.

= Shortcode =

Add the shortcode [songkick_concerts_and_festivals] in the content of any blog post.

When using a shortcode, you can set which artist, venue, metro area, or user you want to display events for, allowing you to show events for different entities:

*   Users:   `[songkick_concerts_and_festivals songkick_id=your_username songkick_id_type=user]`
*   Artists: `[songkick_concerts_and_festivals songkick_id=your_artist_id songkick_id_type=artist]`
*   Venues: `[songkick_concerts_and_festivals songkick_id=your_venue_id songkick_id_type=venue]`
*   Metro areas: `[songkick_concerts_and_festivals songkick_id=your_metro_area_id songkick_id_type=metro_area]`

Override shortcode settings:

*   gigography=true|false
*   number_of_events=integer
*   show_pagination=true|false
*   no_calendar_style=true|false — removes the calendar style from the event dates
*   order=asc|desc - sort order for artist or user events

= PHP code =

You can call the shortcode method directly in your PHP code:
`<?php echo do_shortcode('[songkick_concerts_and_festivals]'); ?>`

= Blogs using this plugin =

*   [OK Go](http://www.okgo.net/shows/)
*   [Haircut Records](http://haircutrecords.co.uk/site/)

Know any others? Let me know!

= Contribute =

This is an open source project that I maintain during my spare time. I welcome contributions!

The code lives on [Github](http://github.com/saleandro/songkick-wp-plugin). To send your contribution, fork my project, make your lovely changes, and send me a [pull request](http://help.github.com/send-pull-requests/). Thanks :)

== Installation ==

1. Upload the directory "songkick_concerts_and_festivals" to the "/wp-content/plugins/" directory
1. Activate the plugin through the "Plugins" menu
1. Go to the Settings page for Songkick or the Widgets page and set your username/artist/venue/metro area ID.
1. Please read through [Songkick’s API terms of use](http://www.songkick.com/developer/api-terms-of-use). If you have a commercial website, please apply for your own API key here: http://www.songkick.com/developer and configure it on the Settings page.
1. Add the widget to a sidebar or the shortcode anywhere in your blog.

== Screenshots ==

1. Widget for a user.

== Changelog ==

= 0.6 =
* Added shortcode.

= 0.7 =
* Fixed some warnings.
* Made calendar date style inline.

= 0.8 =
* Fixed bug where shortcode content would always display on top of other content.

= 0.9 =
* Default options can be overridden when calling shortcode function. This means you can use the plugin for different users and artists.
See Songkick’s admin settings for details.

= 0.9.1 =

* Refactored events and presentable event code.
* Improved exception handling and error logging (thanks [Ethan](https://github.com/ezmiller/songkick-plugin-ethanmod)).
* Improved documentation.
* Added option to hide calendar style for dates (no_calendar_style=true)

= 0.9.2 =

* Support for displaying metro area events.

= 0.9.3 =

* Support for displaying venue events.
* Markup with support for events as defined by [Schema.org](http://www.schema.org/)
* Paginated list of events for shortcode option.

= 0.9.4 -

* Reimplementation of the Widget class. Allows for multilple Widget instances.
* Remove requirement for an API key for *non-commercial* websites.

= 0.9.4.1 =
* Fixing up and removing warnings

= 0.9.4.2 =
* Sort order of past events is descending

= 0.9.4.3 =
* Bug fix: Fixing error when displaying a user's upcoming events

= 0.9.4.4 =
* Catching any error when testing Songkick API key. Some users reported seeing a "name lookup" error on the Settings page.
