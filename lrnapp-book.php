<?php
/**
 * @file
 * Code for LRNApp book
 */

/**
 * Callback for apps/lrnapp-book/data.
 * @param  string $machine_name machine name of this app
 * @return array               data to be json encoded for the front end
 */
function _lrnapp_book_data($machine_name, $app_route, $params, $args) {
  $return = array();
  // check for node being set and being a number
  if (isset($_GET['node']) && is_numeric($_GET['node'])) {
    // load and see if its
    $node = node_load($_GET['node']);
    // validate they have access to see this otherwise bail
    if (!node_access('view', $node)) {
      $status = 404;
    }
  }
  else {
    $node = _mooc_helper_active_outline();
  }
  // verify this is a node
  if (isset($node->nid)) {
    // render the node to content
    $GLOBALS['moocappbook'] = $node;
    // load the navigation block; obviously not this way
    $block = block_load('mooc_nav_block', 'mooc_nav');
    $nav =  _block_get_renderable_array(_block_render_blocks(array($block)));
    $nav['mooc_nav_block_mooc_nav']['#markup'] = str_replace('href="' . base_path() .'node/', 'on-click="changePage" data-node="', $nav['mooc_nav_block_mooc_nav']['#markup']);
    // load the breadcrumb; obviously not this way
    $block = block_load('mooc_helper_book_nav', 'book_sibling_nav');
    $pathing =  _block_get_renderable_array(_block_render_blocks(array($block)));
    $return['content']['breadcrumb'] = drupal_render($pathing);
    $return['content']['nav'] = drupal_render($nav);
    $return['content']['body'] = check_markup('<h2>' . $node->title . '</h2>' . $node->body['und'][0]['value'], $node->body['und'][0]['format']);
    // load associations from cache
    if (isset($node->book['mlid'])) {
      $associations = _book_cache_get_associations($node->book);
      // previous link
      if (isset($associations['prev'])) {
        $return['prev'] = str_replace('node/', '', $associations['prev']['link_path']);
      }
      // next link
      if (isset($associations['next'])) {
        $return['next'] = str_replace('node/', '', $associations['next']['link_path']);
      }
    }
    $status = 200;
  }
  //
  return array(
    'status' => $status,
    'data' => $return
  );
}
