<?php

/**
 * Copyright (C) 2020. Drew Gauderman
 *
 * This source code is licensed under the MIT license found in the
 * README.md file in the root directory of this source tree.
 */

/**
 *
 * @link              https://dpg.host
 * @since             1.0.0
 * @package           wordpress_plugin
 * @author            Drew Gauderman <drew@dpg.host>
 * @copyright         2018-2020 Drew Gauderman.
 *
 * @wordpress-plugin
 * Plugin Name:       Front-End Monitor
 * Plugin URI:        https://fortembr.com/products/wordpress-plugin-front-end-monitor
 * Description:       This plugin helps you remove unused plugins without affecting the front-end of your website.
 * Version:           1.0.0
 * Author:            Fort Embr
 * Author URI:        https://fortembr.com
 * Text Domain:       wordpress_plugin
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 */

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
add_action('plugins_loaded', function () {
    // dynamically load classes from the "classes" folder.
    foreach (glob(plugin_dir_path(__FILE__) . "/classes/*.php") as $filename) {
        include($filename);
    }
});

/**
 * wordpress_plugin_base. other classes extend this one.
 *
 * @author	Drew Gauderman <drew@dpg.host>
 * @since	v1.0.0
 * @global
 */
class wordpress_plugin_base
{
    /**
     * @since	v1.0.0
     * @var		string	$name
     * @access	protected
     */
    protected $name;

    /**
     * @since	v1.0.0
     * @var		string	$version
     * @access	protected
     */
    protected $version;

    /**
     * @since	v1.0.0
     * @var		string	$version
     * @access	protected
     */
    protected $url;

    /**
     * @since	v1.0.0
     * @var		string	$version
     * @access	protected
     */
    protected $path;

    /**
     * __construct
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Wednesday, October 24th, 2018.
     * @access	public
     * @param	string	$name   	Default: 'base_class'
     * @param	string	$version	Optional. Default: '1.0.0'
     * @return	void
     */
    public function __construct($name = 'base_class', $version = '1.0.0')
    {
        global $wp_filter;

        $this->name = $name;
        $this->version = $version;
        $this->url = plugin_dir_url(__FILE__);
        $this->path = plugin_dir_path(__FILE__);

        //debug mode enabled
        if ($this->is_debug()) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

            // disable redirect for development
            remove_filter('template_redirect', 'redirect_canonical');
        }

        // for more simple classes that dont want to use __construct
        if (method_exists($this, 'init')) {
            $this->add('filter', 'init', [$this, 'init'], 10, 0);
        }

        $ignore = ['add', 'filter', 'action', 'remove_action', 'remove_filter', 'run', '__construct'];

        //register the class functions as possible call actions
        foreach (get_class_methods($this) as $method_name) {
            if (in_array($method_name, $ignore)) {
                continue;
            }

            $reflection = new ReflectionMethod(get_class($this), $method_name);
            $args = $reflection->getNumberOfParameters();

            foreach ($wp_filter as $name => $value) {
                if (strpos($method_name, $name) !== false) {
                    $this->action($name, $method_name, 10, $args);

                    break;
                }
            }

            $this->action($method_name, $method_name, 10, $args);
        }
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Sunday, October 21st, 2018.
     * @access	public
     * @param	string 	$hook         	The name of the WordPress action that is being registered.
     * @param	mixed  	$callback     	The name of the function definition on the $component.
     * @param	integer	$priority     	Optional. The priority at which the function should be fired. Default is 10.
     * @param	integer	$accepted_args	Optional. The number of arguments that should be passed to the $callback. Default is 0 .
     * @return	void
     */
    public function action($hook = '', $callback, $priority = 10, $accepted_args = false)
    {
        if ($accepted_args === false) {
            if (is_array($callback)) {
                $fct = new ReflectionMethod($callback[0], $callback[1]);
                $accepted_args = $fct->getNumberOfRequiredParameters();
            } else {
                $fct = new ReflectionFunction('client_func');
                $accepted_args = $fct->getNumberOfRequiredParameters();
            }
        }

        $this->add('action', $hook, $callback, $priority, $accepted_args);
    }

    /**
     * filter
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Monday, October 22nd, 2018.
     * @access	public
     * @param	string 	$hook         	Default: ''
     * @param	mixed  	$callback		string of function name, or function
     * @param	integer	$priority     	Default: 10
     * @param	integer	$accepted_args
     * @return	void
     */
    public function filter($hook = '', $callback, $priority = 10, $accepted_args = false)
    {
        if ($accepted_args === false) {
            if (is_array($callback)) {
                $fct = new ReflectionMethod($callback[0], $callback[1]);
                $accepted_args = $fct->getNumberOfRequiredParameters();
            } else {
                $fct = new ReflectionFunction('client_func');
                $accepted_args = $fct->getNumberOfRequiredParameters();
            }
        }

        $this->add('filter', $hook, $callback, $priority, $accepted_args);
    }

    /**
     * add_shortcode
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Monday, October 22nd, 2018.
     * @access	public
     * @param	string	$tag
     * @param	mixed 	$callback
     * @return	void
     */
    public function add_shortcode($tag = '', $callback)
    {
        $this->add('shortcode', $tag, $callback);
    }

    /**
     * remove_action
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Monday, October 22nd, 2018.
     * @access	public
     * @param	string	$tag
     * @param	mixed 	$callback
     * @return	void
     */
    public function remove_action($tag = '', $callback, $priority = 10)
    {
        $this->add('remove_action', $tag, $callback, $priority);
    }

    /**
     * remove_filter
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Monday, October 22nd, 2018.
     * @access	public
     * @param	string	$tag     	Default: ''
     * @param	mixed 	$callback
     * @return	void
     */
    public function remove_filter($tag = '', $callback, $priority = 10)
    {
        $this->add('remove_filter', $tag, $callback, $priority);
    }

    /**
     * add
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Monday, October 22nd, 2018.
     * @access	protected
     * @param	string	$type
     * @param	string	$hook
     * @param	mixed 	$callback
     * @param	number	$priority     	Optional. Default: 10
     * @param	number	$accepted_args	Optional. Default: 1
     * @return	void
     */
    protected function add($type = '', $hook = '', $callback, $priority = 10, $accepted_args = 1)
    {
        $action = [
            'type' => $type,
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        ];

        if (!in_array($type, ['remove_filter', 'remove_action']) && is_string($callback) && method_exists($this, $callback)) {
            $callback = [$this, $callback];
        }

        switch ($type) {
                //register filter or action
            case 'filter':
            case 'action':
                add_filter($hook, $callback, $priority, $accepted_args);
                break;

                //register shortcode
            case 'shortcode':
                add_shortcode($hook, $callback);
                break;

                //remove filter or action
            case 'remove_filter':
            case 'remove_action':
                remove_filter($hook, $callback, $priority);
                break;
        }
    }

    /**
     * Returns true if Debugging is Enabled.
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @access	protected
     * @return	mixed
     */
    protected function is_debug()
    {
        return ($_SERVER['REMOTE_ADDR'] == '172.32.0.1');
    }

    /**
     * get_template
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Wednesday, October 24th, 2018.
     * @access	protected
     * @param	string	$fileName	Optional. Default: ''
     * @param	mixed 	$arg     	Optional. Default: null
     * @return	void
     */
    protected function get_template($fileName = '', $arg = null)
    {
        include($this->path . "template-parts/$fileName.php");
    }

    /**
     * get_asset_img_path
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Monday, October 22nd, 2018.
     * @access	protected
     * @param	string	$fileName
     * @return	string
     */
    protected function get_asset_img_path($fileName = '')
    {
        return $this->path . "assets/img/$fileName";
    }

    /**
     * get_asset_css_url
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Monday, October 22nd, 2018.
     * @access	protected
     * @param	string	$fileName
     * @return	string
     */
    protected function get_asset_css_url($fileName = '')
    {
        return $this->url . "assets/css/$fileName";
    }

    /**
     * get_asset_css_version.
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.30
     * @version	v1.0.0	Tuesday, January 8th, 2019.
     * @access	protected
     * @param	string	$fileName	Default: ''
     * @return	mixed
     */
    protected function get_asset_css_version($fileName = '')
    {
        return @filemtime($this->path . "assets/css/$fileName");
    }

    /**
     * get_asset_js_version.
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.30
     * @version	v1.0.0	Tuesday, January 8th, 2019.
     * @access	protected
     * @param	string	$fileName	Default: ''
     * @return	mixed
     */
    protected function get_asset_js_version($fileName = '')
    {
        return @filemtime($this->path . "assets/js/$fileName");
    }

    /**
     * get_asset_js_url
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Monday, October 22nd, 2018.
     * @access	protected
     * @param	string	$fileName
     * @return	string
     */
    protected function get_asset_js_url($fileName = '')
    {
        return $this->url . "assets/js/$fileName";
    }

    /**
     * set_transient
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Wednesday, October 31st, 2018.
     * @access	public
     * @param	string	$name 	Default: ''
     * @param	mixed 	$value
     * @return	void
     */
    public function set_transient($name = '', $value)
    {
        set_transient($this->name . $name, ['version' => $this->version, 'value' => $value]);
    }

    /**
     * get_transient
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Wednesday, October 31st, 2018.
     * @access	public
     * @param	string	$name 	Default: ''
     * @param	mixed 	$value
     * @return	mixed
     */
    public function get_transient($name = '')
    {
        $trans = get_transient($this->name . $name);

        //received an update, so need to purge the trans
        if ((!empty($trans) && $trans['version'] != $this->version) || isset($_GET['cookieclear'])) {
            delete_transient($this->name . $name);
            return; //not getting anything
        }

        return $trans['value'] ?? null;
    }

    /**
     * delete_transient
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Wednesday, October 31st, 2018.
     * @access	public
     * @param	string	$name	Default: ''
     * @return	void
     */
    public function delete_transient($name = '')
    {
        delete_transient($this->name . $name);
    }

    /**
     * base64url_encode
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Friday, November 2nd, 2018.
     * @access	public
     * @param	string	$data
     * @return	string
     */
    public function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * base64url_decode
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Friday, November 2nd, 2018.
     * @access	public
     * @param	string	$data
     * @return	string
     */
    public function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * register_nav_menus.
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Wednesday, April 29th, 2020.
     * @access	public
     * @param	array	$data	Default: []
     * @return	void
     */
    public function register_nav_menus($data = [])
    {
        $this->action('after_setup_theme', function () use ($data) {
            register_nav_menus($data);
        });
    }

    /**
     * wp_enqueue_style.
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Wednesday, April 29th, 2020.
     * @access	public
     * @param	mixed	$file
     * @return	void
     */
    public function wp_enqueue_style($name, $file)
    {
        wp_enqueue_style($name, $this->get_asset_css_url($file), [], $this->get_asset_css_version($file));
    }

    /**
     * wp_enqueue_script.
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Wednesday, April 29th, 2020.
     * @access	public
     * @param	mixed	$name
     * @param	mixed	$file
     * @param	array	$depends	Default: []
     * @return	void
     */
    public function wp_enqueue_script($name, $file, $depends = [])
    {
        wp_deregister_script($name);
        wp_enqueue_script($name, $this->get_asset_js_url($file), $depends, $this->get_asset_js_version($file), true);
        wp_enqueue_script($name);
    }

    /**
     * wp_register_script.
     *
     * @author	Drew Gauderman <drew@dpg.host>
     * @since	v1.0.0
     * @version	v1.0.0	Friday, May 1st, 2020.
     * @access	public
     * @param	string	$name
     * @param	string	$file
     * @param	array	$depends 	Default: []
     * @return	void
     */
    public function wp_register_script($name, $file, $depends = [], $localize = false, $localizeArray = [])
    {
        // make sure its deregistered
        wp_deregister_script($name);

        // load javascript
        wp_register_script($name, $this->get_asset_js_url($file), $depends, $this->get_asset_js_version($file), true);

        if ($localize) {
            wp_localize_script($this->name, $localize, $localizeArray);
        }

        // Enqueue our script
        wp_enqueue_script($this->name);
    }

    /**
     * gets the current post type in the WordPress Admin
     *
     * @author  Brad Vincent
     * @author	Domenic Fiore
     * @since	v1.0.0
     * @see     https://gist.github.com/bradvin/1980309
     * @see     https://gist.github.com/DomenicF/3ebcf7d53ce3182854716c4d8f1ab2e2
     * @return	string
     */
    public function get_current_post_type()
    {
        global $post, $typenow, $current_screen;

        //we have a post so we can just get the post type from that
        if ($post && $post->post_type) {
            return $post->post_type;
        }

        //check the global $typenow - set in admin.php
        elseif ($typenow) {
            return $typenow;
        }

        //check the global $current_screen object - set in sceen.php
        elseif ($current_screen && $current_screen->post_type) {
            return $current_screen->post_type;
        }

        //check the post_type querystring
        elseif (isset($_REQUEST['post_type'])) {
            return sanitize_key($_REQUEST['post_type']);
        }

        //lastly check if post ID is in query string
        elseif (isset($_REQUEST['post'])) {
            return get_post_type($_REQUEST['post']);
        }

        //we do not know the post type!
        return null;
    }
}

/*--- 
Setup Custom Endpoint
---*/


add_action('rest_api_init', function () {
    register_rest_route('front-end-monitor/v1', '/get-plugins', array(
        'methods' => 'GET',
        'callback' => 'get_front_end_plugin_list',
    ));
    register_rest_route('front-end-monitor/v1', '/get-enqueued-assets', array(
        'methods' => 'GET',
        'callback' => 'check_for_plugin_enqueued_assets',
        'args' => array(
            'slug' => array(
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_string($param);
                }
            ),
        ),
    ));
});
function get_front_end_plugin_list() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins');

    $formatted_plugins = array_map(function($plugin_path, $plugin_data) use ($active_plugins) {
        // Check if the plugin is in the list of active plugins
        $isActive = in_array($plugin_path, $active_plugins);
        return array_merge($plugin_data, ['isActive' => $isActive]);
    }, array_keys($all_plugins), $all_plugins);

    return new WP_REST_Response($formatted_plugins, 200);
}
// endpoint fails
function check_for_plugin_enqueued_assets(WP_REST_Request $request) {
    $plugin_slug = $request->get_param('slug');
    $hasEnqueuedAssets = false;
    // Check if any of the plugin's scripts are enqueued
    foreach ($plugin_slug as $script_handle) {
        if (wp_script_is($script_handle, 'enqueued')) {
            $hasEnqueuedAssets = true;
            break;
        }
    }
    // Check if any of the plugin's styles are enqueued
    foreach ($plugin_slug as $style_handle) {
        if (wp_style_is($style_handle, 'enqueued')) {
            $hasEnqueuedAssets = true;
            break;
        }
    }
    // Store the result
    update_option('hasEnqueuedAssets', $hasEnqueuedAssets);
}
// need endpoints
function check_for_plugin_widgets($plugin_handle) {
    $hasWidgets = false;

    // Get all active widgets
    $all_widgets = wp_get_sidebars_widgets();
    unset($all_widgets['wp_inactive_widgets']); // Exclude inactive widgets

    // Iterate through each widget area's widgets
    foreach ($all_widgets as $sidebar => $widgets) {
        if (is_array($widgets)) {
            foreach ($widgets as $widget_id) {
                // Get the widget's option name
                $option_name = 'widget_' . _get_widget_id_base($widget_id);

                // Get widget instances
                $widget_instances = get_option($option_name);
                if ($widget_instances) {
                    foreach ($widget_instances as $instance) {
                        // Skip if it's not an array or is the multiwidget index
                        if (!is_array($instance) || isset($instance['_multiwidget'])) {
                            continue;
                        }

                        // Check if the widget class name contains the plugin handle
                        $widget_class = get_class($GLOBALS['wp_widget_factory']->widgets[_get_widget_id_base($widget_id)]);
                        if (strpos(strtolower($widget_class), strtolower($plugin_handle)) !== false) {
                            $hasWidgets = true;
                            break 3; // Break out of all loops
                        }
                    }
                }
            }
        }
    }

    // Store the result
    update_option('hasWidgets_' . $plugin_handle, $hasWidgets);
}
function check_database_queries_start() {
    global $wpdb;
    $wpdb->queries = []; // Reset the queries array to start fresh
}
function check_database_queries_end($plugin_text_domain) {
    global $wpdb;
    $hasDatabaseQueries = false;

    if (isset($wpdb->queries) && is_array($wpdb->queries)) {
        foreach ($wpdb->queries as $query) {
            // Check if the SQL query contains the text domain of the plugin
            if (strpos(strtolower($query[0]), strtolower($plugin_text_domain)) !== false) {
                $hasDatabaseQueries = true;
                break;
            }
        }
    }

    update_option('hasDatabaseQueries_' . $plugin_text_domain, $hasDatabaseQueries);
}
function start_monitoring_api_requests($plugin_text_domain) {
    add_filter('http_api_debug', function($response, $context, $class, $args, $url) use ($plugin_text_domain) {
        check_plugin_api_requests($response, $context, $class, $args, $url, $plugin_text_domain);
    }, 10, 5);
}
function check_plugin_api_requests($response, $context, $class, $args, $url, $plugin_text_domain) {
    static $hasAPIRequests = false;

    // Attempt to form a pattern from the plugin text domain
    // This is heuristic and might not be accurate
    $pattern = '/' . str_replace('-', '.', $plugin_text_domain) . '/';

    if (preg_match($pattern, $url)) {
        $hasAPIRequests = true;
    }

    // Store the result (optimization needed as this will execute multiple times)
    update_option('hasAPIRequests_' . $plugin_text_domain, $hasAPIRequests);
}
function stop_monitoring_api_requests() {
    remove_all_filters('http_api_debug');
}
function enqueue_dom_inspection_script() {
    wp_enqueue_script('dom-inspection-script', get_template_directory_uri() . '/js/dom-inspection.js', array('jquery'), null, true);
    wp_localize_script('dom-inspection-script', 'PluginDOMSettings', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'element_identifier' => '.plugin-specific-class' // Replace with the actual identifier (class, ID, etc.)
    ));
}
function handle_dom_inspection_report() {
    $hasDOMElements = isset($_POST['hasDOMElements']) ? (bool) $_POST['hasDOMElements'] : false;
    update_option('hasDOMElements', $hasDOMElements);
    wp_die(); // Terminate the script
}
function check_posts_for_plugin_url_params($plugin_text_domain) {
    $hasURLParams = false;

    // Create a pattern to search in content
    $pattern = '/https?:\/\/[^\s]*\b' . preg_quote($plugin_text_domain, '/') . '\b/';

    // Query arguments to get all posts of all post types
    $args = array(
        'post_type'      => 'any',
        'post_status'    => 'any',
        'posts_per_page' => -1,
    );

    // Execute the query
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $content = get_the_content();

            // Search the content for URL-like strings containing the text domain
            if (preg_match($pattern, $content)) {
                $hasURLParams = true;
                break; // Stop the loop if we find a match
            }
        }

        // Reset post data to restore original query
        wp_reset_postdata();
    }

    // Store the result
    update_option('hasURLParamsInPosts_' . $plugin_text_domain, $hasURLParams);
}
function check_for_plugin_hooks($plugin_text_domain) {
    $hasFilterActionHooks = false;

    // Generate a list of potential hook names based on the text domain
    // This is a guessing approach and might not be accurate
    $potential_hooks = [
        "init_{$plugin_text_domain}",
        "{$plugin_text_domain}_loaded",
        "{$plugin_text_domain}_enqueue_scripts",
        // Add more patterns as needed
    ];

    // Check each hook for callbacks
    foreach ($potential_hooks as $hook) {
        if (has_action($hook) || has_filter($hook)) {
            $hasFilterActionHooks = true;
            break; // Break if any hook is found
        }
    }

    // Store the result
    update_option('hasFilterActionHooks_' . $plugin_text_domain, $hasFilterActionHooks);
}
function start_template_check() {
    add_filter('template_include', 'check_custom_template', 99);
    add_filter('stylesheet_directory', 'check_custom_stylesheet_directory', 99);
}
function check_custom_template($template) {
    // Check if the template is different from the default template for the current query
    $default_template = locate_template(get_query_template(get_query_var('post_type')));
    if ($default_template !== $template) {
        update_option('hasCustomTemplates', true);
    }
    return $template;
}
function check_custom_stylesheet_directory($stylesheet_dir) {
    // Check if the stylesheet directory is different from the current theme's directory
    if (get_template_directory() !== $stylesheet_dir) {
        update_option('hasCustomTemplates', true);
    }
    return $stylesheet_dir;
}
function check_all_posts_for_shortcodes($plugin_text_domain) {
    $hasShortcodes = false;

    // Assuming the shortcodes are prefixed with the text domain
    $shortcode_pattern = get_shortcode_regex(array($plugin_text_domain . '_*'));

    // Query all posts
    $args = array(
        'post_type'      => 'any',
        'post_status'    => 'any',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $content = get_the_content();

            // Search for shortcodes in the post content
            if (preg_match_all('/' . $shortcode_pattern . '/', $content, $matches)
                && array_key_exists(2, $matches)
                && !empty($matches[2])) {
                $hasShortcodes = true;
                break; // Break the loop if a shortcode is found
            }
        }

        // Reset post data
        wp_reset_postdata();
    }

    // Store the result
    update_option('hasShortcodes_' . $plugin_text_domain, $hasShortcodes);
}
function check_for_plugin_custom_post_types($plugin_text_domain) {
    $hasCustomPostTypes = false;

    // Get all registered post types
    $post_types = get_post_types(array('_builtin' => false), 'names');

    // Look for post types that might be associated with the plugin
    foreach ($post_types as $post_type) {
        if (strpos($post_type, $plugin_text_domain) !== false) {
            $hasCustomPostTypes = true;
            break;
        }
    }

    // Store the result
    update_option('hasCustomPostTypes_' . $plugin_text_domain, $hasCustomPostTypes);
}
function start_meta_box_monitoring() {
    global $wp_meta_boxes;
    $wp_meta_boxes = array(); // Reset meta boxes to start fresh monitoring
}
function check_for_plugin_meta_boxes($plugin_text_domain) {
    global $wp_meta_boxes;
    $hasMetaBoxes = false;

    // Check each context and priority for meta boxes
    foreach ($wp_meta_boxes as $screen => $contexts) {
        foreach ($contexts as $context => $priorities) {
            foreach ($priorities as $priority => $meta_boxes) {
                foreach ($meta_boxes as $meta_box) {
                    if (strpos($meta_box['id'], $plugin_text_domain) !== false) {
                        $hasMetaBoxes = true;
                        break 4; // Break out of all loops
                    }
                }
            }
        }
    }

    // Store the result
    update_option('hasMetaBoxes_' . $plugin_text_domain, $hasMetaBoxes);
}
function check_for_plugin_custom_fields($plugin_text_domain) {
    $hasCustomFields = false;

    // Query all posts
    $args = array(
        'post_type'      => 'any',
        'post_status'    => 'any',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // Get all custom fields for the post
            $custom_fields = get_post_meta($post_id);

            // Check if any custom field key contains the text domain slug
            foreach (array_keys($custom_fields) as $key) {
                if (strpos($key, $plugin_text_domain) !== false) {
                    $hasCustomFields = true;
                    break 2; // Break out of both loops
                }
            }
        }

        // Reset post data
        wp_reset_postdata();
    }

    // Store the result
    update_option('hasCustomFields_' . $plugin_text_domain, $hasCustomFields);
}