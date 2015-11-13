<?php
/**
 * Comments 2 Reviews.
 *
 * @package   Comments 2 Reviews
 * @author    Thomas Lhotta <th.lhotta@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.github.com/thomaslhotta
 * @copyright 2013 Thomas Lhotta
 */

/**
 * A plugin that turn reviews into ratings.
 *
 * @package Comments_2_Reviews
 * @author  Thomas Lhotta <th.lhotta@gmail.com>
 */
class Comments_2_Reviews {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @var     string
	 */
	protected $version = '1.0.2';

	/**
	 * Unique identifier for your plugin.
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'comments2reviews';

	/**
	 * Instance of this class.
	 *
	 * @var      Comments_2_Reviews
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * An array of enabled post types.
	 *
	 * @var array|null
	 */
	protected $enabled_post_types = null;

	/**
	 * @var C2R_Settings
	 */
	protected $settings = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 */
	private function __construct() {
		require_once dirname( __FILE__ ) . '/class-c2r-query.php';
		new C2R_Query( $this->get_settings() );

		require_once dirname( __FILE__ ) . '/functions.php';

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// Add ratings field to comment.
		add_action( 'comment_form_logged_in_after', array( $this, 'add_rating_fields_to_comment' ) );

		add_action( 'comment_post', array( $this, 'save_comment_meta_data' ), 9, 2 );

		if ( is_admin() ) {
			// Update ratings on admin actions. This includes editing the post and editing comments.
			add_action( 'edit_post', array( $this, 'update_post_rating' ) );

			// Register admin  functions
			require_once dirname( __FILE__ ) . '/class-c2r-admin.php';
			new C2R_Admin( $this->get_settings() );
		}

		// Add the rating markup to the comment text on display
		add_filter( 'comment_text', array( $this, 'modify_comment' ), 1000 );

		// Add markup to comment author
		if ( ! is_admin() ) {
			add_filter( 'get_comment_author', array( $this, 'get_comment_author' ), 99 );
		}

		// Add a class to rated comments on display and injects microformat
		add_filter( 'comment_class', array( $this, 'add_comment_class' ), 9999, 3 );

		// Integrate width buddypress
		add_filter( 'bp_blogs_activity_new_comment_action', array(
			$this,
			'buddypress_rename_comment_activity',
		), 100, 2 );

		//@todo Needs fix, BuddyPress strips html attributes.
		add_filter(
			'bp_blogs_activity_new_comment_content',
			array(
				$this,
				'bp_blogs_activity_new_comment_content',
			),
			10,
			2
		);


		// Add myCRED hooks
		add_filter( 'mycred_setup_hooks', array( $this, 'mycred_hooks' ) );

		if ( class_exists( 'myCRED_Hook' ) ) {
			require_once dirname( __FILE__ ) . '/class-mycred-review.php';
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    Comments_2_Reviews    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {

		$domain = $this->get_settings()->get_plugin_slug();
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, true, basename( COMMENTS_2_REVIEWS_DIR ) . '/lang/' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 */
	public function enqueue_styles() {
		if ( ! in_array( get_post_type(), $this->get_settings()->get_enabled_post_types() ) ) {
			return;
		};

		wp_enqueue_style(
			$this->get_settings()->get_plugin_slug() . '-plugin-styles',
			WP_PLUGIN_URL . '/' . basename( COMMENTS_2_REVIEWS_DIR ) . '/css/public.css',
			array(),
			$this->version
		);
	}

	/**
	 * Adds the rating form fields to the comments field.
	 */
	public function add_rating_fields_to_comment() {
		$post = get_post();

		// Don't to anything if comments are not enabled for this post type.
		if ( ! in_array( $post->post_type, $this->get_settings()->get_enabled_post_types() ) ) {
			return;
		}

		$user = wp_get_current_user();

		// Find existing ratings
		$args = array(
			'post_id'    => $post->ID,
			'user_id'    => $user->ID,
			'count'      => false,
			'meta_query' => array(
				array(
					'key'     => 'rating',
					'compare' => 'EXISTS',
				),
			),
		);

		$query            = new WP_Comment_Query();
		$existing_ratings = $query->query( $args );

		if ( empty( $existing_ratings ) ) {
			do_action( 'c2r_add_rating_fields_to_comment' );
			include COMMENTS_2_REVIEWS_DIR . '/views/rating-selector.php';
		}
	}

	/**
	 * Returns the posts rating.
	 */
	public function get_post_rating( $id = null, $echo = true, $microdata = true ) {
		$post = get_post( $id );

		$rating_total = intval( get_post_meta( $post->ID, 'rating_total', true ) );
		$rating_count = intval( get_post_meta( $post->ID, 'rating_count', true ) );

		if ( 0 == $rating_count ) {
			if ( $echo ) {
				include( COMMENTS_2_REVIEWS_DIR . '/views/public-no-review.php' );
			}

			return 0;
		}

		$rating = floatval( get_post_meta( $post->ID, 'rating_mean', true ) );

		if ( $echo ) {
			include( COMMENTS_2_REVIEWS_DIR . '/views/public.php' );
		}
		return $rating;
	}

	/**
	 * Returns rating stats for the current post.
	 *
	 * @param string $id
	 * @param boolean $echo
	 *
	 * @return array
	 */
	public function get_post_rating_stats( $id = null, $echo = true ) {
		$post = get_post( $id );
		$id   = $post->ID;

		global $wpdb;

		$sql = sprintf(
			'SELECT m.meta_value AS rating, COUNT( meta_id ) AS reviews
			FROM %s AS m
			INNER JOIN %s AS c ON m.comment_id = c.comment_ID AND c.comment_post_ID = %d
			WHERE meta_key = \'rating\'
			GROUP BY meta_value
			ORDER BY meta_value ASC',
			$wpdb->commentmeta,
			$wpdb->comments,
			$id
		);

		$stats = array(
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
		);

		foreach ( $wpdb->get_results( $sql ) as $row ) {
			$stats[ intval( $row->rating ) ] = intval( $row->reviews );
		}

		if ( 0 === array_sum( $stats ) ) {
			return $stats;
		}

		if ( $echo ) {
			$rating_total = floatval( get_post_meta( $post->ID, 'rating_total', true ) );
			$rating = $rating_total;
			$rating_count = intval( get_post_meta( $post->ID, 'rating_count', true ) );

			include( COMMENTS_2_REVIEWS_DIR . '/views/stats.php' );
		}

		return $stats;
	}

	/**
	 * Updates the comment rating.
	 *
	 * @param integer $comment_id
	 * @param comment $status
	 */
	public function save_comment_meta_data( $comment_id, $status ) {
		if ( ( isset( $_POST['title'] ) ) && ( '' != $_POST['title'] ) ) {
			$title = wp_filter_nohtml_kses( $_POST['title'] );
			add_comment_meta( $comment_id, 'title', $title );
		}

		if ( ( isset( $_POST['rating'] ) ) && ( '' != $_POST['rating'] ) ) {
			$rating = wp_filter_nohtml_kses( $_POST['rating'] );
			$rating = intval( $rating );

			// Sanitize ratings
			if ( $rating > 5 ) {
				$rating = 5;
			}

			if ( $rating < 1 ) {
				$rating = 1;
			}

			add_comment_meta( $comment_id, 'rating', $rating );

			$comment = get_comment( $comment_id );

			$this->update_post_rating( $comment->comment_post_ID );
			do_action( 'comments2reviews_review', $comment_id, $status );
		}
	}

	/**
	 * Adds the class has-rating to the comment if is a rating commen.
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function add_comment_class( $classes, $class, $comment_id ) {
		if ( ! $this->comment_has_rating( $comment_id ) ) {
			return $classes;
		}

		$classes[] = 'review';

		// Inject micro format
		$classes[] = '" itemprop="review" itemscope itemtype="http://schema.org/Review';

		return $classes;
	}

	/**
	 * Adds itemprop to comment author.
	 *
	 * @param string $author
	 *
	 * @return string
	 */
	public function get_comment_author( $author ) {
		if ( $this->comment_has_rating() ) {
			$author = '<span itemprop="author">' . $author . '</span>';
		}

		return $author;
	}

	/**
	 * Adds the rating box to the comment
	 *
	 * @param string $text
	 *
	 * @return integer|object
	 */
	public function modify_comment( $text, $comment = null ) {
		// Sanitize comment object input
		if ( ! is_object( $comment ) ) {
			if ( is_array( $comment ) ) {
				$comment = $comment['comment_ID'];
			} else {
				$comment = array();
			}
			$comment = get_comment( $comment );
		}

		// No object found, do nothing.
		if ( ! is_object( $comment ) ) {
			return $text;
		}

		// Don't modify for child comments
		if ( 0 != $comment->comment_parent ) {
			return $text;
		}

		$rating = get_comment_meta( $comment->comment_ID, 'rating', true );

		// If no rating was found.
		if ( empty( $rating ) ) {
			return $text;
		}

		$title  = get_comment_meta( $comment->comment_ID, 'title', true );
		$author = $comment->comment_author;

		ob_start();

		include COMMENTS_2_REVIEWS_DIR . '/views/comment.php';

		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}

	/**
	 * Returns the rating value of the comment or null if the comment has no rating.
	 *
	 * @param int|object $id
	 *
	 * @return NULL|number
	 */
	public function get_comment_rating( $id, $echo = false ) {
		if ( is_object( $id ) ) {
			$id = $id->comment_ID;
		}

		$rating = get_comment_meta( $id, 'rating', true );

		if ( empty( $rating ) ) {
			return null;
		}

		if ( $echo ) {
			include dirname( dirname( __FILE__ ) ) . '/views/rating.php';
		}

		return intval( $rating );
	}

	/**
	 * Updates the rating of a post
	 *
	 * @param integer $post
	 */
	public function update_post_rating( $post ) {
		if ( ! $post instanceof WP_Post ) {
			$post = get_post( $post );
		}

		// Only do this for enabled post types.
		if ( ! in_array( $post->post_type, $this->get_settings()->get_enabled_post_types() ) ) {
			return;
		}

		$stats = $this->get_post_rating_stats( $post, false );

		$rating = 0;

		if ( 0 < $total = array_sum( $stats ) ) {
			$weighted = 0;

			foreach ( $stats as $number => $count ) {
				$weighted += $number * $count;
			}
			$rating = $weighted / $total;
		}

		update_post_meta( $post->ID, 'rating_count', $total );
		update_post_meta( $post->ID, 'rating_total', $rating );
	}

	/**
	 * Returns true if the given comment has a rating.
	 *
	 * @return boolean
	 */
	public function comment_has_rating( $id = null ) {
		if ( is_null( $id ) ) {
			$array = array();
			$id    = get_comment( $array );
		}

		if ( is_object( $id ) ) {
			$id = $id->comment_ID;
		}

		$return = get_comment_meta( $id, 'rating' );

		if ( empty( $return ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns a settings object.
	 *
	 * @return C2R_Settings
	 */
	public function get_settings() {
		if ( $this->settings ) {
			return $this->settings;
		}

		if ( ! class_exists( 'C2R_Settings' ) ) {
			require_once dirname( __FILE__ ) . '/class-c2r-settings.php';
		}

		$this->settings = new C2R_Settings();

		return $this->settings;
	}

	/**
	 * Changes the activity string in BuddyPress
	 *
	 * @param string $activity_action
	 * @param $recorded_comment
	 *
	 * @return string
	 */
	public function buddypress_rename_comment_activity( $activity_action, $recorded_comment ) {
		if ( 0 !== intval( $recorded_comment->comment_parent ) ) {
			return $this->review_answer_activity( $activity_action, $recorded_comment );
		}

		// Return if comment has no rating.
		if ( ! $this->comment_has_rating( $recorded_comment ) ) {
			return $activity_action;
		}

		$user           = get_user_by( 'email', $recorded_comment->comment_author_email );
		$user_id        = (int) $user->ID;
		$post_permalink = get_permalink( $recorded_comment->comment_post_ID );
		$blog_id        = get_current_blog_id();
		$comment_post   = get_post( $recorded_comment->comment_post_ID );
		if ( ! $comment_post instanceof WP_Post ) {
			return $activity_action;
		}

		$plugin_slug = $this->get_settings()->get_plugin_slug();

		$single = __( '%1$s posted a review on %2$s', $plugin_slug );
		$multi  = __( 'on the site %1$s', $plugin_slug );

		$string = sprintf(
			$single,
			bp_core_get_userlink( $user_id ),
			'<a href="' . $post_permalink . '">' . apply_filters( 'the_title', $comment_post->post_title ) . '</a>'
		);

		if ( is_multisite() && 1 !== get_current_blog_id() ) {
			$string .= ',' . sprintf(
				$multi,
				'<a href="' . get_blog_option( $blog_id, 'home' ) . '">' . get_blog_option( $blog_id, 'blogname' ) . '</a>'
			);
		}

		return $string;
	}

	protected function review_answer_activity( $activity_action, $recorded_comment ) {
		$parent = get_comment( $recorded_comment->comment_parent );

		if ( ! $this->comment_has_rating( $parent ) ) {
			// Parent was no review, don't rename
			return $activity_action;
		}

		$plugin_slug = $this->get_settings()->get_plugin_slug();
		$blog_id     = get_current_blog_id();

		$single = __( '%1$s commented on %2$s\'s review on %3$s', $plugin_slug );
		$multi  = __( 'on the site %1$s', $plugin_slug );

		$post_id = intval( $recorded_comment->comment_post_ID );
		$post    = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return $activity_action;
		}

		$post_permalink = get_post_permalink( $post_id );

		$string = sprintf(
			$single,
			bp_core_get_userlink( $recorded_comment->user_id ),
			bp_core_get_userlink( $parent->user_id ),
			'<a href="' . $post_permalink . '">' . get_the_title( $post_id ) . '</a>'
		);

		if ( is_multisite() && 1 !== get_current_blog_id() ) {
			$string .= ',' . sprintf(
				$multi,
				'<a href="' . get_blog_option( $blog_id, 'home' ) . '">' . get_blog_option( $blog_id, 'blogname' ) . '</a>'
			);
		}

		return $string;
	}

	/**
	 * Includes the rating in the BuddyPress activity
	 *
	 * @param string $activity_content
	 * @param object $recorded_comment
	 * @param string|boolean $is_approved
	 *
	 * @return string
	 */
	public function bp_blogs_activity_new_comment_content( $activity_content, $recorded_comment, $is_approved = true ) {
		// Return if comment has no rating.
		if ( ! $this->comment_has_rating( $recorded_comment ) ) {
			return $activity_content;
		}

		$activity_content = bp_activity_filter_kses( $activity_content );

		// Ensure that ? what
		remove_filter( 'bp_activity_content_before_save', 'bp_activity_filter_kses', 1 );

		$rating = get_comment_meta( $recorded_comment->comment_ID, 'rating', true );

		$title = get_comment_meta( $recorded_comment->comment_ID, 'title', true );

		if ( ! empty( $title ) ) {
			$activity_content = $title;
		}

		get_post_thumbnail_id();

		ob_start();
		include COMMENTS_2_REVIEWS_DIR . '/views/rating.php';
		$rating = ob_get_contents();
		ob_end_clean();

		$activity_content = $rating . get_the_post_thumbnail( $recorded_comment->comment_post_ID, 'thumbnail' ) . $activity_content;

		// Remove stupid markup that BuddyPress adds to image
		add_filter(
			'bp_activity_thumbnail_content_images',
			function( $content ) use ( $activity_content ) {
				return $activity_content;
			}
		);

		return $activity_content;
	}

	/**
	 * Adds MyCRED hooks
	 *
	 * @param array $hooks
	 *
	 * @return array
	 */
	public function mycred_hooks( $hooks ) {
		$slug = Comments_2_Reviews::get_instance()->get_settings()->get_plugin_slug();

		$hooks['comments2reviews_review'] = array(
			'title'       => __( '%plural% for creating a review', $slug ),
			'description' => __( 'Triggered when a user creates a review.', $slug ),
			'callback'    => array( 'Mycred_Review' ),
		);

		return $hooks;
	}

	/**
	 * @deprecated
	 */
	public function __( $text ) {
		return __( $text, $this->get_settings()->get_plugin_slug() );
	}
}
