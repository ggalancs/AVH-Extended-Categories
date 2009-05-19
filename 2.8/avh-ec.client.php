<?php
class WP_Widget_AVH_ExtendedCategories_Normal extends WP_Widget
{

	function __construct ()
	{
		//@TODO Convert the old option widget_extended_categories to widget_avh_extendedcategories_normal
		$widget_ops = array ('classname' => 'widget_avh_ec_normal', 'description' => __( "An extended version of the default Categories widget." ) );
		parent::__construct( false, __( 'AVH Exenteded Categories' ), $widget_ops );
	}

	function WP_Widget_AVH_EC_Normal ()
	{
		$this->__construct();
	}

	/**
	 * Display the widget
	 *
	 * @param unknown_type $args
	 * @param unknown_type $instance
	 */
	function widget ( $args, $instance )
	{
		extract( $args );

		$version = '2.0-rc1';

		$c = $instance['count'] ? '1' : '0';
		$h = $instance['hierarchical'] ? '1' : '0';
		$e = $instance['hide_empty'] ? '1' : '0';
		$s = $instance['sort_column'] ? $instance['sort_column'] : 'name';
		$o = $instance['sort_order'] ? $instance['sort_order'] : 'asc';
		$r = $instance['rssfeed'] ? 'RSS' : '';
		$i = $instance['rssimage'] ? $instance['rssimage'] : '';
		if ( empty( $r ) ) {
			$i = '';
		}
		if ( ! empty( $i ) ) {
			if ( ! file_exists( ABSPATH . '/' . $i ) ) {
				$i = '';
			}
		}
		$title = empty( $instance['title'] ) ? __( 'Categories' ) : attribute_escape( $instance['title'] );
		$style = empty( $instance['style'] ) ? 'list' : $instance['style'];
		if ( $instance['post_category'] ) {
			$post_category = unserialize( $instance['post_category'] );
			$included_cats = implode( ",", $post_category );
		}
		$cat_args = array ('include' => $included_cats, 'orderby' => $s, 'order' => $o, 'show_count' => $c, 'hide_empty' => $e, 'hierarchical' => $h, 'title_li' => '', 'show_option_none' => __( 'Select Category' ), 'feed' => $r, 'feed_image' => $i, 'name' => 'ec-cat-' . $number );
		echo $before_widget;
		echo '<!-- AVH Extended Categories version ' . $version . ' | http://blog.avirtualhome.com/wordpress-plugins/ -->';
		echo $before_title . $title . $after_title;
		echo '<ul>';

		if ( $style == 'list' ) {
			wp_list_categories( $cat_args );
		} else {
			wp_dropdown_categories( $cat_args );
			echo '<script type=\'text/javascript\'>';
			echo '/* <![CDATA[ */';
			echo '            var ec_dropdown_' . $this->number . ' = document.getElementById("ec-cat-' . $this->number . '");';
			echo '            function ec_onCatChange_' . $this->number . '() {';
			echo '                if ( ec_dropdown_' . $this->number . '.options[ec_dropdown_' . $this->number . '.selectedIndex].value > 0 ) {';
			echo '                    location.href = "' . get_option( 'home' ) . '/?cat="+ec_dropdown_' . $this->number . '.options[ec_dropdown_' . $this->number . '.selectedIndex].value;';
			echo '                }';
			echo '            }';
			echo '            ec_dropdown_' . $this->number . '.onchange = ec_onCatChange_' . $this->number . ';';
			echo '/* ]]> */';
			echo '</script>';
		}
		echo '</ul>';
		echo $after_widget;
	}

	/**
	 * When Widget Control Form Is Posted
	 *
	 * @param unknown_type $new_instance
	 * @param unknown_type $old_instance
	 * @return unknown
	 */
	function update ( $new_instance, $old_instance )
	{
		// update the instance's settings
		if ( ! isset( $new_instance['submit'] ) ) {
			return false;
		}

		$instance = $old_instance;

		$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['count'] = isset( $new_instance['count'] );
		$instance['hierarchical'] = isset( $new_instance['hierarchical'] );
		$instance['hide_empty'] = isset( $new_instance['hide_empty'] );
		$instance['sort_column'] = strip_tags( stripslashes( $new_instance['sort_column'] ) );
		$instance['sort_order'] = strip_tags( stripslashes( $new_instance['sort_order'] ) );
		$instance['style'] = strip_tags( stripslashes( $new_instance['style'] ) );
		$instance['rssfeed'] = isset( $new_instance['rssfeed'] );
		$instance['rssimage'] = attribute_escape( $new_instance['rssimage'] );
		if ( array_key_exists('all', $new_instance['post_category'] ) ){
			$instance['post_category'] = false;
		} else {
			$instance['post_category'] = serialize( $new_instance['post_category'] );
		}
		return $instance;
	}

	/**
	 *  Display Widget Control Form
	 *
	 * @param unknown_type $instance
	 */
	function form ( $instance )
	{
		// displays the widget admin form
		$instance = wp_parse_args( ( array ) $instance, array ('title' => '', 'rssimage' => '' ) );

		// Prepare data for display
		$title = htmlspecialchars( $instance['title'], ENT_QUOTES );
		$count = ( bool ) $instance['count'];
		$hierarchical = ( bool ) $instance['hierarchical'];
		$hide_empty = ( bool ) $instance['hide_empty'];
		$sort_id = ($instance['sort_column'] == 'ID') ? ' SELECTED' : '';
		$sort_name = ($instance['sort_column'] == 'name') ? ' SELECTED' : '';
		$sort_count = ($instance['sort_column'] == 'count') ? ' SELECTED' : '';
		$sort_order_a = ($instance['sort_order'] == 'asc') ? ' SELECTED' : '';
		$sort_order_d = ($instance['sort_order'] == 'desc') ? ' SELECTED' : '';
		$style_list = ($instance['style'] == 'list') ? ' SELECTED' : '';
		$style_drop = ($instance['style'] == 'drop') ? ' SELECTED' : '';
		$rssfeed = ( bool ) $instance['rssfeed'];
		$rssimage = htmlspecialchars( $instance['rssimage'], ENT_QUOTES );
		$selected_cats = (is_array($instance['post_category'] )) ? unserialize( $instance['post_category'] ) : false;

		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'title' ) . '">';
		_e( 'Title:' );
		echo '<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . $title . '" />';
		echo '</label>';
		echo '</p>';

		echo '<p>';

		echo '<label for="' . $this->get_field_id( 'count' ) . '">';
		_e( 'Show post counts' );
		echo '<input class="checkbox" type="checkbox" id="' . $this->get_field_id( 'count' ) . '"	name="' . $this->get_field_name( 'count' ) . '" ' . $this->isChecked( true, $count ) . ' />';
		echo '</label>';
		echo '<br />';

		echo '<label for="' . $this->get_field_id( 'hierachical' ) . '">';
		_e( 'Show hierarchy' );
		echo '<input class="checkbox" type="checkbox" id="' . $this->get_field_id( 'hierachical' ) . '" name="' . $this->get_field_name( 'hierarchical' ) . '" ' . $this->isChecked( true, $hierarchical ) . ' />';
		echo '</label>';
		echo '<br />';

		echo '<label for="' . $this->get_field_id( 'hide_empty' ) . '">';
		_e( 'Hide empty categories' );
		echo '<input class="checkbox" type="checkbox" id="' . $this->get_field_id( 'hide_empty' ) . '"	name="' . $this->get_field_name( 'hide_empty' ) . '" ' . $this->isChecked( true, $hide_empty ) . '/>';
		echo '</label>';
		echo '<br />';

		echo '<label for="' . $this->get_field_id( 'sort_column' ) . '">';
		_e( 'Sort by' );
		echo '<select id="' . $this->get_field_id( 'sort_column' ) . '" name="' . $this->get_field_name( 'sort_column' ) . '">';
		echo '<option value="ID" ' . $sort_id . '>' . __( 'ID' ) . '</option>';
		echo '<option value="name" ' . $sort_name . '>' . __( 'Name' ) . '</option>';
		echo '<option value="count" ' . $sort_count . '>' . __( 'Count' ) . '</option>';
		echo '</select>';
		echo '</label>';
		echo '<br />';

		echo '<label for="' . $this->get_field_id( 'sort_order' ) . '">';
		_e( 'Sort order' );
		echo '<select id="' . $this->get_field_id( 'sort_order' ) . '"	name="' . $this->get_field_name( 'sort_order' ) . '">';
		echo '<option value="asc" ' . $sort_order_a . '>' . __( 'Ascending' ) . '</option>';
		echo '<option value="desc" ' . $sort_order_d . '>' . __( 'Descending' ) . '</option>';
		echo '</select>';
		echo '</label>';
		echo '<br />';

		echo '<label for="' . $this->get_field_id( 'style' ) . '">';
		_e( 'Display style' );
		echo '<select id="' . $this->get_field_id( 'style' ) . '" name="' . $this->get_field_name( 'style' ) . '">';
		echo '<option value="list" ' . $style_list . '>' . __( 'List' ) . '</option>';
		echo '<option value="drop" ' . $style_drop . '>' . __( 'Drop down' ) . '</option>';
		echo '</select>';
		echo '</label>';
		echo '<br />';

		echo '<label for="' . $this->get_field_id( 'rssfeed' ) . '">';
		_e( 'Show RSS Feed' );
		echo '<input class="checkbox" type="checkbox" id="' . $this->get_field_id( 'rssfeed' ) . '" name="' . $this->get_field_name( 'rssfeed' ) . '" ' . $this->isChecked( true, $rssfeed ) . '/>';
		echo '</label>';
		echo '<br />';

		echo '<label for="">';
		_e( 'Full path to RSS image:' );
		echo '<input class="widefat" id="' . $this->get_field_id( 'rssimage' ) . '" name="' . $this->get_field_name( 'rssimage' ) . '" type="text" value="' . $rssimage . '" />';
		echo '</label>';

		echo '<b>' . __( 'Include these categories' ) . '</b><hr />';
		echo '<ul id="categorychecklist" class="list:category categorychecklist form-no-clear" style="list-style-type: none; margin-left: 5px; padding-left: 0px; margin-bottom: 20px;">';
		echo '<li id="' . $this->get_field_id( 'category--1' ) . '" class="popular-category">';
		echo '<label for="' . $this->get_field_id( 'post_category' ) . '" class="selectit">';
		echo '<input value="all" id="' . $this->get_field_id( 'post_category' ) . '" name="' . $this->get_field_name( 'post_category' ) . '[all]" type="checkbox" ' . $this->isChecked( false, $selected_cats ) . '>';
		_e( 'Include All Categories' );
		echo '</label>';
		echo '</li>';
		$this->avh_wp_category_checklist( 0, 0, $selected_cats, false, $this->number );
		echo '</ul>';
		echo '</p>';

		echo '<input type="hidden" id="' . $this->get_field_id( 'submit' ) . '" name="' . $this->get_field_name( 'submit' ) . '" value="1" />';
	}

	/**
	 * Used in forms to set the checked option.
	 *
	 * @param mixed $checked
	 * @param mixed_type $current
	 * @return string
	 *
	 * @since 2.0
	 */
	function isChecked ( $checked, $current )
	{
		if ( $checked == $current )
			return (' checked="checked"');
	}

	/**
	 * Creates the categories checklist
	 *
	 * @param int $post_id
	 * @param int $descendants_and_self
	 * @param array $selected_cats
	 * @param array $popular_cats
	 * @param int $number
	 */
	function avh_wp_category_checklist ( $post_id = 0, $descendants_and_self = 0, $selected_cats = false, $popular_cats = false, $number )
	{
		$walker = new AVH_Walker_Category_Checklist( );
		$walker->number = $number;
		$walker->input_id = $this->get_field_id( 'post_category' );
		$walker->input_name = $this->get_field_name( 'post_category' );
		$walker->li_id = $this->get_field_id( 'category--1' );

		$descendants_and_self = ( int ) $descendants_and_self;

		$args = array ();
		if ( is_array( $selected_cats ) )
			$args['selected_cats'] = $selected_cats;
		elseif ( $post_id )
			$args['selected_cats'] = wp_get_post_categories( $post_id );
		else
			$args['selected_cats'] = array ();

		if ( is_array( $popular_cats ) )
			$args['popular_cats'] = $popular_cats;
		else
			$args['popular_cats'] = get_terms( 'category', array ('fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false ) );

		if ( $descendants_and_self ) {
			$categories = get_categories( "child_of=$descendants_and_self&hierarchical=0&hide_empty=0" );
			$self = get_category( $descendants_and_self );
			array_unshift( $categories, $self );
		} else {
			$categories = get_categories( 'get=all' );
		}

		// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
		$checked_categories = array ();
		for ( $i = 0; isset( $categories[$i] ); $i ++ ) {
			if ( in_array( $categories[$i]->term_id, $args['selected_cats'] ) ) {
				$checked_categories[] = $categories[$i];
				unset( $categories[$i] );
			}
		}

		// Put checked cats on top
		echo call_user_func_array( array (&$walker, 'walk' ), array ($checked_categories, 0, $args ) );
		// Then the rest of them
		echo call_user_func_array( array (&$walker, 'walk' ), array ($categories, 0, $args ) );
	}
}

 /**
 * As the original wp_category_checklist doesn't support multiple lists on the same page I needed to duplicate the functions
 * use by the wp_category_checklist function
 *
 */
/**
 * Class that will display the categories
 *
 */
class AVH_Walker_Category_Checklist extends Walker
{
	var $tree_type = 'category';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id' ); //TODO: decouple this
	var $number;
	var $input_id;
	var $input_name;
	var $li_id;

	function start_lvl ( &$output, $depth, $args )
	{
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent<ul class='children'>\n";
	}

	function end_lvl ( &$output, $depth, $args )
	{
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}

	function start_el ( &$output, $category, $depth, $args )
	{
		extract( $args );
		$this->input_id = $this->input_id.'-'.$category->term_id;
		$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
		$output .= "\n<li id='$this->li_id'$class>" . '<label for="' . $this->input_id . '" class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="' . $this->input_name . '['.$category->term_id.']" id="' . $this->input_id . '"' . (in_array( $category->term_id, $selected_cats ) ? ' checked="checked"' : "") . '/> ' . wp_specialchars( apply_filters( 'the_category', $category->name ) ) . '</label>';
	}

	function end_el ( &$output, $category, $depth, $args )
	{
		$output .= "</li>\n";
	}
}

add_action('widgets_init', 'widget_avh_ec_normal_init');
function widget_avh_ec_normal_init() {
	register_widget('WP_Widget_AVH_ExtendedCategories_Normal');
}
?>