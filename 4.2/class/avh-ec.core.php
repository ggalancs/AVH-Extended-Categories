<?php

/**
 * Create HTML list of categories.
 *
 * @uses Walker
 */
class AVHEC_Walker_Category extends Walker
{
    /**
     * @see   Walker::$db_fields
     * @since 2.1.0
     * @todo  Decouple this
     * @var array
     */
    public $db_fields = array('parent' => 'parent', 'id' => 'term_id');
    /**
     * @see   Walker::$tree_type
     * @since 2.1.0
     * @var string
     */
    public $tree_type = 'category';

    /**
     * @see      Walker::end_el()
     * @since    2.1.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $object
     * @param int    $depth  Depth of category. Not used.
     * @param array  $args   Only uses 'list' for whether should append to output.
     */
    public function end_el(&$output, $object, $depth = 0, $args = array())
    {
        if ('list' != $args['style']) {
            return;
        }

        $output .= '</li>' . "\n";
    }

    /**
     * @see   Walker::end_lvl()
     * @since 2.1.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of category. Used for tab indentation.
     * @param array  $args   Will only append content if style argument value is 'list'.
     */
    public function end_lvl(&$output, $depth = 0, $args = array())
    {
        if ('list' != $args['style']) {
            return;
        }

        $indent = str_repeat("\t", $depth);
        $output .= $indent . '</ul>' . "\n";
    }

    /**
     * @see   Walker::start_el()
     * @since 2.1.0
     *
     * @param string $output   Passed by reference. Used to append additional content.
     * @param object $category Category data object.
     * @param int    $depth    Depth of category in reference to parents.
     * @param array  $args
     * @param int    $current_object_id
     */
    public function start_el(&$output, $category, $depth = 0, $args = array(), $current_object_id = 0)
    {
        $cat_name = apply_filters('list_cats', esc_attr($category->name), $category);
        // Don't generate an element if the category name is empty.
        if (!$cat_name) {
            return;
        }

        $link = '<div class="avhec-widget-line"><a href="' . get_category_link($category->term_id) . '" ';
        if ($args['use_desc_for_title'] && !empty($category->description)) {
            /**
             * Filter the category description for display.
             *
             * @since 1.2.0
             *
             * @param string $description Category description.
             * @param object $category    Category object.
             */
            $link .= 'title="' .
                     esc_attr(strip_tags(apply_filters('category_description', $category->description, $category))) .
                     '"';
        } else {
            $link .= 'title="' . sprintf(__('View all posts filed under %s'), $cat_name) . '"';
        }
        $link .= '>';
        $link .= $cat_name . '</a>';

        if (!empty($args['feed_image']) || !empty($args['feed'])) {
            $link .= '<div class="avhec-widget-rss"> ';

            if (empty($args['feed_image'])) {
                $link .= '(';
            }

            $link .= '<a href="' . get_category_feed_link($category->term_id, $args['feed_type']) . '"';

            if (empty($args['feed'])) {
                $alt = ' alt="' . sprintf(__('Feed for all posts filed under %s'), $cat_name) . '"';
            } else {
                $alt = ' alt="' . $args['feed'] . '"';
                $name = $args['feed'];
                $link .= ' title="';
                $link .= empty($args['title']) ? $args['feed'] : $args['title'];
                $link .= '"';
            }

            $link .= '>';

            if (empty($args['feed_image'])) {
                $link .= $name;
            } else {
                $link .= '<img src="' . $args['feed_image'] . '"' . $alt . '" />';
            }
            $link .= '</a>';

            if (empty($args['feed_image'])) {
                $link .= ')';
            }

            $link .= '</div>';
        }

        if (!empty($args['show_count'])) {
            $link .= '<div class="avhec-widget-count"> (' . number_format_i18n($category->count) . ')</div>';
        }

        if (!empty($args['$show_date'])) {
            $link .= ' ' . gmdate('Y-m-d', $category->last_update_timestamp);
        }

        if ('list' == $args['style']) {
            // When on a single post get the post's category. This ensures that that category will be given the CSS style of "current category".
            if (is_single()) {
                $post_cats = get_the_category();
                $args['current_category'] = $post_cats[0]->term_id;
            }

            $output .= "\t" . '<li';
            $css_classes = array(
                'cat-item',
                'cat-item-' . $category->term_id,
            );

            if (!empty($args['current_category'])) {
                $_current_category = get_term($args['current_category'], $category->taxonomy);
                if ($category->term_id == $args['current_category']) {
                    $css_classes[] = 'current-cat';
                } elseif ($category->term_id == $_current_category->parent) {
                    $css_classes[] = 'current-cat-parent';
                }
            }

            /**
             * Filter the list of CSS classes to include with each category in the list.
             *
             * @since 4.2.0
             * @see   wp_list_categories()
             *
             * @param array  $css_classes An array of CSS classes to be applied to each list item.
             * @param object $category    Category data object.
             * @param int    $depth       Depth of page, used for padding.
             * @param array  $args        An array of wp_list_categories() arguments.
             */
            $css_classes = implode(' ', apply_filters('category_css_class', $css_classes, $category, $depth, $args));

            $output .= ' class="' . $css_classes . '"';
            $output .= '>' . $link . '</div>' . "\n";
        } else {
            $output .= "\t" . $link . '</div><br />' . "\n";
        }
    }

    /**
     * @see   Walker::start_lvl()
     * @since 2.1.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of category. Used for tab indentation.
     * @param array  $args   Will only append content if style argument value is 'list'.
     */
    public function start_lvl(&$output, $depth = 0, $args = array())
    {
        if ('list' != $args['style']) {
            return;
        }

        $indent = str_repeat("\t", $depth);
        $output .= $indent . '<ul class="children">' . "\n";
    }
}

class AVH_EC_Core
{
    public $comment;
    public $db_options_core;
    public $db_options_tax_meta;
    public $default_options;
    public $default_options_category_group;
    public $default_options_general;
    public $default_options_sp_category_group;
    public $info;
    public $options;
    public $version;

    /**
     * AVH_EC_Core constructor.
     */
    public function __construct()
    {
        $this->version = '3.10.0-dev.1';
        $this->comment = '<!-- AVH Extended Categories version ' .
                         $this->version .
                         ' | http://blog.avirtualhome.com/wordpress-plugins/ -->';
        $this->db_options_core = 'avhec';
        $this->db_options_tax_meta = 'avhec-tax_meta';

        add_action('init', array($this, 'handleInitializePlugin'), 10);
    }

    public function applyOrderFilter($orderby, $args)
    {
        switch ($args['orderby']) {
            case 'avhec_manualorder':
                $new_orderby = 't.avhec_term_order';
                break;
            case 'avhec_3rdparty_mycategoryorder':
                $new_orderby = 't.term_order';
                break;
            default:
                $new_orderby = $orderby;
                break;
        }

        return $new_orderby;
    }

    /**
     * Display or retrieve the HTML dropdown list of categories.
     * The list of arguments is below:
     * 'show_option_all' (string) - Text to display for showing all categories.
     * 'show_option_none' (string) - Text to display for showing no categories.
     * 'orderby' (string) default is 'ID' - What column to use for ordering the
     * categories.
     * 'order' (string) default is 'ASC' - What direction to order categories.
     * 'show_last_update' (bool|int) default is 0 - See {@link get_categories()}
     * 'show_count' (bool|int) default is 0 - Whether to show how many posts are
     * in the category.
     * 'hide_empty' (bool|int) default is 1 - Whether to hide categories that
     * don't have any posts attached to them.
     * 'child_of' (int) default is 0 - See {@link get_categories()}.
     * 'exclude' (string) - See {@link get_categories()}.
     * 'echo' (bool|int) default is 1 - Whether to display or retrieve content.
     * 'depth' (int) - The max depth.
     * 'tab_index' (int) - Tab index for select element.
     * 'name' (string) - The name attribute value for selected element.
     * 'class' (string) - The class attribute value for selected element.
     * 'selected' (int) - Which category ID is selected.
     * The 'hierarchical' argument, which is disabled by default, will override the
     * depth argument, unless it is true. When the argument is false, it will
     * display all of the categories. When it is enabled it will use the value in
     * the 'depth' argument.
     *
     * @since 2.1.0
     *
     * @param string|array $args Optional. Override default arguments.
     *
     * @return string HTML content only if 'echo' argument is 0.
     */
    public function avh_wp_dropdown_categories($args = array())
    {
        $mywalker = new AVH_Walker_CategoryDropdown();

        // @format_off
        $defaults = array(
            'show_option_all'  => '',
            'show_option_none' => '',
            'orderby'          => 'id',
            'order'            => 'ASC',
            'show_last_update' => 0,
            'show_count'       => 0,
            'hide_empty'       => 1,
            'child_of'         => 0,
            'exclude'          => '',
            'echo'             => 1,
            'selected'         => 0,
            'hierarchical'     => 0,
            'name'             => 'cat',
            'id'               => '',
            'class'            => 'postform',
            'depth'            => 0,
            'tab_index'        => 0,
            'taxonomy'         => 'category',
            'walker'           => $mywalker,
            'hide_if_empty'    => false
        );
        // @format_on
        $defaults['selected'] = (is_category()) ? get_query_var('cat') : 0;

        $r = wp_parse_args($args, $defaults);

        if (!isset($r['pad_counts']) && $r['show_count'] && $r['hierarchical']) {
            $r['pad_counts'] = true;
        }

        $r['include_last_update_time'] = $r['show_last_update'];
        extract($r);

        $tab_index_attribute = '';
        if ((int) $tab_index > 0) {
            $tab_index_attribute = ' tabindex="' . $tab_index . '"';
        }

        // Avoid clashes with the 'name' param of get_terms().
        $get_terms_args = $r;
        unset($get_terms_args['name']);
        $categories = get_terms($r['taxonomy'], $get_terms_args);

        $name = esc_attr($r['name']);
        $class = esc_attr($r['class']);
        $id = $r['id'] ? esc_attr($r['id']) : $name;

        if (!$r['hide_if_empty'] || !empty($categories)) {
            $output = "<select name='$name' id='$id' class='$class' $tab_index_attribute>\n";
        } else {
            $output = '';
        }

        if (empty($categories) && !$r['hide_if_empty'] && !empty($show_option_none)) {
            $show_option_none = apply_filters('list_cats', $show_option_none);
            $output .= "\t<option value='-1' selected='selected'>$show_option_none</option>\n";
        }
        if (!empty($categories)) {

            if ($show_option_all) {
                $show_option_all = apply_filters('list_cats', $show_option_all);
                $selected = ('0' === strval($r['selected'])) ? " selected='selected'" : '';
                $output .= "\t" . '<option value="0"' . $selected . '>' . $show_option_all . '</option>' . "\n";
            }

            if ($show_option_none) {
                $show_option_none = apply_filters('list_cats', $show_option_none);
                $selected = ('-1' === strval($r['selected'])) ? " selected='selected'" : '';
                $output .= "\t" . '<option value="-1"' . $selected . '>' . $show_option_none . '</option>' . "\n";
            }

            if ($hierarchical) {
                $depth = $r['depth']; // Walk the full depth.
            } else {
                $depth = -1; // Flat
            }
            $output .= walk_category_dropdown_tree($categories, $depth, $r);
        }
        if (!$r['hide_if_empty'] || !empty($categories)) {
            $output .= "</select>\n";
        }

        $output = apply_filters('wp_dropdown_cats', $output);

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Display or retrieve the HTML list of categories.
     * The list of arguments is below:
     * 'show_option_all' (string) - Text to display for showing all categories.
     * 'orderby' (string) default is 'ID' - What column to use for ordering the
     * categories.
     * 'order' (string) default is 'ASC' - What direction to order categories.
     * 'show_last_update' (bool|int) default is 0 - See {@link
     * walk_category_dropdown_tree()}
     * 'show_count' (bool|int) default is 0 - Whether to show how many posts are
     * in the category.
     * 'hide_empty' (bool|int) default is 1 - Whether to hide categories that
     * don't have any posts attached to them.
     * 'use_desc_for_title' (bool|int) default is 1 - Whether to use the
     * description instead of the category title.
     * 'feed' - See {@link get_categories()}.
     * 'feed_type' - See {@link get_categories()}.
     * 'feed_image' - See {@link get_categories()}.
     * 'child_of' (int) default is 0 - See {@link get_categories()}.
     * 'exclude' (string) - See {@link get_categories()}.
     * 'exclude_tree' (string) - See {@link get_categories()}.
     * 'echo' (bool|int) default is 1 - Whether to display or retrieve content.
     * 'current_category' (int) - See {@link get_categories()}.
     * 'hierarchical' (bool) - See {@link get_categories()}.
     * 'title_li' (string) - See {@link get_categories()}.
     * 'depth' (int) - The max depth.
     *
     * @since 2.1.0
     *
     * @param string|array $args Optional. Override default arguments.
     *
     * @return void|string HTML content only if 'echo' argument is 0.
     */
    public function avh_wp_list_categories($args = array())
    {
        $mywalker = new AVHEC_Walker_Category();
        $defaults = array(
            'show_option_all'    => '',
            'orderby'            => 'name',
            'order'              => 'ASC',
            'show_last_update'   => 0,
            'style'              => 'list',
            'show_count'         => 0,
            'hide_empty'         => 1,
            'use_desc_for_title' => 1,
            'child_of'           => 0,
            'feed'               => '',
            'feed_type'          => '',
            'feed_image'         => '',
            'exclude'            => '',
            'exclude_tree'       => '',
            'current_category'   => 0,
            'hierarchical'       => true,
            'title_li'           => __('Categories'),
            'echo'               => 1,
            'depth'              => 0,
            'walker'             => $mywalker
        );

        $r = wp_parse_args($args, $defaults);

        if (!isset($r['pad_counts']) && $r['show_count'] && $r['hierarchical']) {
            $r['pad_counts'] = true;
        }

        if (!isset($r['pad_counts']) && $r['show_count'] && $r['hierarchical']) {
            $r['pad_counts'] = true;
        }

        if (isset($r['show_date'])) {
            $r['include_last_update_time'] = $r['show_date'];
        }

        if (true == $r['hierarchical']) {
            $r['exclude_tree'] = $r['exclude'];
            $r['exclude'] = '';
        }

        extract($r);

        $categories = get_categories($r);

        $output = '';
        if ($title_li && 'list' == $style) {
            $output = '<li class="categories">' . $r['title_li'] . '<ul>';
        }

        if (empty($categories)) {
            if ('list' == $style) {
                $output .= '<li>' . __("No categories") . '</li>';
            } else {
                $output .= __("No categories");
            }
        } else {
            global $wp_query;

            if (!empty($show_option_all)) {
                if ('list' == $style) {
                    $output .= '<li><a href="' . get_bloginfo('url') . '">' . $show_option_all . '</a></li>';
                } else {
                    $output .= '<a href="' . get_bloginfo('url') . '">' . $show_option_all . '</a>';
                }
            }
            if (empty($r['current_category']) && is_category()) {
                $r['current_category'] = $wp_query->get_queried_object_id();
            }

            if ($hierarchical) {
                $depth = $r['depth'];
            } else {
                $depth = -1; // Flat.
            }

            $output .= walk_category_tree($categories, $depth, $r);
        }

        if ($title_li && 'list' == $style) {
            $output .= '</ul></li>';
        }

        $output = apply_filters('wp_list_categories', $output);

        if ($echo) {
            echo $output;
        } else {
            return $output;
        }

        return;
    }

    /**
     * Checks if running version is newer and do upgrades if necessary
     *
     * @since 1.2.3
     *
     * @param string $db_version
     */
    public function doUpdateOptions($db_version)
    {
        $options = $this->getOptions();

        // Add none existing sections and/or elements to the options
        foreach ($this->default_options as $section => $default_data) {
            if (!array_key_exists($section, $options)) {
                $options[$section] = $default_data;
                continue;
            }
            foreach ($default_data as $element => $default_value) {
                if (!array_key_exists($element, $options[$section])) {
                    $options[$section][$element] = $default_value;
                }
            }
        }

        // Remove none existing sections and/or elements from the options
        foreach ($options as $section => $data) {
            if (!array_key_exists($section, $this->default_options)) {
                unset($options[$section]);
                continue;
            }
            foreach ($data as $element => $value) {
                if (!array_key_exists($element, $this->default_options[$section])) {
                    unset($options[$section][$element]);
                }
            }
        }
        /**
         * Update the options to the latests versions
         */
        $options['general']['version'] = $this->version;
        $options['general']['dbversion'] = $db_version;
        $this->saveOptions($options);
    }

    /**
     * Get the base directory of a directory structure
     *
     * @param string $directory
     *
     * @return string
     */
    public function getBaseDirectory($directory)
    {
        // place each directory into array and get the last element
        $directory_array = explode('/', $directory);
        // get highest or top level in array of directory strings
        $public_base = end($directory_array);

        return $public_base;
    }

    public function getCategories()
    {
        static $_categories = null;
        if (null === $_categories) {
            $_categories = get_categories('get=all');
        }

        return $_categories;
    }

    public function getCategoriesId($categories)
    {
        static $_categories_id = null;
        if (null == $_categories_id) {
            foreach ($categories as $key => $category) {
                $_categories_id[$category->term_id] = $key;
            }
        }

        return $_categories_id;
    }

    /**
     * *******************************
     * *
     * Methods for variable: options *
     * *
     * ******************************
     */

    /**
     * Get the value for an option element.
     * If there's no option is set on the Admin page, return the default value.
     *
     * @param string $key
     * @param string $option
     *
     * @return mixed
     */
    public function getOptionElement($option, $key)
    {
        if ($this->options[$option][$key]) {
            $return = $this->options[$option][$key]; // From Admin Page
        } else {
            $return = $this->default_options[$option][$key]; // Default
        }

        return ($return);
    }

    /**
     * return array
     */
    public function getOptions()
    {
        return ($this->options);
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function handleInitializePlugin()
    {
        global $wpdb;

        /** @var AVH_EC_Category_Group $catgrp */
        $catgrp = &AVH_EC_Singleton::getInstance('AVH_EC_Category_Group');
        $db_version = 4;

        $info['siteurl'] = get_option('siteurl');
        $info['plugin_dir'] = AVHEC_PLUGIN_DIR;
        $info['graphics_url'] = AVHEC_PLUGIN_URL . '/images';

        // Set class property for info
        $this->info = array(
            'home'         => get_option('home'),
            'siteurl'      => $info['siteurl'],
            'plugin_dir'   => $info['plugin_dir'],
            'js_dir'       => $info['plugin_dir'] . '/js',
            'graphics_url' => $info['graphics_url']
        );

        // Set the default options
        $this->default_options_general = array(
            'version'                          => $this->version,
            'dbversion'                        => $db_version,
            'alternative_name_select_category' => ''
        );

        // Set the default category group options
        $no_group_id = $catgrp->getTermIDBy('slug', 'none');
        $home_group_id = $catgrp->getTermIDBy('slug', 'home');
        $default_group_id = $catgrp->getTermIDBy('slug', 'all');
        $this->default_options_category_group = array(
            'no_group'      => $no_group_id,
            'home_group'    => $home_group_id,
            'default_group' => $default_group_id
        );

        $this->default_options_sp_category_group = array(
            'home_group'     => $home_group_id,
            'category_group' => $default_group_id,
            'day_group'      => $default_group_id,
            'month_group'    => $default_group_id,
            'year_group'     => $default_group_id,
            'author_group'   => $default_group_id,
            'search_group'   => $default_group_id
        );

        $this->default_options = array(
            'general'       => $this->default_options_general,
            'cat_group'     => $this->default_options_category_group,
            'widget_titles' => array(),
            'sp_cat_group'  => $this->default_options_sp_category_group
        );

        /**
         * Set the options for the program
         */
        $this->loadOptions();

        // Check if we have to do updates
        if ((!isset($this->options['general']['dbversion'])) || $this->options['general']['dbversion'] < $db_version) {
            $this->doUpdateOptions($db_version);
        }

        $db = new AVH_DB();
        if (!$db->field_exists('avhec_term_order', $wpdb->terms)) {
            $wpdb->query("ALTER TABLE $wpdb->terms ADD `avhec_term_order` INT( 4 ) null DEFAULT '0'");
        }

        $this->handleTextdomain();
        add_filter('get_terms_orderby', array($this, 'applyOrderFilter'), 10, 2);
    }

    /**
     * Loads the i18n
     */
    public function handleTextdomain()
    {
        load_plugin_textdomain('avh-ec', false, AVHEC_RELATIVE_PLUGIN_DIR . '/lang');
    }

    /**
     * Used in forms to set the checked option.
     *
     * @param mixed      $checked
     * @param mixed_type $current
     *
     * @return string
     * @since 2.0
     */
    public function isChecked($checked, $current)
    {
        if ($checked == $current) {
            return (' checked="checked"');
        }

        return ('');
    }

    /**
     * Used in forms to set the SELECTED option
     *
     * @param string $current
     * @param string $field
     *
     * @return string
     */
    public function isSelected($current, $field)
    {
        if ($current == $field) {
            return (' SELECTED');
        }

        return ('');
    }

    /**
     * Retrieves the plugin options from the WordPress options table and assigns to class variable.
     * If the options do not exists, like a new installation, the options are set to the default value.
     *
     * @return none
     */
    public function loadOptions()
    {
        $options = get_option($this->db_options_core);
        if (false === $options) { // New installation
            $this->resetToDefaultOptions();
        } else {
            $this->setOptions($options);
        }
    }

    /**
     * Reset to default options and save in DB
     */
    public function resetToDefaultOptions()
    {
        $this->options = $this->default_options;
        $this->saveOptions($this->default_options);
    }

    /**
     * Save all current options and set the options
     *
     * @param array $options
     */
    public function saveOptions($options)
    {
        update_option($this->db_options_core, $options);
        wp_cache_flush(); // Delete cache
        $this->setOptions($options);
    }
}

/**
 * Create HTML dropdown list of Categories.
 *
 * @uses Walker
 */
class AVH_Walker_CategoryDropdown extends Walker_CategoryDropdown
{
    public function walk($elements, $max_depth)
    {
        $args = array_slice(func_get_args(), 2);
        $output = '';

        if ($max_depth < -1) {
            return $output;
        }

        if (empty($elements)) {
            return $output;
        }
        
        $parent_field = $this->db_fields['parent'];

        // flat display
        if (-1 == $max_depth) {
            $empty_array = array();
            foreach ($elements as $e) {
                $this->display_element($e, $empty_array, 1, 0, $args, $output);
            }

            return $output;
        }

        /*
         * need to display in hierarchical order seperate elements into two buckets: top level and children elements children_elements is two dimensional array, eg. children_elements[10][] contains all sub-elements whose parent is 10.
         */
        $top_level_elements = array();
        $children_elements = array();
        foreach ($elements as $e) {
            if (0 == $e->$parent_field) {
                $top_level_elements[] = $e;
            } else {
                $children_elements[$e->$parent_field][] = $e;
            }
        }

        /*
         * when none of the elements is top level assume the first one must be root of the sub elements
         */
        if (empty($top_level_elements)) {

            $first = array_slice($elements, 0, 1);
            $root = $first[0];

            $top_level_elements = array();
            $children_elements = array();
            foreach ($elements as $e) {
                if ($root->$parent_field == $e->$parent_field) {
                    $top_level_elements[] = $e;
                } else {
                    $children_elements[$e->$parent_field][] = $e;
                }
            }
        }

        foreach ($top_level_elements as $e) {
            $this->display_element($e, $children_elements, $max_depth, 0, $args, $output);
        }

        /*
         * if we are displaying all levels, and remaining children_elements is not empty, then we got orphans, which should be displayed regardless
         */
        if ((0 == $max_depth) && count($children_elements) > 0) {
            $empty_array = array();
            foreach ($children_elements as $orphans) {
                foreach ($orphans as $op) {
                    $this->display_element($op, $empty_array, 1, 0, $args, $output);
                }
            }
        }

        return $output;
    }
}
