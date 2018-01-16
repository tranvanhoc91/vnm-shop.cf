<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */


/**
 * Sort items by property
 * @param a $items array of object
 * @param $key
 * @param $property
 * @param bool|false $reverse
 * @return array sorted items
 */
if (!function_exists('wpfd_sort_by_property')) {
    function wpfd_sort_by_property($items, $key = '', $property, $reverse = false)
    {

        $sorted = array();
        $items_bk = $items;
        foreach ($items as $item) {
            $sorted[$item->$key] = $item->$property;
            $items_bk[$item->$key] = $item;
        }

        if ($reverse) arsort($sorted); else asort($sorted);
        $results = array();

        foreach ($sorted as $key2 => $value) {
            $results[] = $items_bk[$key2];
        }
        return $results;

    }
}

/**
 * Get extension of file
 * @param $file
 * @return string
 */
if (!function_exists('wpfd_getext')) {
    function wpfd_getext($file)
    {
        $dot = strrpos($file, '.') + 1;

        return substr($file, $dot);
    }
}

/**
 * Get size of file with remote url
 * @param $url
 * @return mixed|string
 */
if (!function_exists('wpfd_remote_file_size')) {
    function wpfd_remote_file_size($url)
    {

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_NOBODY => 1,
        ));

        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);

        $clen = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        if (!$clen || ($clen == -1)) {
            return 'n/a';
        }

        return $clen;

    }
}

/**
 * display select pages number
 * @param int $paged
 */
if (!function_exists('wpfd_num')) {
    function wpfd_num($paged = 5)
    {
        ?>
        <div class="wpfd-num">
            <?php
            $p_number = array(5, 10, 15, 20, 25, 30, 50, 100, -1);
            ?>
            <div class="limit pull-right">
                Display #
                <select id="limit" name="limit" class="" size="1">
                    <?php
                    foreach ($p_number as $num) {
                        $selected = $num == $paged ? ' selected="selected"' : '';
                        ?>
                        <option
                                value="<?php echo $num; ?>"<?php echo $selected; ?>><?php echo $num == '-1' ? 'All' : $num; ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }
}

/**
 * render a select html
 * @param array $options
 * @param string $name
 * @param string $select
 * @param string $attr
 * @return string
 */
if (!function_exists('wpfd_select')) {
    function wpfd_select($options = array(), $name = '', $select = '', $attr = '')
    {
        $html = '';

        $html .= '<select';
        if ($name != '') {
            $html .= ' name="' . $name . '"';
        }

        if ($attr != '') {
            $html .= ' ' . $attr;
        }
        $html .= '>';
        if (!empty($options)) {

            foreach ($options as $key => $value) {
                $select_option = '';
                if (is_array($select)) {
                    $select_option = in_array($key, $select) ? 'selected="selected"' : '';
                } else {
                    $select_option = selected($select, $key, false);
                }
                $html .= '<option value="' . $key . '" ' . $select_option . '>' . $value . '</option>';
            }

        }

        $html .= '</select>';

        return $html;
    }
}

/**
 * Display a custom pagination
 * @param array $args
 * @param string $form_name
 * @return array|string
 */
if (!function_exists('wpfd_pagination')) {
    function wpfd_pagination($args = array(), $form_name = '')
    {
        global $wp_query, $wp_rewrite;

        // Setting up default values based on the current URL.
        $pagenum_link = html_entity_decode(get_pagenum_link());
        $url_parts = explode('?', $pagenum_link);

        // Get max pages and current page out of the current query, if available.
        $total = isset($wp_query->max_num_pages) ? $wp_query->max_num_pages : 1;
        $current = get_query_var('paged') ? intval(get_query_var('paged')) : 1;

        // Append the format placeholder to the base URL.
        $pagenum_link = trailingslashit($url_parts[0]) . '%_%';

        // URL base depends on permalink settings.
        $format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged') : '?paged=%#%';

        $defaults = array(
            'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
            'format' => $format, // ?page=%#% : %#% is replaced by the page number
            'total' => $total,
            'current' => $current,
            'show_all' => false,
            'prev_next' => true,
            'prev_text' => __('&laquo; Previous', 'wp-smart-editor'),
            'next_text' => __('Next &raquo;', 'wp-smart-editor'),
            'end_size' => 1,
            'mid_size' => 2,
            'type' => 'plain',
            'add_args' => array(), // array of query args to add
            'add_fragment' => '',
            'before_page_number' => '',
            'after_page_number' => ''
        );

        $args = wp_parse_args($args, $defaults);

        if (!is_array($args['add_args'])) {
            $args['add_args'] = array();
        }

        // Merge additional query vars found in the original URL into 'add_args' array.
        if (isset($url_parts[1])) {
            // Find the format argument.
            $format = explode('?', str_replace('%_%', $args['format'], $args['base']));
            $format_query = isset($format[1]) ? $format[1] : '';
            wp_parse_str($format_query, $format_args);

            // Find the query args of the requested URL.
            wp_parse_str($url_parts[1], $url_query_args);

            // Remove the format argument from the array of query arguments, to avoid overwriting custom format.
            foreach ($format_args as $format_arg => $format_arg_value) {
                unset($url_query_args[$format_arg]);
            }

            $args['add_args'] = array_merge($args['add_args'], urlencode_deep($url_query_args));
        }

        // Who knows what else people pass in $args
        $total = (int)$args['total'];
        if ($total < 2) {
            return;
        }
        $current = (int)$args['current'];
        $end_size = (int)$args['end_size']; // Out of bounds?  Make it the default.
        if ($end_size < 1) {
            $end_size = 1;
        }
        $mid_size = (int)$args['mid_size'];
        if ($mid_size < 0) {
            $mid_size = 2;
        }
        $add_args = $args['add_args'];
        $r = '';
        $page_links = array();
        $dots = false;

        if ($args['prev_next'] && $current && 1 < $current) :
            $link = str_replace('%_%', 2 == $current ? '' : $args['format'], $args['base']);
            $link = str_replace('%#%', $current - 1, $link);
            if ($add_args)
                $link = add_query_arg($add_args, $link);
            $link .= $args['add_fragment'];

            /**
             * Filter the paginated links for the given archive pages.
             *
             * @since 3.0.0
             *
             * @param string $link The paginated link URL.
             */
            $page_links[] = "<a class='prev page-numbers' onclick='document." . $form_name . ".paged.value=" . ($current - 1) . "; document." . $form_name . ".submit();'>" . $args['prev_text'] . "</a>";
        endif;
        for ($n = 1; $n <= $total; $n++) :
            if ($n == $current) :
                $page_links[] = "<span class='page-numbers current'>" . $args['before_page_number'] . number_format_i18n($n) . $args['after_page_number'] . "</span>";
                $dots = true;
            else :
                if ($args['show_all'] || ($n <= $end_size || ($current && $n >= $current - $mid_size && $n <= $current + $mid_size) || $n > $total - $end_size)) :
                    $link = str_replace('%_%', 1 == $n ? '' : $args['format'], $args['base']);
                    $link = str_replace('%#%', $n, $link);
                    if ($add_args)
                        $link = add_query_arg($add_args, $link);
                    $link .= $args['add_fragment'];

                    /** This filter is documented in wp-includes/general-template.php */
                    $page_links[] = "<a class='page-numbers' onclick='document." . $form_name . ".paged.value=" . $n . "; document." . $form_name . ".submit();'>" . $args['before_page_number'] . number_format_i18n($n) . $args['after_page_number'] . "</a>";
                    $dots = true;
                elseif ($dots && !$args['show_all']) :
                    $page_links[] = '<span class="page-numbers dots">' . __('&hellip;', 'wp-smart-editor') . '</span>';
                    $dots = false;
                endif;
            endif;
        endfor;
        if ($args['prev_next'] && $current && ($current < $total || -1 == $total)) :
            $link = str_replace('%_%', $args['format'], $args['base']);
            $link = str_replace('%#%', $current + 1, $link);
            if ($add_args)
                $link = add_query_arg($add_args, $link);
            $link .= $args['add_fragment'];

            /** This filter is documented in wp-includes/general-template.php */
            $page_links[] = "<a class='next page-numbers' onclick='document." . $form_name . ".paged.value=" . ($current + 1) . "; document." . $form_name . ".submit();'>" . $args['next_text'] . "</a>";
        endif;
        switch ($args['type']) {
            case 'array' :
                return $page_links;

            case 'list' :
                $r .= "<ul class='page-numbers'>\n\t<li>";
                $r .= join("</li>\n\t<li>", $page_links);
                $r .= "</li>\n</ul>\n";
                break;

            default :
                $r = join("\n", $page_links);
                break;
        }
        return $r;
    }
}


/**
 * Display a custom pagination
 * @param array $args
 * @param string $form_name
 * @return array|string
 */
if (!function_exists('wpfd_category_pagination')) {
    function wpfd_category_pagination($args = array(), $form_name = '')
    {
        global $wp_query, $wp_rewrite;

        // Setting up default values based on the current URL.
        $pagenum_link = html_entity_decode(get_pagenum_link());
        $url_parts = explode('?', $pagenum_link);

        // Get max pages and current page out of the current query, if available.
        $total = isset($wp_query->max_num_pages) ? $wp_query->max_num_pages : 1;
        $current = get_query_var('paged') ? intval(get_query_var('paged')) : 1;

        // Append the format placeholder to the base URL.
        $pagenum_link = trailingslashit($url_parts[0]) . '%_%';

        // URL base depends on permalink settings.
        $format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged') : '?paged=%#%';

        $defaults = array(
            'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
            'format' => $format, // ?page=%#% : %#% is replaced by the page number
            'total' => $total,
            'current' => $current,
            'show_all' => false,
            'prev_next' => true,
            'prev_text' => __('&laquo; Previous', 'wp-smart-editor'),
            'next_text' => __('Next &raquo;', 'wp-smart-editor'),
            'end_size' => 1,
            'mid_size' => 2,
            'type' => 'plain',
            'add_args' => array(), // array of query args to add
            'add_fragment' => '',
            'before_page_number' => '',
            'after_page_number' => ''
        );

        $args = wp_parse_args($args, $defaults);

        if (!is_array($args['add_args'])) {
            $args['add_args'] = array();
        }

        // Merge additional query vars found in the original URL into 'add_args' array.
        if (isset($url_parts[1])) {
            // Find the format argument.
            $format = explode('?', str_replace('%_%', $args['format'], $args['base']));
            $format_query = isset($format[1]) ? $format[1] : '';
            wp_parse_str($format_query, $format_args);

            // Find the query args of the requested URL.
            wp_parse_str($url_parts[1], $url_query_args);

            // Remove the format argument from the array of query arguments, to avoid overwriting custom format.
            foreach ($format_args as $format_arg => $format_arg_value) {
                unset($url_query_args[$format_arg]);
            }

            $args['add_args'] = array_merge($args['add_args'], urlencode_deep($url_query_args));
        }

        // Who knows what else people pass in $args
        $total = (int)$args['total'];
        if ($total < 2) {
            return;
        }
        $current = (int)$args['current'];
        $end_size = (int)$args['end_size']; // Out of bounds?  Make it the default.
        if ($end_size < 1) {
            $end_size = 1;
        }
        $mid_size = (int)$args['mid_size'];
        if ($mid_size < 0) {
            $mid_size = 2;
        }
        $add_args = $args['add_args'];
        $r = '';
        $page_links = array();
        $dots = false;

        if ($args['prev_next'] && $current && 1 < $current) :
            $link = str_replace('%_%', 2 == $current ? '' : $args['format'], $args['base']);
            $link = str_replace('%#%', $current - 1, $link);
            if ($add_args)
                $link = add_query_arg($add_args, $link);
            $link .= $args['add_fragment'];

            /**
             * Filter the paginated links for the given archive pages.
             *
             * @since 3.0.0
             *
             * @param string $link The paginated link URL.
             */
            $page_links[] = "<a class='prev page-numbers' data-page='" . ($current - 1) . "'>" . $args['prev_text'] . "</a>";
        endif;
        for ($n = 1; $n <= $total; $n++) :
            if ($n == $current) :
                $page_links[] = "<span class='page-numbers current'>" . $args['before_page_number'] . number_format_i18n($n) . $args['after_page_number'] . "</span>";
                $dots = true;
            else :
                if ($args['show_all'] || ($n <= $end_size || ($current && $n >= $current - $mid_size && $n <= $current + $mid_size) || $n > $total - $end_size)) :
                    $link = str_replace('%_%', 1 == $n ? '' : $args['format'], $args['base']);
                    $link = str_replace('%#%', $n, $link);
                    if ($add_args)
                        $link = add_query_arg($add_args, $link);
                    $link .= $args['add_fragment'];

                    /** This filter is documented in wp-includes/general-template.php */
                    $page_links[] = "<a class='page-numbers' data-page='" . $n . "'>" . $args['before_page_number'] . number_format_i18n($n) . $args['after_page_number'] . "</a>";
                    $dots = true;
                elseif ($dots && !$args['show_all']) :
                    $page_links[] = '<span class="page-numbers dots">' . __('&hellip;', 'wp-smart-editor') . '</span>';
                    $dots = false;
                endif;
            endif;
        endfor;
        if ($args['prev_next'] && $current && ($current < $total || -1 == $total)) :
            $link = str_replace('%_%', $args['format'], $args['base']);
            $link = str_replace('%#%', $current + 1, $link);
            if ($add_args)
                $link = add_query_arg($add_args, $link);
            $link .= $args['add_fragment'];

            /** This filter is documented in wp-includes/general-template.php */
            $page_links[] = "<a class='next page-numbers' data-page='" . ($current + 1) . "'>" . $args['next_text'] . "</a>";
        endif;
        switch ($args['type']) {
            case 'array' :
                return $page_links;

            case 'list' :
                $r .= "<ul class='page-numbers'>\n\t<li>";
                $r .= join("</li>\n\t<li>", $page_links);
                $r .= "</li>\n</ul>\n";
                break;

            default :
                $r .= "<div class='wpfd-pagination'>\n\t";
                $r .= join("\n", $page_links);
                $r .= "\n</div>\n";
                break;
        }
        return $r;
    }
}