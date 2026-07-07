<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    shortcodes-finder
 * @subpackage shortcodes-finder/admin
 * @author     Scribit <wordpress@scribit.it>
 */
class Shortcodes_Finder_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     * @access   public
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     * @access   public
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Shortcodes_Finder_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Shortcodes_Finder_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page gate, no state change.
        if (isset($_GET['page']) && (sanitize_text_field(wp_unslash($_GET['page'])) == SHORTCODES_FINDER_PLUGIN_SLUG)) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/shortcodes-finder-admin.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     * @access   public
     */
    public function enqueue_scripts()
    {
        if (isset($_GET['page']) && ($_GET['page'] == SHORTCODES_FINDER_PLUGIN_SLUG)) {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/shortcodes-finder-admin.js', array('jquery'), $this->version, false);

            if (
                isset($_POST['subpage'], $_POST['_wpnonce']) &&
                wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), SHORTCODES_FINDER_NONCE_ACTION)
            ) {
                $subpage = sanitize_text_field(wp_unslash($_POST['subpage']));
                if (($subpage == 'find_content' || $subpage == 'find_unused') && isset($_POST['search_into_content'])) {
                    require_once plugin_dir_path(__FILE__) . '../includes/shortcodes-finder-utils.php';

                    $post_type = sanitize_text_field(wp_unslash($_POST['search_into_content']));
                    $include_not_published = (isset($_POST['include_not_published']) && (sanitize_text_field(wp_unslash($_POST['include_not_published'])) == 'on'));
                    $posts = shortcodes_finder_get_posts_ids($post_type, $include_not_published);    // Pass the post type

                    wp_localize_script(
                        $this->plugin_name,
                        'ajax_vars',
                        array(
                            'ajax_url' => admin_url('admin-ajax.php'),
                            'action' => $subpage,
                            'posts' => $posts,
                            'post_type' => $post_type,   // Mandatory for custom post types
                            'nonce' => wp_create_nonce(SHORTCODES_FINDER_NONCE_ACTION)
                        )
                    );
                }
            }
        }
    }

    /**
     * Define menu items for tools menu.
     *
     * @since    1.0.0
     * @access   public
     */
    public function management_page()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/shortcodes-finder-admin-display.php';

        add_management_page(
            __('Shortcodes Finder', 'shortcodes-finder'),
            __('Shortcodes Finder', 'shortcodes-finder'),
            'manage_options',
            SHORTCODES_FINDER_PLUGIN_SLUG,
            'shortcodes_finder_admin_page_handler'
        );
    }

    /**
     * Manage actions on plugin load
     *
     * @since    1.4.3
     * @access   public
     */
    public function load_plugin()
    {
        // Manage redirection after plugin activation
        // See Wordpress tip: https://developer.wordpress.org/reference/functions/register_activation_hook/
        if (is_admin() && get_option('activated_plugin') == SHORTCODES_FINDER_PLUGIN_SLUG) {
            delete_option('activated_plugin');
            wp_safe_redirect(esc_url(admin_url('tools.php?page=' . SHORTCODES_FINDER_PLUGIN_SLUG)));
            exit();
        }
    }

    /**
     * Manage ajax call for shortcodes search by content
     *
     * @since    1.6.2
     * @access   public
     */
    public function ajax_shortcodes_finder_content_search_process()
    {
        check_ajax_referer(SHORTCODES_FINDER_NONCE_ACTION, 'nonce');

        require_once plugin_dir_path(__FILE__) . 'partials/shortcodes-finder-admin-display.php';

        $post_type = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : '';
        $posts = isset($_POST['posts']) ? array_map('absint', (array) wp_unslash($_POST['posts'])) : array();

        $args = array(
            'posts_per_page' => -1,
            //'post_type' => 'any',
            'post_type' => $post_type,     // For custom post types I have to specify exact slug to retrieve with get_posts function.
            // If set to "any" the post with custom type will not be retrieved.
            'post_status' => 'any',
            'orderby' => 'date',
            'order' => 'DESC',
            'post__in' => $posts,
        );
        $posts = get_posts($args);

        shortcodes_finder_print_contents_shortcodes($posts);

        die;
    }

    /**
     * Manage ajax call for unused shortcodes search
     *
     * @since    1.6.2
     * @access   public
     */
    public function ajax_shortcodes_finder_unused_search_process()
    {
        check_ajax_referer(SHORTCODES_FINDER_NONCE_ACTION, 'nonce');

        require_once plugin_dir_path(__FILE__) . 'partials/shortcodes-finder-admin-display.php';

        $post_type = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : '';
        $posts = isset($_POST['posts']) ? array_map('absint', (array) wp_unslash($_POST['posts'])) : array();

        $args = array(
            'posts_per_page' => -1,
            //'post_type' => 'any',
            'post_type' => $post_type,     // For custom post types I have to specify exact slug to retrieve with get_posts function.
            // If set to "any" the post with custom type will not be retrieved.
            'post_status' => 'any',
            'orderby' => 'date',
            'order' => 'DESC',
            'post__in' => $posts
        );
        $posts = get_posts($args);

        shortcodes_finder_get_unused_shortcodes($posts);

        die;
    }

    /**
     * Manage admin notices for admin pages
     *
     * @since    1.6.2
     * @access   public
     */
    public function shortcodes_finder_admin_notices()
    {
        $current_page = get_current_screen()->base;
        if ('tools_page_shortcodes_finder' == $current_page) {
        }
    }
}
