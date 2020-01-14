<?php
/*
  Plugin Name: Odds Calculator
  Plugin URI:
  Description: Odds Calculator
  Author: Alberto Belotti
  Version: 1.0.0
  Author URI:
  Text Domain: odds-calculator
 */

namespace oddsCalculator;

defined('ABSPATH') or die('No script kiddies please!');

include_once dirname(__FILE__) . '/config.php';
include_once dirname(__FILE__) . '/Service/Service.php';
include_once dirname(__FILE__) . '/Oddslatest.php';

use oddsCalculator\Service\Service as Service;
use oddsCalculator\Oddslatest as Oddslatest;

Class Odds {

    private $plugin_dir_url;
    
    public function __construct() {
        
        $this->plugin_dir_url = plugin_dir_url(__FILE__);
        $this->currentDir = dirname(__FILE__);
        $this->addActions();
        $this->registerApiEndpoints();
        add_action('wp_enqueue_scripts', array($this, 'registerScripts'));
        $this->registerStyles();
    }
    
    /**
     * Registering all the endpoints.
     */
    private function registerApiEndpoints(){
        
        add_action('rest_api_init', function () {
            
            register_rest_route('odds/v1', '/getOdds', array(
                'methods' => 'GET',
                'callback' => function ($json) {
                    return Service::getOdds();
                }
            ));
            
            register_rest_route('odds/v1', '/getRemoteOdds', array(
                'methods' => 'GET',
                'callback' => function () {
                    return Service::getRemote("odds", $force_update);
                }
            ));
            
            register_rest_route('odds/v1', '/getRemoteSports', array(
                'methods' => 'GET',
                'callback' => function () {
                    return Service::getRemote("sports",$force_update);
                }
            ));
            
            register_rest_route('odds/v1', '/getLatestOdds', array(
                'methods' => 'GET',
                'callback' => function () {
                    return Service::getLatestOdds();
                }
            ));
            
            register_rest_route('odds/v1', '/getChoices', array(
                'methods' => 'GET',
                'callback' => function () {
                    return Service::getChoices();
                }
            ));
        });
    }
    
    /**
     * 
     */
    function registerScripts() {
        
        $handle = 'odds_handle';
        wp_register_script($handle, $this->plugin_dir_url . "js/bootstrap.min.js", array('jquery'), null, true);
        wp_enqueue_script($handle);
        
        $handle = 'odds_script_handle';
        wp_register_script($handle, $this->plugin_dir_url . "js/scripts.js", array('jquery'), null, true);
        $params = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'odds_nonce' => wp_create_nonce('calculate_odds')
        );
        wp_localize_script($handle, 'ajax_object', $params);
        wp_enqueue_script($handle);

        $handleDatatable = 'odds_datatables_handle';
        wp_register_script($handleDatatable, $this->plugin_dir_url . "libraries/datatable/dt-1.10.18/datatables.min.js", array('jquery'), null, true);
        wp_enqueue_script($handleDatatable);
        
        $handleBootstrap = 'odds_datatables_bootstrap_handle';
        wp_register_script($handleBootstrap, $this->plugin_dir_url . "js/DataTables/js/dataTables.bootstrap4.js", array('jquery'), null, true);
        wp_enqueue_script($handleBootstrap);
        
        $handleResponsive = 'odds_datatables_responsive';
        wp_register_script($handleResponsive, $this->plugin_dir_url . "js/datatableResponsive.js", array('jquery'), null, true);
        wp_enqueue_script($handleResponsive);
    }
    
    /**
     * 
     */
    private function registerStyles() {
        wp_register_style('custom_wp_admin_css', $this->plugin_dir_url . 'css/bootstrap.min.css', false, '1.0.0' );
        wp_register_style('custom_wp_admin_datatable_bootstrap_css', $this->plugin_dir_url . 'js/DataTables/css/dataTables.bootstrap.css', false, '1.0.0' );
        wp_register_style('custom_wp_admin_custom_css', $this->plugin_dir_url . 'css/odds.css', false, '1.0.0' );
        wp_register_style('custom_wp_datatable_css', $this->plugin_dir_url . 'css/datatable.min.css', false, '1.0.0' );
        wp_register_style('custom_wp_admin_datatable_css', $this->plugin_dir_url . '/libraries/datatable/dt-1.10.18/datatables.min.css', false, '1.10.18' );

        wp_enqueue_style('custom_wp_admin_css');
        wp_enqueue_style('custom_wp_admin_custom_css');
        wp_enqueue_style('custom_wp_admin_datatable_bootstrap_css');
        wp_enqueue_style('custom_wp_datatable_css');
        wp_enqueue_style('custom_wp_admin_datatable_css');
    }
    
    /**
     * 
     */
    private function addActions() {
        $service = new Service;
        add_action('admin_enqueue_scripts',array($this, 'registerScripts'));
        add_action('wp_ajax_getOdds', array($service, 'getOdds'));
        add_action('wp_ajax_getChoices', array($service, 'getChoices'));
        add_action('wp_ajax_getLatest', array($service, 'getLatest'));
        add_action('wp_ajax_getRemote', array($service, 'getRemote'));
    }
    
     /**
     * Initialize plugin. 
     * Creating mandatory tables
     * @return type
     */
    public static function initPlugin() {
        global $wpdb;
        $table_name = "wp_odds_latest";
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
        if (!$wpdb->get_var($query) == $table_name) {
            $this->createOddsLatest();
        }
    }
    /**
     * This method creates the custom tables for latest odds
     * @global type $wpdb
     */
    public function createOddsLatest() {
        global $wpdb;
        $oddsLatest = new Oddslatest();
        $oddsLatest::createTable();
    }
    
    
}

$odds = new Odds;

// I SET THIS IN THE FOOTER ONLY FOR EASIER DEBUGGING
// IN LIVE I WOULD CREATE WIDGETS INSTEAD AND SHORTCODES. 
add_action('wp_footer', function(){
    $service = new Service();
    $service->getTemplate("sports");
    $service->getTemplate("odds");
    $service->getTemplate("latest");
});

register_activation_hook(__FILE__, array($odds, 'initPlugin'));

