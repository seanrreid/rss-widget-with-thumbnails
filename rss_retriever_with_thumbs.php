<?php
/**
 * Plugin Name: RSS Retriever with Thumbnails
 * Plugin URI: https://github.com/torchcodelab/rss-widget-with-thumbnails
 * Description: A lightweight RSS fetch plugin which uses the shortcode [rss_retriever_with_thumbs] to fetch and display an RSS feed in an unordered list.
 * Version: 1.0
 * Author: Torch Code Lab
 * Author URI: https://github.com/torchcodelab/WP-RSS-Retriever
 *
 * Original Plugin Name: RSS Retriever
 * Original Plugin URI: http://wordpress.org/plugins/wp-rss-retriever/
 * Description: A lightweight RSS fetch plugin which uses the shortcode [rss_retriever_with_thumbs] to fetch and display an RSS feed in an unordered list.
 * Original Version: 1.1.1
 * Original Author: Travis Taylor
 * Original Author URI: http://travistaylor.com/
 * License: GPL2
 */

add_shortcode( 'rss_retriever_with_thumbs', 'rss_retriever_with_thumbs_func' );

function rss_retriever_with_thumbs_func( $atts, $content = null ){
  extract( shortcode_atts( array(
    'url' => '#',
    'items' => '10',
    'orderby' => 'default',
    'title' => 'true',
    'excerpt' => '0',
    'read_more' => 'true',
    'new_window' => 'true',
    'thumbnail' => 'false',
    'source' => 'true',
    'date' => 'true',
    'cache' => '43200'
  ), $atts ) );

  update_option( 'wp_rss_cache', $cache );

  //multiple urls
  $urls = explode(',', $url);

  add_filter( 'wp_feed_cache_transient_lifetime', 'rss_retriever_with_thumbs_cache' );

  $rss = fetch_feed( $urls );

  remove_filter( 'wp_feed_cache_transient_lifetime', 'rss_retriever_with_thumbs_cache' );

  if ( ! is_wp_error( $rss ) ) :

    if ($orderby == 'date' || $orderby == 'date_reverse') {
      $rss->enable_order_by_date(true);
    }
    $maxitems = $rss->get_item_quantity( $items );
    $rss_items = $rss->get_items( 0, $maxitems );
    if ( $new_window != 'false' ) {
      $newWindowOutput = 'target="_blank" ';
    } else {
      $newWindowOutput = NULL;
    }

    if ($orderby == 'date_reverse') {
      $rss_items = array_reverse($rss_items);
    }

  endif;
  $output = '<div class="wp_rss_retriever">';
    $output .= '<ul class="wp_rss_retriever_list">';
      if ( !isset($maxitems) ) :
        $output .= '<li>' . _e( 'No items', 'wp-rss-retriever' ) . '</li>';
      else :
        //loop through each feed item and display each item.
        foreach ( $rss_items as $item ) :
          //variables
          $content = $item->get_content();
          $the_title = $item->get_title();
          $enclosure = $item->get_enclosure();

          //build output
          $output .= '<li class="wp_rss_retriever_item"><div class="wp_rss_retriever_item_wrapper">';
            //thumbnail
            if ($thumbnail != 'false' && $enclosure) {
              $thumbnail_image = $enclosure->get_thumbnail();
              if ($thumbnail_image) {
                //use thumbnail image if it exists
                $image_to_be_sized = $thumbnail_image;
              } else {
                //if not than find and use first image in content
                preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $first_image);
                if ($first_image){                  
                  $image_to_be_sized  = $first_image["src"];
                }
                $resized_image = rss_retriever_with_thumbs_resize_thumbnail($image_to_be_sized );
                $class = "portrait"; // @TODO need to fix this and make it more dynamic
                $output .= '<div class="wp_rss_retriever_image">';
                $output .= '<a class="wp_rss_retriever_title" ' . $newWindowOutput . 'href="' . esc_url( $item->get_permalink() ) . '">';
                $output .= '<img class="' . $class . '" src="' . $resized_image . '" alt="' . $title . '">';
                $output .= '</a>';
                $output .= '</div>';
              }
            }
            //title
            if ($title == 'true') {
              $output .= '<a class="wp_rss_retriever_title" ' . $newWindowOutput . 'href="' . esc_url( $item->get_permalink() ) . '"
                title="' . $the_title . '">';
                $output .= $the_title;
              $output .= '</a>';
            }
            //content
            $output .= '<div class="wp_rss_retriever_container">';
            if ( $excerpt != 'none' ) {
              if ( $excerpt > 0 ) {
                $output .= esc_html(implode(' ', array_slice(explode(' ', strip_tags($content)), 0, $excerpt))) . "...";
              } else {
                $output .= $content;
              }
              if( $read_more == 'true' ) {
                $output .= ' <a class="wp_rss_retriever_readmore" ' . $newWindowOutput . 'href="' . esc_url( $item->get_permalink() ) . '"
                    title="' . sprintf( __( 'Posted %s', 'wp-rss-retriever' ), $item->get_date('j F Y | g:i a') ) . '">';
                    $output .= __( 'Read more &raquo;', 'wp-rss-retriever' );
                $output .= '</a>';
              }
            }
            //metadata
            if ($source == 'true' || $date == 'true') {
              $output .= '<div class="wp_rss_retriever_metadata">';
                $source_title = $item->get_feed()->get_title();
                $time = $item->get_date('F j, Y - g:i a');
                if ($source == 'true' && $source_title) {
                  $output .= '<span class="wp_rss_retriever_source">' . sprintf( __( 'Source: %s', 'wp-rss-retriever' ), $source_title ) . '</span>';
                }
                if ($source == 'true' && $date == 'true') {
                  $output .= ' | ';
                }
                if ($date == 'true' && $time) {
                  $output .= '<span class="wp_rss_retriever_date">' . sprintf( __( 'Published: %s', 'wp-rss-retriever' ), $time ) . '</span>';
                }
              $output .= '</div>';
            }
          $output .= '</div></div></li>';
        endforeach;
      endif;
    $output .= '</ul>';
  $output .= '</div>';

  return $output;
}

add_option( 'wp_rss_cache', 43200 );

function rss_retriever_with_thumbs_cache() {
  //change the default feed cache
  $cache = get_option( 'wp_rss_cache', 43200 );
  return $cache;
}

function rss_retriever_with_thumbs_get_image_class($image_src) {
  $class = '';
  return $class;
}

function rss_retriever_with_thumbs_resize_thumbnail($image_to_be_sized) {  
  require_once('ImageCache.php');
  require_once('BFI_Thumb.php');
  $BFIparams = array( 'width' => 195, 'height' => 300, 'quality' => 75);
  ob_start();

  $upload_info = wp_upload_dir();
  $cache_dir = $upload_info['basedir'];
  $cache_dir .= "/book_covers";

  if ( ! is_dir( $cache_dir ) ) {
      wp_mkdir_p( $cache_dir );
  }

  $imagecache = new ImageCache();
  $cache_directory = $cache_dir;
  
  $imagecache->cached_image_directory = $cache_directory;
  $cached_src = $imagecache->cache( $image_to_be_sized );
  $url = 'http:' . $cached_src;
  $resized_image = bfi_thumb( $url, $BFIparams );

  return $resized_image;
}