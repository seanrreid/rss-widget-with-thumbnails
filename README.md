# RSS Feed Parser for Wordpress via Shortcode
##A fork of WP RSS Retriever that adds thumbnail generation for feed images.

This plugin is a fork of the excellent [RSS Retriever Plugin by Travis Taylor](https://github.com/tjtaylo/WP-RSS-Retriever).

I had the need to generate lightweight thumbnails from the images found in an RSS feed.  By default, RSS Retriever does a CSS resize, but uses the original image from the host. I updated it to add a include image caching and resizing inside of Wordpress by incorporating ImageCache and BFI_Thumb.

BFI_Thumb can be found here: [https://bfintal.github.io/bfi_thumb/](https://bfintal.github.io/bfi_thumb/)
ImageCache can be found here: [http://nielse63.github.io/php-image-cache/](http://nielse63.github.io/php-image-cache/)

### Changelog

* 02.05.2015 - Initial build
    * This version is very inelegant. It's in a "it just works" format in order to address an existing issue for a particular client. Future updates should help iron things out a bit.
