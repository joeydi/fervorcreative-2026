<?php

/**
 * Plugin Name: ClearOpt - GDPR/CCPA Cookie Consent Banner
 * Plugin URI: https://clearopt.org
 * Description: Lightweight cookie consent for WordPress with a customizable cookie banner, cookie scanning, script blocking, consent preferences, and GDPR/CCPA support.
 * Version: 1.0.17
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: ClearOpt
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: clearopt-cookie-consent
 */

if (!defined('ABSPATH')) :
    exit;
endif;

define('CLEAROPT_COOKIE_CONSENT_VERSION', '1.0.17');

final class ClearOpt_Cookie_Consent
{
    private $option_name = 'clearopt_settings';

    public function __construct()
    {

        // Actions 

        add_action('admin_menu', array($this, 'add_admin_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_head', array($this, 'render_admin_menu_icon_styles'));
        add_action('current_screen', array($this, 'suppress_admin_notices'));
        add_action('admin_notices', array($this, 'render_scan_my_site_notice'));

        add_action('wp_head', array($this, 'render_blocker_script'), 1);

        add_action('wp_ajax_clearopt_dismiss_scan_notice', array($this, 'ajax_dismiss_scan_notice'));
        add_action('wp_ajax_clearopt_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_clearopt_cloud_connect', array($this, 'ajax_cloud_connect'));
        add_action('wp_ajax_clearopt_cloud_disconnect', array($this, 'ajax_cloud_disconnect'));
        add_action('wp_ajax_clearopt_cloud_scan', array($this, 'ajax_cloud_scan'));
        add_action('wp_ajax_clearopt_dismiss_cookie', array($this, 'ajax_dismiss_cookie'));
        add_action('wp_ajax_clearopt_ignore_service', array($this, 'ajax_ignore_service'));
        add_action('wp_ajax_clearopt_reclassify_cookie', array($this, 'ajax_reclassify_cookie'));
        add_action('wp_ajax_clearopt_reclassify_service',array($this, 'ajax_reclassify_service'));
        add_action('wp_ajax_clearopt_block_service', array($this, 'ajax_block_service'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_footer', array($this, 'render_cookie_banner'));

        // Filters 

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_action_links'));
        add_filter('wp_get_consent_type', array($this, 'get_wp_consent_type'));
        add_filter('nav_menu_link_attributes', array($this, 'filter_cookie_preferences_menu_link'), 10, 4);

    }

    public function add_admin_page()
    {
        add_menu_page(
            'ClearOpt Cookie Consent',
            'ClearOpt',
            'manage_options',
            'clearopt-cookie-consent',
            array($this, 'render_admin_page'),
            plugin_dir_url(__FILE__) . 'assets/img/clearopt-shield.png',
            999
        );
    }

    public function add_plugin_action_links($links)
    {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=clearopt-cookie-consent')),
            esc_html__('Settings', 'clearopt-cookie-consent')
        );

        array_unshift($links, $settings_link);

        return $links;
    }

    public function filter_cookie_preferences_menu_link($atts, $menu_item, $args, $depth)
    {
        if (empty($atts['href'])) :
            return $atts;
        endif;

        if (in_array($atts['href'], array('#privacy-settings', '#cookie-preferences'), true)) :
            $atts['href'] = '#cookie-preferences';
            $atts['aria-haspopup'] = 'dialog';
            $atts['aria-controls'] = 'clearopt-cookie-modal';
        endif;

        return $atts;
    }

    public function get_wp_consent_type($consent_type)
    {
        return 'optin';
    }

    public function enqueue_admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_clearopt-cookie-consent') :
            return;
        endif;

        wp_enqueue_style(
          'clearopt-fontawesome',
          plugin_dir_url(__FILE__) . 'assets/fontawesome/css/fontawesome.min.css',
          array(),
          CLEAROPT_COOKIE_CONSENT_VERSION
        );
        
        wp_enqueue_style(
          'clearopt-fontawesome-solid',
          plugin_dir_url(__FILE__) . 'assets/fontawesome/css/solid.min.css',
          array('clearopt-fontawesome'),
          CLEAROPT_COOKIE_CONSENT_VERSION
        );
        
        wp_enqueue_style(
          'clearopt-fontawesome-regular',
          plugin_dir_url(__FILE__) . 'assets/fontawesome/css/regular.min.css',
          array('clearopt-fontawesome'),
          CLEAROPT_COOKIE_CONSENT_VERSION
        );

        wp_enqueue_style(
          'clearopt-fontawesome-brands',
          plugin_dir_url(__FILE__) . 'assets/fontawesome/css/brands.min.css',
          array('clearopt-fontawesome'),
          CLEAROPT_COOKIE_CONSENT_VERSION
        );

        wp_enqueue_style(
          'clearopt-admin',
          plugin_dir_url(__FILE__) . 'assets/css/admin.css',
          array(
            'clearopt-fontawesome-solid',
            'clearopt-fontawesome-regular',
          ),
          CLEAROPT_COOKIE_CONSENT_VERSION . '.' . filemtime(plugin_dir_path(__FILE__) . 'assets/css/admin.css')
        );

        wp_enqueue_script(
            'clearopt-admin',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            CLEAROPT_COOKIE_CONSENT_VERSION . '.' . filemtime(plugin_dir_path(__FILE__) . 'assets/js/admin.js'),
            true
        );

        wp_localize_script(
            'clearopt-admin',
            'ClearOptAdmin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('clearopt_save_settings'),
                'cloudConnectNonce' => wp_create_nonce('clearopt_cloud_connect'),
                'cloudDisconnectNonce' => wp_create_nonce('clearopt_cloud_disconnect'),
                'cloudScanNonce' => wp_create_nonce('clearopt_cloud_scan'),
                'dismissCookieNonce' => wp_create_nonce('clearopt_dismiss_cookie'),
                'reclassifyCookieNonce' => wp_create_nonce('clearopt_reclassify_cookie'),
                'reclassifyServiceNonce' => wp_create_nonce('clearopt_reclassify_service'),
                'ignoreServiceNonce' => wp_create_nonce('clearopt_ignore_service'),
                'blockServiceNonce' => wp_create_nonce('clearopt_block_service'),
            ),
        );
    }

    public function render_admin_menu_icon_styles()
    {
        ?>
        <style>
            #adminmenu .toplevel_page_clearopt-cookie-consent .wp-menu-image img {
              width: 18px;
              height: auto;
              object-fit: cover;
              padding: 7px 0 0;
              opacity: 1 !important;
              filter: brightness(0) invert(1) !important;
            }
        </style>
        <?php
    }

    public function suppress_admin_notices($screen)
    {
      if (!$screen || 'toplevel_page_clearopt-cookie-consent' !== $screen->id) {
        return;
      }

      remove_all_actions('admin_notices');
      remove_all_actions('all_admin_notices');
    }

    public function enqueue_frontend_assets()
    {
        $settings = $this->get_settings();
        $effective_defaults = $this->get_effective_consent_defaults();
      
        if ($settings['enable_banner'] !== '1') :
            return;
        endif;

        wp_enqueue_style(
            'clearopt-frontend',
            plugin_dir_url(__FILE__) . 'assets/css/frontend.css',
            array(),
            CLEAROPT_COOKIE_CONSENT_VERSION
        );

        wp_add_inline_style(
          'clearopt-frontend',
          '
          .clearopt-accent--wordpress_blue {
              --pp-accent: #2271b1;
              --pp-accent-hover: #1b607f;
              --pp-accent-focus: #72aee6;
          }
          '
        );

        wp_enqueue_script(
            'clearopt-frontend',
            plugin_dir_url(__FILE__) . 'assets/js/frontend.js',
            array('jquery'),
            CLEAROPT_COOKIE_CONSENT_VERSION,
            true
        );

        wp_localize_script(
          'clearopt-frontend',
          'clearOptSettings',
          array(
              'gtmContainerId' => $settings['gtm_container_id'] ?? '',
              'clarityId' => $settings['clarity_project_id'] ?? '',
              'metaPixelId' => $settings['meta_pixel_id'] ?? '',
              'analyticsDefault' => $effective_defaults['analytics'],
              'marketingDefault' => $effective_defaults['marketing'],
              'honorGpc' => $settings['honor_gpc'] ?? '0',
              'regionalConsentMode' => $settings['regional_consent_mode'] ?? 'off',
              'visitorRegion' => $effective_defaults['region'],
              'debug' => false,
          )
      );
    }

    private function get_visitor_country_code(): string
    {
        // return 'US';

        $candidates = array(
            getenv('HTTP_GEOIP_COUNTRY_CODE'),
            isset($_SERVER['HTTP_CF_IPCOUNTRY'])
                ? sanitize_text_field(wp_unslash($_SERVER['HTTP_CF_IPCOUNTRY']))
                : '',
            isset($_SERVER['HTTP_CLOUDFRONT_VIEWER_COUNTRY'])
                ? sanitize_text_field(wp_unslash($_SERVER['HTTP_CLOUDFRONT_VIEWER_COUNTRY']))
                : '',
        );

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || $candidate === '') {
                continue;
            }

            $country_code = strtoupper(
                sanitize_text_field($candidate)
            );

            if (
                preg_match('/^[A-Z]{2}$/', $country_code) &&
                $country_code !== 'XX'
            ) {
                return $country_code;
            }
        }

        return '';
    }

    private function has_global_privacy_control_signal(): bool
    {
        if (!isset($_SERVER['HTTP_SEC_GPC'])) {
            return false;
        }

        $gpc = sanitize_text_field(
            wp_unslash($_SERVER['HTTP_SEC_GPC'])
        );

        return $gpc === '1';
    }

    private function get_visitor_region_group(): string
    {
        $country_code = $this->get_visitor_country_code();

        if ($country_code === '') {
            return 'unknown';
        }
        
        $eu_eea_uk = array(
            'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE',
            'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV',
            'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK',
            'SI', 'ES', 'SE',
            'IS', 'LI', 'NO',
            'GB',
        );

        if (in_array($country_code, $eu_eea_uk, true)) {
            return 'eu_uk';
        }

        if ($country_code === 'US') {
            return 'us';
        }

        return 'other';
    }

    private function get_effective_consent_defaults(): array
    {
        $settings = $this->get_settings();

        $analytics_default = $settings['analytics_default'] ?? '0';
        $marketing_default = $settings['marketing_default'] ?? '0';
        $region_group = 'other';

        if (($settings['regional_consent_mode'] ?? 'off') === 'auto') {
            $region_group = $this->get_visitor_region_group();

            if ($region_group === 'eu_uk') {
              $analytics_default = '0';
              $marketing_default = '0';
            } elseif ($region_group === 'us') {
                $analytics_default = '1';
                $marketing_default = '1';
            } elseif ($region_group === 'unknown') {
                $analytics_default = '0';
                $marketing_default = '0';
            }
        }

        if (
            ($settings['honor_gpc'] ?? '0') === '1' &&
            $this->has_global_privacy_control_signal()
        ) {
            $marketing_default = '0';
        }

        return array(
            'analytics' => $analytics_default,
            'marketing' => $marketing_default,
            'region'    => $region_group,
        );
    }

    public function get_settings()
    {
        $defaults = array(
          'enable_banner'   => '0',
          'banner_position' => 'bottom',
          'banner_layout_style' => 'full_width',
          'floating_banner_position' => 'bottom-right',
          'privacy_policy_url' => home_url('/privacy-policy/'),
          'banner_theme' => 'light',
          'banner_message' => 'We use cookies to help provide the best experience possible on our website.',
          'modal_title' => 'Cookie Settings',
          'accent_color' => 'wordpress_blue',
          'gtm_container_id' => '',
          'meta_pixel_id' => '',
          'clarity_project_id' => '',
          'analytics_default' => '0',
          'marketing_default' => '0',
          'honor_gpc' => '0',
          'regional_consent_mode' => 'off',
        );

        $settings = get_option($this->option_name, array());

        return wp_parse_args($settings, $defaults);
    }

    public function render_blocker_script()
    {
        $settings = $this->get_settings();
        $effective_defaults = $this->get_effective_consent_defaults();

        $control_rules = get_option(
          'clearopt_cloud_control_rules',
          array()
        );
        
        if (!is_array($control_rules)) {
          $control_rules = array();
        }
        
        $analytics_consent = ($effective_defaults['analytics'] === '1')
            ? 'granted'
            : 'denied';
        
        $marketing_consent = ($effective_defaults['marketing'] === '1')
            ? 'granted'
            : 'denied';
        
        $blocker_settings = array(
            'resourceRules' => $control_rules,
            'analyticsDefault' => $effective_defaults['analytics'],
            'marketingDefault' => $effective_defaults['marketing'],
            'honorGpc' => $settings['honor_gpc'] ?? '0',
            'regionalConsentMode' => $settings['regional_consent_mode'] ?? 'off',
            'visitorRegion' => $effective_defaults['region'],
            'googleConsentDefault' => array(
                'analytics_storage' => $analytics_consent,
                'ad_storage' => $marketing_consent,
                'ad_user_data' => $marketing_consent,
                'ad_personalization' => $marketing_consent,
            ),
        );

        wp_register_script(
            'clearopt-blocker',
            plugins_url(
                'assets/js/blocker.js',
                __FILE__
            ),
            array(),
            CLEAROPT_COOKIE_CONSENT_VERSION,
            false
        );

        wp_add_inline_script(
            'clearopt-blocker',
            'window.ClearOptBlockerSettings = ' . wp_json_encode($blocker_settings) . ';',
            'before'
        );

        wp_enqueue_script('clearopt-blocker');

        wp_print_scripts('clearopt-blocker');
    }

    public function render_scan_my_site_notice()
    {
        if (!current_user_can('manage_options')) :
            return;
        endif;

        $screen = get_current_screen();

        if (
            $screen &&
            'toplevel_page_clearopt-cookie-consent' === $screen->id
        ) :
            return;
        endif;

        if (get_option('clearopt_scan_notice_dismissed')) :
            return;
        endif;

        if (get_option('clearopt_cloud_last_scan')) :
            return;
        endif;
        ?>

        <div class="notice notice-info is-dismissible clearopt-scan-notice">
            <p>
                <strong>ClearOpt is ready to scan your site for cookies.</strong>

                <a
                    href="<?php echo esc_url(
                        admin_url('admin.php?page=clearopt-cookie-consent')
                    ); ?>"
                    class="button button-small"
                >
                    Scan My Site
                </a>
            </p>
        </div>

        <script>
            document.addEventListener('click', function (event) {
                const button = event.target.closest('.clearopt-scan-notice .notice-dismiss');

                if (!button) {
                    return;
                }

                const notice = button.closest('.clearopt-scan-notice');

                if (notice) {
                    notice.remove();
                }

                const data = new URLSearchParams();

                data.append('action', 'clearopt_dismiss_scan_notice');
                data.append(
                    'nonce',
                    <?php echo wp_json_encode(
                        wp_create_nonce('clearopt_dismiss_scan_notice')
                    ); ?>
                );

                fetch(ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: data.toString()
                });
            });
        </script>

        <?php
    }

    public function render_admin_page()
    {
    $settings = $this->get_settings(); 
    $active_tab = isset($_GET['tab'])
        ? sanitize_key(wp_unslash($_GET['tab']))
        : 'overview';

    if (!in_array($active_tab, array('overview', 'banner', 'integrations'), true)) :
        $active_tab = 'overview';
    endif;

    $overview_url = add_query_arg(
        array(
            'page' => 'clearopt-cookie-consent',
            'tab'  => 'overview',
        ),
        admin_url('admin.php')
    );

    $banner_url = add_query_arg(
        array(
            'page' => 'clearopt-cookie-consent',
            'tab'  => 'banner',
        ),
        admin_url('admin.php')
    );

    $integrations_url = add_query_arg(
      array(
          'page' => 'clearopt-cookie-consent',
          'tab'  => 'integrations',
      ),
      admin_url('admin.php')
    );

    $cloud_token = get_option('clearopt_cloud_token', '');
    $cloud_connected_at = get_option('clearopt_cloud_connected_at', '');
    $is_cloud_connected = !empty($cloud_token); 
    $cloud_cookies_found = get_option('clearopt_cloud_cookies_found', '');
    $cloud_categories = get_option(
      'clearopt_cloud_categories',['necessary' => 0,'analytics' => 0,'marketing' => 0,'unknown' => 0,]
    );

    $cloud_cookie_details = get_option(
      'clearopt_cloud_cookie_details',
      array()
    );

    $classification_overrides = get_option(
      'clearopt_cookie_classification_overrides',
      array()
    );
    
    if (!is_array($classification_overrides)) :
        $classification_overrides = array();
    endif;
    
    foreach ($cloud_cookie_details as &$cookie) :
    
        $cookie_key = strtolower(
            ($cookie['name'] ?? '') . '|' . ($cookie['domain'] ?? '')
        );
    
        if (
            empty($cookie['service']) &&
            isset($classification_overrides[$cookie_key]) &&
            in_array(
                $classification_overrides[$cookie_key],
                array('necessary', 'analytics', 'marketing', 'unknown'),
                true
            )
        ) :
            $cookie['category'] = $classification_overrides[$cookie_key];
        endif;
    
    endforeach;
    
    unset($cookie);

    $cloud_services = array();
    $cloud_unidentified_cookies = array();

    foreach ($cloud_cookie_details as $cookie) :

        $service = $cookie['service'] ?? '';

        if (empty($service)) :

            $cloud_unidentified_cookies[] = $cookie;
            continue;

        endif;

        if (!isset($cloud_services[$service])) :

          $service_labels = array(
              'meta-pixel'        => 'Meta Pixel',
              'google-analytics'  => 'Google Analytics',
              'cloudflare'        => 'Cloudflare',
              'clearopt-consent'  => 'ClearOpt Consent',
              'microsoft-clarity' => 'Microsoft Clarity',
              'sourcebuster'      => 'Sourcebuster',
              'sharethis'         => 'ShareThis',
              'magellan-ai'       => 'Magellan AI',
              'tapad'             => 'TapAd',
          );
          
          $cloud_services[$service] = array(
              'service'  => $service,
              'name'     => $service_labels[$service] ?? ucwords(str_replace('-', ' ', $service)),
              'provider' => $cookie['provider'] ?? '',
              'category' => $cookie['category'] ?? 'unknown',
              'cookies'  => array(),
          );

        endif;

        $cloud_services[$service]['cookies'][] = $cookie;

    endforeach;

    $service_classification_overrides = get_option(
        'clearopt_service_classification_overrides',
        array()
    );
    
    if (!is_array($service_classification_overrides)) :
        $service_classification_overrides = array();
    endif;
    
    foreach ($cloud_services as $service_id => &$service_data) :
    
        if (
            isset($service_classification_overrides[$service_id]) &&
            in_array(
                $service_classification_overrides[$service_id],
                array('necessary', 'analytics', 'marketing', 'unknown'),
                true
            )
        ) :
    
            $service_data['category'] =
                $service_classification_overrides[$service_id];
    
            foreach ($service_data['cookies'] as &$cookie) :
                $cookie['category'] = $service_data['category'];
            endforeach;
    
            unset($cookie);
    
        endif;
    
    endforeach;
    
    unset($service_data);

    $cloud_service_categories = array(
        'necessary' => 0,
        'analytics' => 0,
        'marketing' => 0,
        'unknown'   => 0,
    );
    
    foreach ($cloud_services as $service_data) :
    
        $category = $service_data['category'] ?? 'unknown';
    
        if (isset($cloud_service_categories[$category])) :
            $cloud_service_categories[$category]++;
        endif;
    
    endforeach;
    
    $cloud_services_found = count($cloud_services);

    $cloud_unidentified_count = count(
        array_filter(
            $cloud_unidentified_cookies,
            function ($cookie) {
                return ($cookie['category'] ?? 'unknown') === 'unknown';
            }
        )
    );

    foreach ($cloud_unidentified_cookies as $cookie) :

        $category = $cookie['category'] ?? 'unknown';
    
        if (isset($cloud_service_categories[$category])) :
            $cloud_service_categories[$category]++;
        endif;
    
    endforeach;

    $cloud_service_health_counts = array(
        'green'  => 0,
        'yellow' => 0,
        'red'    => 0,
    );
    
    foreach ($cloud_services as $service_data) :
    
        $service_health_status = $this->get_service_health_status($service_data);
    
        if (isset($cloud_service_health_counts[$service_health_status])) :
            $cloud_service_health_counts[$service_health_status]++;
        endif;
    
    endforeach;
    
    $cloud_services_need_attention = $cloud_service_health_counts['red'];

    $cloud_categories = array(
      'necessary' => 0,
      'analytics' => 0,
      'marketing' => 0,
      'unknown'   => 0,
    );
    
    foreach ($cloud_cookie_details as $cookie) :
    
        $category = $cookie['category'] ?? 'unknown';
    
        if (isset($cloud_categories[$category])) :
            $cloud_categories[$category]++;
        endif;
    
    endforeach;

    $cloud_health_counts = array(
      'green'  => 0,
      'yellow' => 0,
      'red'    => 0,
    );
  
    $cloud_before_consent_count = 0;

    foreach ($cloud_cookie_details as $cookie) :

        $health_status = $this->get_cookie_health_status($cookie);

        if (isset($cloud_health_counts[$health_status])) :
            $cloud_health_counts[$health_status]++;
        endif;

        $category = $cookie['category'] ?? 'unknown';
        $first_seen_state = $cookie['first_seen_state'] ?? '';

        if (
            in_array($category, array('analytics', 'marketing'), true) &&
            $first_seen_state === 'baseline'
        ) :
            $cloud_before_consent_count++;
        endif;

    endforeach;

    $cloud_last_scan = get_option('clearopt_cloud_last_scan', '');

    $has_cloud_scan = !empty($cloud_last_scan)
        && strtotime($cloud_last_scan) !== false; ?>

    <div class="wrap clearopt-admin" style="width: 89%;">
        <div class="clearopt-app-header">
            <div class="clearopt-app-header__brand">
                <span class="clearopt-app-header__mark" aria-hidden="true"></span>
                <div class="clearopt-app-header__brand-copy">
                    <h1>ClearOpt</h1>
                    <p>Scan your site to audit, classify, and review your website's cookies.</p>
                    <p>Customize your cookie banner and connect analytics and marketing tools based on visitor consent.</p>
                </div>
            </div>
            <div class="clearopt-app-header__actions">
                <a class="clearopt-app-header__support" href="https://wordpress.org/support/plugin/clearopt-cookie-consent/" target="_blank">
                    <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                    <span>Get Support</span>
                </a>
            </div>
        </div>

        <div class="clearopt-workspace clearopt-workspace--<?php echo esc_attr($active_tab); ?>">

            <nav class="clearopt-app-nav" aria-label="ClearOpt navigation">

                <a href="<?php echo esc_url($overview_url); ?>" class="clearopt-app-nav__item<?php echo $active_tab === 'overview' ? ' clearopt-app-nav__item--active' : ''; ?>">
                    <!-- <svg class="clearopt-app-nav__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                        <path d="M8 5.5H16L15.25 8H17L15.5 19H8.5L7 8H8.75L8 5.5Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
                        <path d="M12 8.75V11" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                    </svg> -->
                    <span class="dashicons dashicons-superhero"></span>
                    <span>Overview</span>
                </a>

                <a href="<?php echo esc_url($banner_url); ?>" class="clearopt-app-nav__item<?php echo $active_tab === 'banner' ? ' clearopt-app-nav__item--active' : ''; ?>">
                    <!-- <svg class="clearopt-app-nav__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                        <rect x="3" y="4" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.75" />
                        <path d="M8 21H16" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
                        <path d="M12 18V21" stroke="currentColor" stroke-width="1.75" />
                    </svg> -->
                    <span class="dashicons dashicons-desktop"></span>
                    <span>Cookie Banner</span>
                </a>

                <a href="<?php echo esc_url($integrations_url); ?>" class="clearopt-app-nav__item<?php echo $active_tab === 'integrations' ? ' clearopt-app-nav__item--active' : ''; ?>">
                    <span class="dashicons dashicons-admin-links"></span>
                    <span>Integrations</span>
                </a>

                <span class="clearopt-app-save-status" id="clearopt_save_status" role="status" aria-live="polite"></span>

                <button type="button" class="clearopt-app-save" id="clearopt_save_settings">
                    Save Settings
                </button>

            </nav>

            <div class="metabox-holder has-right-sidebar clearopt-settings-layout">
                <div class="clearopt-main" style="float: left; width: 65%;">
                    <div class="postbox clearopt-service-review clearopt-tab-overview">
                        <div class="inside">


                        <div class="clearopt-service-review__header">
                            <div class="clearopt-service-review__heading">
                                <div>
                                    <h3>
                                        <span class="dashicons dashicons-database"></span>
                                        Service Review
                                    </h3>
                                    <p>
                                      A complete view of the third-party services and cookies detected on your site.
                                    </p>
                                </div>
                            </div>
                            <div class="clearopt-service-review__actions">
                              
                              <button type="button" class="clearopt-scan-button clearopt-scan-cloud clearopt-service-review__scan">
                                <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>
                                <span>Scan Again</span>
                              </button>
                            </div>
                        </div>

                        

                            <div class="clearopt-cookie-categories">

                                <div class="clearopt-cookie-category clearopt-cookie-category--allowed">
                                    <span class="clearopt-cookie-category__icon">
                                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                    </span>
                                    <span class="clearopt-cookie-category__count">
                                        <?php echo absint($cloud_service_health_counts['green'] ?? 0); ?>
                                    </span>
                                    <span class="clearopt-cookie-category__label">
                                        Services passed
                                    </span>
                                    <span class="clearopt-cookie-category__description">
                                        Based on current settings
                                    </span>
                                </div>

                                <div class="clearopt-cookie-category clearopt-cookie-category--attention">
                                    <span class="clearopt-cookie-category__icon">
                                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                                    </span>
                                    <span class="clearopt-cookie-category__count">
                                        <?php echo absint($cloud_service_health_counts['red'] ?? 0); ?>
                                    </span>
                                    <span class="clearopt-cookie-category__label">
                                        Needs Attention
                                    </span>
                                    <span class="clearopt-cookie-category__description">
                                        Review recommended
                                    </span>
                                </div>

                                <div class="clearopt-cookie-category clearopt-cookie-category--unknown">
                                    <span class="clearopt-cookie-category__icon">
                                        <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
                                    </span>
                                    <span class="clearopt-cookie-category__count">
                                        <?php echo absint($cloud_unidentified_count); ?>
                                    </span>
                                    <span class="clearopt-cookie-category__label">
                                        Unknown
                                    </span>
                                    <span class="clearopt-cookie-category__description">
                                        Not yet categorized
                                    </span>
                                </div>

                                <div class="clearopt-cookie-category clearopt-cookie-category--total">
                                    <span class="clearopt-cookie-category__icon">
                                        <i class="fa-solid fa-cookie-bite" aria-hidden="true"></i>
                                    </span>
                                    <span class="clearopt-cookie-category__count">
                                        <?php echo absint($cloud_cookies_found); ?>
                                    </span>
                                    <span class="clearopt-cookie-category__label">
                                        Total Cookies
                                    </span>
                                    <span class="clearopt-cookie-category__description">
                                        Across all services
                                    </span>
                                </div>

                            </div>

                            <?php if (!$has_cloud_scan) : ?>

                                <div class="clearopt-scan-health clearopt-scan-health--neutral clearopt-scan-health--success">
                                    <span class="clearopt-scan-health__icon">
                                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                    </span>

                                    <div class="clearopt-scan-health__content">
                                        <strong>No scan results yet</strong>
                                        <span>Scan your site to review detected cookies and third-party services.</span>
                                    </div>
                                </div>

                            <?php elseif ($cloud_services_need_attention > 0) : ?>
                                <div class="clearopt-scan-health clearopt-scan-health--red clearopt-scan-health--success">
                                    <span class="clearopt-scan-health__icon">
                                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                    </span>

                                    <div class="clearopt-scan-health__content">
                                        <strong>
                                            <?php echo esc_html($cloud_services_need_attention); ?>
                                            <?php echo $cloud_services_need_attention === 1 ? 'service needs attention' : 'services need attention'; ?>
                                        </strong>
                                        <span>Review the items below for more information.</span>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="clearopt-scan-health clearopt-scan-health--green clearopt-scan-health--success">
                                    <span class="clearopt-scan-health__icon">
                                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    </span>

                                    <div class="clearopt-scan-health__content">
                                        <strong>Everything looks good!</strong>
                                        <span>Your site's observed cookies are loading properly based on your current ClearOpt settings.</span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($cloud_cookie_details)) : ?>
                                <div class="clearopt-cookie-details">
                                    <div class="clearopt-cookie-details__content">

                                        <?php if (!empty($cloud_services)) : ?>

                                            <div class="clearopt-service-details">

                                                <?php foreach ($cloud_services as $service_data) : ?>

                                                    <?php
                                                    $service_external_load_candidate = false;

                                                    foreach ($service_data['cookies'] as $cookie) :

                                                        if ($this->is_external_service_load_candidate($cookie)) :
                                                            $service_external_load_candidate = true;
                                                            break;
                                                        endif;

                                                    endforeach; ?>

                                                    <?php
                                                    $service_health_status = $this->get_service_health_status($service_data);

                                                    $service_health_label = array(
                                                      'green'     => 'Good ✓',
                                                      'yellow'    => 'Review ⚠',
                                                      'red'       => 'Needs attention',
                                                      'dismissed' => 'Ignored',
                                                    )[$service_health_status] ?? 'Review ⚠'; 

                                                    $service_icon_class = 'clearopt-service-icon--generic';

                                                    switch (strtolower($service_data['name'] ?? '')) {
                                                        case 'google analytics':
                                                          $service_icon_class = 'clearopt-service-icon--google-analytics';
                                                          break;

                                                        case 'cloudflare':
                                                          $service_icon_class = 'clearopt-service-icon--cloudflare';
                                                          break;

                                                        case 'clearopt consent':
                                                          $service_icon_class = 'clearopt-service-icon--clearopt';
                                                          break;

                                                        case 'sharethis':
                                                          $service_icon_class = 'clearopt-service-icon--sharethis';
                                                          break;

                                                        case 'meta pixel':
                                                          $service_icon_class = 'clearopt-service-icon--meta';
                                                          break;

                                                        case 'microsoft clarity':
                                                          $service_icon_class = 'clearopt-service-icon--clarity';
                                                          break;
                                                        case 'magellan ai':
                                                          $service_icon_class = 'clearopt-service-icon--magellan';
                                                          break;
                                                        
                                                        case 'tapad':
                                                          $service_icon_class = 'clearopt-service-icon--tapad';
                                                          break;
                                                      } ?>

                                                      <div class="clearopt-service-details__item">
                                                        <div class="clearopt-service-details__icon <?php echo esc_attr($service_icon_class); ?>">
                                                            <?php if ($service_icon_class === 'clearopt-service-icon--google-analytics') : ?>
                                                              <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
                                                            <?php elseif ($service_icon_class === 'clearopt-service-icon--cloudflare') : ?>
                                                              <i class="fa-brands fa-cloudflare" aria-hidden="true"></i>
                                                            <?php elseif ($service_icon_class === 'clearopt-service-icon--clearopt') : ?>
                                                              <i class="fa-solid fa-link" aria-hidden="true"></i>
                                                            <?php elseif ($service_icon_class === 'clearopt-service-icon--meta') : ?>
                                                              <i class="fa-brands fa-meta" aria-hidden="true"></i>
                                                            <?php elseif ($service_icon_class === 'clearopt-service-icon--magellan') : ?>
                                                              <i class="fa-solid fa-satellite-dish" aria-hidden="true"></i>
                                                            <?php elseif ($service_icon_class === 'clearopt-service-icon--tapad') : ?>
                                                              <i class="fa-solid fa-bullseye" aria-hidden="true"></i>
                                                            <?php else : ?>
                                                              <span class="dashicons dashicons-admin-generic"></span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="clearopt-service-details__content">
                                                            <div class="clearopt-service-details__title-row">
                                                                <strong class="clearopt-service-details__name">
                                                                    <?php echo esc_html($service_data['name']); ?>
                                                                </strong>
                                                                <span class="clearopt-cookie-health clearopt-cookie-health--<?php echo esc_attr($service_health_status); ?>">
                                                                    <?php echo esc_html($service_health_label); ?>
                                                                </span>
                                                            </div>

                                                            <span class="clearopt-service-details__meta">
                                                                <?php echo esc_html(ucfirst($service_data['category'])); ?>

                                                                <?php if (!empty($service_data['provider'])) : ?>
                                                                    · <?php echo esc_html($service_data['provider']); ?>
                                                                <?php endif; ?>
                                                            </span>

                                                            <?php if ($service_external_load_candidate) : ?>
                                                                <p class="clearopt-cookie-diagnostic">
                                                                    This service may also be loading outside of ClearOpt.
                                                                </p>
                                                            <?php endif; ?>

                                                            <div class="clearopt-service-details__cookies">
                                                                <strong>Cookies detected:</strong>
                                                                <?php foreach ($service_data['cookies'] as $cookie) : ?>
                                                                    <code>
                                                                        <?php echo esc_html($cookie['name']); ?>
                                                                    </code>
                                                                <?php endforeach; ?>
                                                            </div>

                                                            <div class="clearopt-cookie-review-actions">
                                                                <strong class="clearopt-cookie-review-actions__label">
                                                                    Action
                                                                </strong>
                                                                <button type="button" class="clearopt-cookie-action clearopt-service-reclassify" data-service="<?php echo esc_attr($service_data['service']); ?>" data-category="<?php echo esc_attr($service_data['category']); ?>">
                                                                    Reclassify
                                                                </button>

                                                                <button type="button" class="clearopt-cookie-action clearopt-service-ignore" data-service="<?php echo esc_attr($service_data['service']); ?>">
                                                                    Ignore
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                              
                                            </div>

                                        <?php endif; ?>

                                        <?php
                                        $cookie_categories = array(
                                            'necessary' => 'Necessary',
                                            'analytics' => 'Analytics',
                                            'marketing' => 'Marketing',
                                            'unknown'   => 'Unknown',
                                        );
                                        ?>

                                        <?php foreach ($cookie_categories as $category_key => $category_label) : ?>

                                            <?php
                                            $category_cookies = array_filter(
                                                $cloud_cookie_details,
                                                function ($cookie) use ($category_key) {
                                                    return isset($cookie['category'])
                                                        && $cookie['category'] === $category_key
                                                        && empty($cookie['service']);
                                                }
                                            );
                                            ?>

                                            <?php if (!empty($category_cookies)) : ?>

                                                <div class="clearopt-cookie-details__group">

                                                    <strong class="clearopt-cookie-details__heading">
                                                        <?php echo esc_html($category_label); ?>
                                                    </strong>

                                                    <?php foreach ($category_cookies as $cookie) : ?>

                                                    <?php
                                                    $health_status = $this->get_cookie_health_status($cookie);

                                                    $health_label = array(
                                                        'green'     => 'Good ✓',
                                                        'yellow'    => 'Review ⚠',
                                                        'red'       => 'Needs attention',
                                                        'dismissed' => 'Ignored',
                                                    )[$health_status] ?? 'Review ⚠';
                                                    ?>

                                                    <div class="clearopt-service-details__item clearopt-service-details__item--<?php echo esc_attr($health_status); ?>">

                                                        <div class="clearopt-service-details__icon clearopt-cookie-icon--<?php echo esc_attr($category_key); ?>">
                                                            <i class="fa-solid fa-cookie-bite" aria-hidden="true"></i>
                                                        </div>

                                                        <div class="clearopt-service-details__content">

                                                            <div class="clearopt-cookie-details__title-row">

                                                                <code>
                                                                    <?php echo esc_html($cookie['name']); ?>

                                                                    <?php if (!empty($cookie['provider'])) : ?>
                                                                        (<?php echo esc_html($cookie['provider']); ?>)
                                                                    <?php endif; ?>
                                                                </code>

                                                                <span class="clearopt-cookie-health clearopt-cookie-health--<?php echo esc_attr($health_status); ?>">
                                                                    <?php echo esc_html($health_label); ?>
                                                                </span>

                                                                <?php if ($health_status === 'red') : ?>
                                                                    <button
                                                                        style="display:none"
                                                                        type="button"
                                                                        class="clearopt-cookie-review"
                                                                        data-cookie-name="<?php echo esc_attr($cookie['name']); ?>"
                                                                        data-cookie-category="<?php echo esc_attr($cookie['category']); ?>"
                                                                        data-cookie-state="<?php echo esc_attr($cookie['first_seen_state']); ?>"
                                                                        data-cookie-provider="<?php echo esc_attr($cookie['provider'] ?? ''); ?>"
                                                                        data-cookie-service="<?php echo esc_attr($cookie['service'] ?? ''); ?>"
                                                                    >
                                                                        Review
                                                                    </button>
                                                                <?php endif; ?>

                                                            </div>

                                                            <?php if (!empty($cookie['domain'])) : ?>

                                                                <?php
                                                                $display_domain = preg_replace(
                                                                    '#^https?://#i',
                                                                    '',
                                                                    $cookie['domain']
                                                                );

                                                                $display_domain = ltrim($display_domain, '.');
                                                                ?>

                                                                <span class="clearopt-cookie-details__domain">
                                                                    <strong>Domain:</strong>
                                                                    <?php echo esc_html($display_domain); ?>
                                                                </span>

                                                            <?php endif; ?>

                                                            <?php if ($this->is_external_service_load_candidate($cookie)) : ?>

                                                                <p class="clearopt-cookie-diagnostic">
                                                                    This service may also be loading outside of ClearOpt.
                                                                </p>

                                                            <?php endif; ?>

                                                            <div class="clearopt-cookie-health-row" style="display:none">

                                                                <span class="clearopt-cookie-health clearopt-cookie-health--<?php echo esc_attr($health_status); ?>">
                                                                    <?php echo esc_html($health_label); ?>
                                                                </span>

                                                                <?php if ($health_status === 'red') : ?>
                                                                    <button
                                                                        type="button"
                                                                        class="clearopt-cookie-review"
                                                                        data-cookie-name="<?php echo esc_attr($cookie['name']); ?>"
                                                                        data-cookie-category="<?php echo esc_attr($cookie['category']); ?>"
                                                                        data-cookie-state="<?php echo esc_attr($cookie['first_seen_state']); ?>"
                                                                        data-cookie-provider="<?php echo esc_attr($cookie['provider'] ?? ''); ?>"
                                                                        data-cookie-service="<?php echo esc_attr($cookie['service'] ?? ''); ?>"
                                                                    >
                                                                        Review
                                                                    </button>
                                                                <?php endif; ?>

                                                            </div>

                                                            <?php if ($health_status === 'red') : ?>
                                                                <div class="clearopt-cookie-review-panel" hidden>
                                                                    <p>
                                                                        This <?php echo esc_html(ucfirst($cookie['category'])); ?> cookie was detected before consent was granted.
                                                                    </p>
                                                                </div>
                                                            <?php endif; ?>

                                                            <div class="clearopt-cookie-review-actions">

                                                                <strong class="clearopt-cookie-review-actions__label">
                                                                    Action
                                                                </strong>

                                                                <button
                                                                    type="button"
                                                                    class="clearopt-cookie-action clearopt-change-classification"
                                                                    data-cookie-name="<?php echo esc_attr($cookie['name']); ?>"
                                                                    data-cookie-domain="<?php echo esc_attr($cookie['domain'] ?? ''); ?>"
                                                                    data-cookie-category="<?php echo esc_attr($cookie['category']); ?>"
                                                                >
                                                                    Reclassify
                                                                </button>

                                                                <button
                                                                    type="button"
                                                                    class="clearopt-cookie-action clearopt-dismiss-cookie"
                                                                    data-cookie-name="<?php echo esc_attr($cookie['name']); ?>"
                                                                    data-cookie-domain="<?php echo esc_attr($cookie['domain'] ?? ''); ?>"
                                                                >
                                                                    Ignore
                                                                </button>

                                                            </div>
                                                          
                                                        </div>

                                                    </div>

                                                    <?php endforeach; ?>

                                                </div>

                                            <?php endif; ?>

                                        <?php endforeach; ?>

                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div> <!-- .clearopt-service-review -->
                    
                    <div class="postbox clearopt-banner-settings clearopt-tab-banner">
                        <h3>
                            <span class="dashicons dashicons-text"></span>
                            Banner Content
                        </h3>

                        <div class="inside">
                            <table class="form-table" style="border-collapse: collapse;" width="100%" cellspacing="8" cellpadding="0">
                                <tbody>
                                    <tr>
                                      <td>
                                          <label for="clearopt_banner_message">
                                              <strong>Banner Message</strong>
                                              <p class="description">Enter the message displayed in the cookie banner. Basic HTML is supported, including links, bold text, and paragraphs. Use <code>{{privacy_policy_url}}</code> inside an anchor tag to automatically insert your Privacy Policy URL.</p>
                                          </label>
                                      </td>

                                      <td>
                                          <textarea
                                              id="clearopt_banner_message"
                                              rows="3"
                                              class="large-text"
                                          ><?php echo esc_textarea($settings['banner_message']); ?></textarea>
                                      </td>
                                  </tr>
                                  <tr>
                                      <td>
                                          <label for="clearopt_privacy_policy_url">
                                              <strong>Privacy Policy URL</strong>
                                              <p class="description">Enter the URL for your privacy policy page.</p>
                                          </label>
                                      </td>

                                      <td>
                                          <input
                                              type="url"
                                              id="clearopt_privacy_policy_url"
                                              value="<?php echo esc_url($settings['privacy_policy_url']); ?>"
                                              class="regular-text"
                                          >
                                      </td>
                                  </tr>
                                </tbody>
                            </table>
                        </div>
                    </div> <!-- .clearopt-banner-settings -->

                    <div class="postbox clearopt-preference-settings clearopt-tab-banner">
                        <h3>
                            <span class="dashicons dashicons-privacy"></span>
                            Cookie Preference Settings
                        </h3>

                        <div class="inside">
                            <table class="form-table" style="border-collapse: collapse;" width="100%" cellspacing="8" cellpadding="0">
                                <tbody>
                                    <tr>
                                        <td>
                                            <label for="clearopt_modal_title">
                                                <strong>Modal Title</strong>
                                                <p class="description">
                                                    Enter the title displayed at the top of the cookie preferences modal.
                                                </p>
                                            </label>
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                id="clearopt_modal_title"
                                                value="<?php echo esc_attr($settings['modal_title']); ?>"
                                                class="regular-text"
                                            >
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div> <!-- .clearopt-preference-settings -->

                    <div class="postbox clearopt-integration-settings clearopt-tab-integrations">
                        <h3>
                            <span class="dashicons dashicons-admin-plugins"></span>
                            Third-Party Integrations
                        </h3>

                        <div class="inside">
                          <table class="form-table" style="border-collapse: collapse;" width="100%" cellspacing="8" cellpadding="0">
                            <tbody>
                              <tr>
                                <th scope="row">
                                  <label for="clearopt_gtm_container_id">
                                      Google Tag Manager Container ID
                                  </label>
                                </th>
                                <td>
                                  <input type="text" id="clearopt_gtm_container_id" name="clearopt_settings[gtm_container_id]" value="<?php echo esc_attr($settings['gtm_container_id'] ?? ''); ?>" class="regular-text" placeholder="GTM-XXXXXXX">
                                  <p class="description">
                                      Enter the Google Tag Manager container ID to load after analytics consent is granted.
                                  </p>
                                </td>
                              </tr>
                              <tr>
                                <th scope="row">
                                    <label for="clearopt-meta-pixel-id">
                                        Meta Pixel ID
                                    </label>
                                </th>

                                <td>
                                    <input
                                        type="text"
                                        id="clearopt-meta-pixel-id"
                                        name="meta_pixel_id"
                                        value="<?php echo esc_attr($settings['meta_pixel_id'] ?? ''); ?>"
                                        class="regular-text"
                                        placeholder="123456789012345"
                                    >

                                    <p class="description">
                                        Enter your Meta Pixel ID. ClearOpt will load the Meta Pixel only after Marketing consent has been granted.
                                    </p>
                                </td>
                            </tr>
                              <tr>
                                <th scope="row">
                                    <label for="clearopt-clarity-project-id">
                                        Microsoft Clarity Project ID
                                    </label>
                                </th>

                                <td>
                                    <input
                                        type="text"
                                        id="clearopt-clarity-project-id"
                                        name="clarity_project_id"
                                        value="<?php echo esc_attr($settings['clarity_project_id'] ?? ''); ?>"
                                        class="regular-text"
                                        placeholder="abcd1234ef"
                                    >

                                    <p class="description">
                                        Enter your Microsoft Clarity Project ID. ClearOpt will load Clarity only after Analytics consent has been granted.
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                          </table>
                        </div>
                    </div> <!-- .clearopt-integration-settings -->

                    <div class="postbox clearopt-advanced-settings" style="display:none">
                      <h3>
                          <span class="dashicons dashicons-admin-tools"></span>
                          Advanced Settings
                      </h3>
                    </div> <!-- .clearopt-advanced-setings -->
                </div>

                <div class="clearopt-sidebar" style="float: right; width: 32%;">
                    <div class="postbox clearopt-banner-status clearopt-tab-banner">
                        <h3>
                            <span class="dashicons dashicons-visibility"></span>
                            Banner Status
                        </h3>

                        <div class="inside">
                            <p>
                                <label>
                                    <input type="checkbox" id="clearopt_enable_banner" value="1" <?php checked($settings['enable_banner'], '1'); ?>>
                                    <strong>Enable cookie banner</strong>
                                </label>
                            </p>

                            <p class="description">
                                Display the cookie consent banner and preferences modal on your website.
                            </p>
                        </div>
                    </div>
                    <div class="postbox clearopt-banner-layout clearopt-tab-banner">
                        <h3>
                            <span class="dashicons dashicons-layout"></span>
                            Banner Layout
                        </h3>

                        <div class="inside">
                            <p>
                                <label for="clearopt_banner_layout_style">
                                    <strong>Layout Style</strong>
                                </label>
                            </p>

                            <select id="clearopt_banner_layout_style">
                                <option value="default" <?php selected($settings['banner_layout_style'], 'default'); ?>>Default</option>
                                <option value="full_width" <?php selected($settings['banner_layout_style'], 'full_width'); ?>>Full Width</option>
                                <option value="floating_box" <?php selected($settings['banner_layout_style'], 'floating_box'); ?>>Floating Box</option>
                            </select>

                            <p class="description">
                                Choose the overall layout style for the cookie banner.
                            </p>

                            <p>
                                <label for="clearopt_banner_position">
                                    <strong>Banner Position</strong>
                                </label>
                            </p>

                            <select id="clearopt_banner_position">
                                <option value="bottom" <?php selected($settings['banner_position'], 'bottom'); ?>>Bottom</option>
                                <option value="top" <?php selected($settings['banner_position'], 'top'); ?>>Top</option>
                            </select>

                            <p class="description">
                                Choose where the cookie banner appears on the frontend.
                            </p>

                            <div class="clearopt-floating-banner-position-row">
                                <p>
                                    <label for="clearopt_floating_banner_position">
                                        <strong>Floating Position</strong>
                                    </label>
                                </p>

                                <select id="clearopt_floating_banner_position" name="floating_banner_position">
                                    <option value="bottom-right" <?php selected($settings['floating_banner_position'], 'bottom-right'); ?>>Bottom Right</option>
                                    <option value="bottom-left" <?php selected($settings['floating_banner_position'], 'bottom-left'); ?>>Bottom Left</option>
                                    <option value="top-right" <?php selected($settings['floating_banner_position'], 'top-right'); ?>>Top Right</option>
                                    <option value="top-left" <?php selected($settings['floating_banner_position'], 'top-left'); ?>>Top Left</option>
                                </select>

                                <p class="description">Choose where the floating cookie banner appears.</p>
                            </div>
                        </div>
                    </div>
                    <div class="postbox clearopt-banner-style clearopt-tab-banner">
                        <h3>
                            <span class="dashicons dashicons-art"></span>
                            Style
                        </h3>

                        <div class="inside">
                            <p>
                                <label for="clearopt_banner_theme">
                                    <strong>Banner Theme</strong>
                                </label>
                            </p>

                            <select id="clearopt_banner_theme">
                                <option value="light" <?php selected($settings['banner_theme'], 'light'); ?>>Light</option>
                                <option value="dark" <?php selected($settings['banner_theme'], 'dark'); ?>>Dark</option>
                                <option value="plain" <?php selected($settings['banner_theme'], 'plain'); ?>>Plain / Unstyled</option>
                            </select>

                            <p class="description">
                                Choose the visual appearance of the cookie banner and settings modal.
                            </p>

                            <p>
                                <label for="clearopt_accent_color">
                                    <strong>Accent Color</strong>
                                </label>
                            </p>

                            <select id="clearopt_accent_color">
                                <option value="wordpress_blue" <?php selected($settings['accent_color'], 'wordpress_blue'); ?>>
                                  WordPress Blue
                                </option>

                                <option value="navy" <?php selected($settings['accent_color'], 'navy'); ?>>
                                  Navy
                                </option>

                                <option value="verdant_green" <?php selected($settings['accent_color'], 'verdant_green'); ?>>
                                  Verdant Green
                                </option>

                                <option value="mocha_mousse" <?php selected($settings['accent_color'], 'mocha_mousse'); ?>>
                                  Mocha Mousse
                                </option>

                                <option value="terracotta" <?php selected($settings['accent_color'], 'terracotta'); ?>>
                                  Terracotta
                                </option>

                                <option value="harvest_orange" <?php selected($settings['accent_color'], 'harvest_orange'); ?>>
                                  Harvest Orange
                                </option>

                                <option value="muted_rose" <?php selected($settings['accent_color'], 'muted_rose'); ?>>
                                  Muted Rose
                                </option>

                                <option value="digital_lavender" <?php selected($settings['accent_color'], 'digital_lavender'); ?>>
                                  Digital Lavender
                                </option>
                            </select>






                        </div>
                    </div>
                    <div class="postbox clearopt-cloud-status clearopt-tab-overview">

                        <h3>
                            <span class="dashicons dashicons-cloud"></span>
                            Status
                        </h3>

                        <div class="inside">

                            <?php if ($is_cloud_connected) : ?>

                                <div class="clearopt-cloud-connected">
                                    <i class="fa-regular fa-cloud clearopt-cloud-connected__icon" aria-hidden="true"></i>
                                    <div class="clearopt-cloud-connected__text">
                                        <strong>Ready</strong>
                                        <span>ClearOpt Cloud</span>
                                    </div>
                                </div>

                                <?php if (!empty($cloud_connected_at)) : ?>
                                    <p class="description">
                                        Last scan:
                                        <strong>
                                          <?php echo esc_html(wp_date('F j, Y \a\t g:i a',strtotime($cloud_connected_at))); ?>
                                        </strong>
                                    </p>
                                <?php endif; ?>

                                <p>
                                    <button type="button" class="clearopt-scan-button clearopt-scan-cloud">
                                      <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                      <span><?php echo $cloud_last_scan ? 'Scan Again' : 'Scan My Site'; ?></span>
                                    </button>
                                </p>

                            <?php else : ?>

                                <div class="clearopt-cloud-connected">
                                    <i class="fa-regular fa-cloud clearopt-cloud-connected__icon" aria-hidden="true"></i>
                                    <div class="clearopt-cloud-connected__text">
                                        <strong>Ready</strong>
                                        <span>ClearOpt Cloud</span>
                                    </div>
                                </div>

                                <p class="description">
                                    Scan your site to identify cookies and privacy services.
                                </p>

                                <p>
                                    <button type="button" class="clearopt-scan-button clearopt-scan-cloud">
                                      <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                      <span><?php echo $cloud_last_scan ? 'Scan Again' : 'Scan My Site'; ?></span>
                                    </button>
                                </p>

                            <?php endif; ?>

                            <div id="clearopt-scan-progress" style="display: none;">
                                <span class="spinner is-active" style="float: none; margin: 0 6px 0 0;"></span>
                                <span>
                                    Scanning your site...
                                    <strong id="clearopt-scan-percent">0%</strong>
                                </span>
                            </div>

                        </div>
                    </div>

                    <div class="postbox clearopt-default-settings clearopt-tab-overview">
                        <h3>
                            <span class="dashicons dashicons-admin-generic"></span>
                            Default Settings
                        </h3>

                        <div class="inside">
                            <table
                                class="form-table"
                                style="border-collapse: collapse;"
                                width="100%"
                                cellspacing="8"
                                cellpadding="0"
                            >
                                <tbody>

                                    <tr class="clearopt-default-setting">
                                        <td colspan="2">
                                            <div class="clearopt-default-setting__header">
                                                <label for="clearopt-analytics-default">
                                                    <strong>Analytics Cookies Default</strong>
                                                </label>

                                                <label class="clearopt-switch">
                                                    <input type="checkbox" id="clearopt-analytics-default" value="1"<?php checked($settings['analytics_default'], '1'); ?>>

                                                    <span
                                                        class="clearopt-switch__slider"
                                                        aria-hidden="true"
                                                    ></span>
                                                </label>
                                            </div>

                                            <p class="description">
                                                Choose whether Analytics cookies are enabled by default for new visitors.
                                            </p>
                                        </td>
                                    </tr>

                                    <tr class="clearopt-default-setting">
                                        <td colspan="2">
                                            <div class="clearopt-default-setting__header">
                                                <label for="clearopt-marketing-default">
                                                    <strong>Marketing Cookies Default</strong>
                                                </label>

                                                <label class="clearopt-switch">
                                                    <input type="checkbox" id="clearopt-marketing-default" value="1"<?php checked($settings['marketing_default'], '1'); ?>>

                                                    <span
                                                        class="clearopt-switch__slider"
                                                        aria-hidden="true"
                                                    ></span>
                                                </label>
                                            </div>

                                            <p class="description">
                                                Choose whether Marketing cookies are enabled by default for new visitors.
                                            </p>
                                        </td>
                                    </tr>

                                    <tr class="clearopt-default-setting">
                                        <td colspan="2">
                                            <div class="clearopt-default-setting__header">
                                                <label for="clearopt-honor-gpc">
                                                    <strong>Honor Global Privacy Control (GPC)</strong>
                                                </label>

                                                <label class="clearopt-switch">
                                                    <input
                                                        type="checkbox"
                                                        id="clearopt-honor-gpc"
                                                        value="1"
                                                        <?php checked($settings['honor_gpc'], '1'); ?>
                                                    >

                                                    <span
                                                        class="clearopt-switch__slider"
                                                        aria-hidden="true"
                                                    ></span>
                                                </label>
                                            </div>

                                            <p class="description">
                                                When enabled, ClearOpt will honor a browser's Global Privacy Control signal by disabling Marketing cookies for that visitor.
                                            </p>
                                        </td>
                                    </tr>

                                    <tr class="clearopt-default-setting">
                                        <td colspan="2">
                                            <div class="clearopt-default-setting__header">
                                                <label for="clearopt-regional-consent-mode">
                                                    <strong>Smart Regional Consent</strong>
                                                </label>

                                                <select id="clearopt-regional-consent-mode">
                                                    <option
                                                        value="off"
                                                        <?php selected($settings['regional_consent_mode'], 'off'); ?>
                                                    >
                                                        Off
                                                    </option>

                                                    <option
                                                        value="auto"
                                                        <?php selected($settings['regional_consent_mode'], 'auto'); ?>
                                                    >
                                                        Automatic
                                                    </option>
                                                </select>
                                            </div>

                                            <p class="description">
                                                Automatically adjust consent behavior based on the visitor's region when location information is available.
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="postbox clearopt-menu-link clearopt-tab-overview">
                        <h3>
                            <span class="dashicons dashicons-admin-links"></span>
                            Menu Link
                        </h3>

                        <div class="inside">
                            <p>Add this custom link to any WordPress menu to open the cookie settings modal:</p>
                            <code>#cookie-preferences</code>
                            <p class="description">Existing <code>#privacy-settings</code> menu links remain supported and are automatically upgraded.</p>
                        </div>
                    </div>

                    <div class="postbox clearopt-usage clearopt-tab-overview">
                        <h3>
                            <span class="dashicons dashicons-editor-help"></span>
                            Usage
                        </h3>

                        <div class="inside">
                            <p>The banner and settings modal are automatically added to the frontend when activated.</p>
                        </div>
                    </div>
                </div>

                <div class="clear"></div>
            </div>
        </div>
    </div>
    <?php
    }

    public function ajax_save_settings()
    {
        check_ajax_referer('clearopt_save_settings', 'nonce');

        if (!current_user_can('manage_options')) :
            wp_send_json_error(array(
                'message' => 'You do not have permission to save these settings.',
            ));
        endif;

        $banner_position = isset($_POST['banner_position']) ? sanitize_key($_POST['banner_position']) : 'bottom';

        if (!in_array($banner_position, array('top', 'bottom'), true)) :
            $banner_position = 'bottom';
        endif;

        $banner_layout_style = isset($_POST['banner_layout_style']) ? sanitize_key($_POST['banner_layout_style']) : 'full_width';

        if (!in_array($banner_layout_style, array('default', 'full_width', 'floating_box'), true)) :
            $banner_layout_style = 'full_width';
        endif;

        $floating_banner_position = isset($_POST['floating_banner_position']) ? sanitize_key($_POST['floating_banner_position']) : 'bottom-right';

        if (!in_array($floating_banner_position, array('bottom-right', 'bottom-left', 'top-right', 'top-left'), true)) :
            $floating_banner_position = 'bottom-right';
        endif;

        $privacy_policy_url = isset($_POST['privacy_policy_url']) ? esc_url_raw(wp_unslash($_POST['privacy_policy_url']))  : home_url('/privacy-policy/');

        $banner_theme = isset($_POST['banner_theme']) ? sanitize_key($_POST['banner_theme']) : 'light';

        if (!in_array($banner_theme, array('light', 'dark', 'plain'), true)) :
            $banner_theme = 'light';
        endif;

        $accent_color = isset($_POST['accent_color']) ? sanitize_key($_POST['accent_color']) : 'wordpress_blue';

        if (!in_array($accent_color, array(
          'wordpress_blue',
          'navy',
          'verdant_green',
          'mocha_mousse',
          'terracotta',
          'harvest_orange',
          'muted_rose',
          'digital_lavender',
        ), true)) :
          $accent_color = 'wordpress_blue';
        endif;

        $banner_message = isset($_POST['banner_message']) ? wp_kses_post(wp_unslash($_POST['banner_message'])) : 'We use cookies to help provide the best experience possible on our website.';

        $modal_title = isset($_POST['modal_title']) ? sanitize_text_field(wp_unslash($_POST['modal_title'])) : 'Cookie Settings';

        if ($modal_title === ''):
          $modal_title = 'Cookie Settings';
        endif;

        $gtm_container_id = isset($_POST['gtm_container_id']) ? strtoupper(trim(sanitize_text_field(wp_unslash($_POST['gtm_container_id'])))) : '';

        if ($gtm_container_id && !preg_match('/^GTM-[A-Z0-9]+$/', $gtm_container_id)):
          $gtm_container_id = '';
        endif;

        $meta_pixel_id = isset($_POST['meta_pixel_id']) ? sanitize_text_field(wp_unslash($_POST['meta_pixel_id'])) : '';

        $clarity_project_id = isset($_POST['clarity_project_id']) ? sanitize_text_field(wp_unslash($_POST['clarity_project_id'])) : '';

        $analytics_default = isset($_POST['analytics_default']) && $_POST['analytics_default'] === '1' ? '1' : '0';
        
        $marketing_default = isset($_POST['marketing_default']) && $_POST['marketing_default'] === '1' ? '1' : '0';

        $honor_gpc = isset($_POST['honor_gpc']) && $_POST['honor_gpc'] === '1' ? '1' : '0';

        $regional_consent_mode = isset($_POST['regional_consent_mode']) ? sanitize_key(wp_unslash($_POST['regional_consent_mode'])) : 'off';

        if (!in_array($regional_consent_mode, array('off', 'auto'), true)) :
            $regional_consent_mode = 'off';
        endif;

        $settings = array(
          'enable_banner'   => isset($_POST['enable_banner']) && $_POST['enable_banner'] === '1' ? '1' : '0',
          'banner_position' => $banner_position,
          'banner_layout_style'  => $banner_layout_style,
          'floating_banner_position' => $floating_banner_position,
          'privacy_policy_url' => $privacy_policy_url,
          'banner_theme' => $banner_theme,
          'accent_color' => $accent_color,
          'banner_message' => $banner_message,
          'modal_title' => $modal_title,
          'gtm_container_id' => $gtm_container_id,
          'meta_pixel_id' => $meta_pixel_id,
          'clarity_project_id' => $clarity_project_id,
          'analytics_default' => $analytics_default,
          'marketing_default' => $marketing_default,
          'honor_gpc' => $honor_gpc,
          'regional_consent_mode' => $regional_consent_mode,
        );

        update_option($this->option_name, $settings);

        wp_send_json_success(array(
            'message' => 'Settings saved.',
        ));
    }

    public function ajax_cloud_connect()
    {
        check_ajax_referer('clearopt_cloud_connect', 'nonce');

        if (!current_user_can('manage_options')) :
            wp_send_json_error(array(
                'message' => 'You do not have permission to connect this site.',
            ), 403);
        endif;

        $installation_uuid = get_option('clearopt_installation_uuid', '');

        if (empty($installation_uuid)) :
            $installation_uuid = wp_generate_uuid4();

            update_option(
                'clearopt_installation_uuid',
                $installation_uuid,
                false
            );
        endif;

        $response = wp_remote_post(
            'https://api.clearopt.cloud/api/v1/connect',
            array(
                'timeout' => 20,
                'headers' => array(
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode(array(
                    'installation_uuid' => $installation_uuid,
                    'name'              => get_bloginfo('name'),
                    'url'               => home_url('/'),
                )),
            )
        );

        if (is_wp_error($response)) :
            wp_send_json_error(array(
                'message' => 'ClearOpt Cloud could not be reached.',
            ), 502);
        endif;

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);
        $data        = json_decode($body, true);

        if (
            $status_code !== 200 ||
            !is_array($data) ||
            empty($data['token'])
        ) :
            wp_send_json_error(array(
                'message' => 'ClearOpt Cloud did not return a valid connection.',
            ), 502);
        endif;

        update_option(
            'clearopt_cloud_token',
            sanitize_text_field($data['token']),
            false
        );

        update_option(
            'clearopt_cloud_connected_at',
            isset($data['connected_at'])
                ? sanitize_text_field($data['connected_at'])
                : current_time('mysql'),
            false
        );

        wp_send_json_success(array(
            'message' => 'Connected to ClearOpt Cloud.',
        ));
    }

    public function ajax_cloud_disconnect()
    {
        check_ajax_referer('clearopt_cloud_disconnect', 'nonce');

        if (!current_user_can('manage_options')) :
            wp_send_json_error(array(
                'message' => 'You do not have permission to disconnect this site.',
            ), 403);
        endif;

        $token = get_option('clearopt_cloud_token', '');

        if (empty($token)) :
            wp_send_json_error(array(
                'message' => 'This site is not connected to ClearOpt Cloud.',
            ), 400);
        endif;

        $response = wp_remote_post(
            'https://api.clearopt.cloud/api/v1/disconnect',
            array(
                'timeout' => 20,
                'headers' => array(
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ),
            )
        );

        if (is_wp_error($response)) :
            wp_send_json_error(array(
                'message' => 'ClearOpt Cloud could not be reached.',
            ), 502);
        endif;

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200 && $status_code !== 401) :
            wp_send_json_error(array(
                'message' => 'ClearOpt Cloud could not disconnect this site.',
            ), 502);
        endif;

        delete_option('clearopt_cloud_token');
        delete_option('clearopt_cloud_connected_at');
        delete_option('clearopt_cloud_cookies_found');
        delete_option('clearopt_cloud_categories');
        delete_option('clearopt_cloud_cookie_details');
        delete_option('clearopt_cloud_last_scan');

        wp_send_json_success(array(
            'message' => 'Disconnected from ClearOpt Cloud.',
        ));
    }

    public function ajax_cloud_scan()
    {
        check_ajax_referer('clearopt_cloud_scan', 'nonce');

        if (!current_user_can('manage_options')) :
            wp_send_json_error(array(
                'message' => 'You do not have permission to scan this site.',
            ), 403);
        endif;

        $token = get_option('clearopt_cloud_token', '');

        if (empty($token)) :
            wp_send_json_error(array(
                'message' => 'This site is not connected to ClearOpt Cloud.',
                'code' => 'connection_required',
            ), 400);
        endif;

        $settings = $this->get_settings();
        $declared_services = array();

        if (!empty($settings['gtm_container_id'])) {
          $declared_services[] = 'google-tag-manager';
        }

        if (!empty($settings['meta_pixel_id'])) {
          $declared_services[] = 'meta-pixel';
        }

        if (!empty($settings['clarity_project_id'])) {
          $declared_services[] = 'microsoft-clarity';
        }

        $response = wp_remote_post(
            'https://api.clearopt.cloud/api/v1/scan',
            array(
                'timeout' => 75,
                'headers' => array(
                  'Accept'        => 'application/json',
                  'Authorization' => 'Bearer ' . $token,
                  'Content-Type'  => 'application/json',
                ),
                'body' => wp_json_encode(array(
                  'services' => $declared_services,
                )),
            )
        );

        if (is_wp_error($response)) :
            wp_send_json_error(array(
                'message' => 'ClearOpt Cloud could not be reached.',
            ), 502);
        endif;

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);
        $data        = json_decode($body, true);

        if ($status_code === 401) :
            delete_option('clearopt_cloud_token');
            delete_option('clearopt_cloud_connected_at');
            delete_option('clearopt_cloud_cookies_found');
            delete_option('clearopt_cloud_last_scan');
            delete_option('clearopt_cloud_categories');
            delete_option('clearopt_cloud_cookie_details');
        
            wp_send_json_error(array(
                'message' => 'Your ClearOpt Cloud connection has expired. Please reconnect.',
                'code' => 'connection_required',
            ), 401);
        endif;

        if ($status_code !== 200 || !is_array($data)) :
            $message = isset($data['message'])
                ? sanitize_text_field($data['message'])
                : 'ClearOpt Cloud could not scan this site.';

            wp_send_json_error(array(
                'message' => $message,
            ), $status_code ?: 502);
        endif;

        $cookies_found = isset($data['cookies_found'])
            ? absint($data['cookies_found'])
            : 0;

        $categories = array(
          'necessary' => isset($data['categories']['necessary'])
              ? absint($data['categories']['necessary'])
              : 0,
          'analytics' => isset($data['categories']['analytics'])
              ? absint($data['categories']['analytics'])
              : 0,
          'marketing' => isset($data['categories']['marketing'])
              ? absint($data['categories']['marketing'])
              : 0,
          'unknown' => isset($data['categories']['unknown'])
              ? absint($data['categories']['unknown'])
              : 0,
        );

        $cookie_details = array();

        if (isset($data['cookies']) && is_array($data['cookies'])) :
            foreach ($data['cookies'] as $cookie) :
                if (!is_array($cookie) || empty($cookie['name'])) :
                    continue;
                endif;

                $category = isset($cookie['category'])
                    ? sanitize_key($cookie['category'])
                    : 'unknown';

                if (!in_array($category, array('necessary', 'analytics', 'marketing', 'unknown'), true)) :
                    $category = 'unknown';
                endif;

                $first_seen_state = isset($cookie['first_seen_state'])
                    ? sanitize_key($cookie['first_seen_state'])
                    : '';

                if (!in_array($first_seen_state, array('baseline', 'accepted'), true)) :
                    $first_seen_state = '';
                endif;

                $cookie_details[] = array(
                    'name'             => sanitize_text_field($cookie['name']),
                    'domain'           => isset($cookie['domain'])
                        ? sanitize_text_field($cookie['domain'])
                        : '',
                    'category'         => $category,
                    'first_seen_state' => $first_seen_state,
                    'provider' => isset($cookie['provider'])
                        ? sanitize_text_field($cookie['provider'])
                        : '',

                    'service' => isset($cookie['service'])
                        ? sanitize_key($cookie['service'])
                        : '',
                );
            endforeach;
        endif;

        $control_rules = array();

        if (isset($data['controls']) && is_array($data['controls'])) :
          foreach ($data['controls'] as $rule) :
            if (
              !is_array($rule) ||
              empty($rule['service']) ||
              empty($rule['category']) ||
              empty($rule['patterns']) ||
              !is_array($rule['patterns'])
            ) :
              continue;
            endif;

            $control_rules[] = array(
              'service'  => sanitize_key($rule['service']),
              'category' => sanitize_key($rule['category']),
              'target'   => isset($rule['target'])
                ? sanitize_key($rule['target'])
                : '',
              'match'    => isset($rule['match'])
                ? sanitize_key($rule['match'])
                : '',
              'patterns' => array_values(
                array_filter(
                  array_map(
                    'sanitize_text_field',
                    $rule['patterns']
                  )
                )
              ),
            );
          endforeach;
        endif;

        $scanned_at = isset($data['scanned_at'])
            ? sanitize_text_field($data['scanned_at'])
            : current_time('mysql');

        update_option(
            'clearopt_cloud_cookies_found',
            $cookies_found,
            false
        );

        update_option(
          'clearopt_cloud_categories',
          $categories,
          false
        );

        update_option(
          'clearopt_cloud_cookie_details',
          $cookie_details,
          false
        );

        update_option(
          'clearopt_cloud_control_rules',
          $control_rules,
          false
        );

        update_option(
            'clearopt_cloud_last_scan',
            $scanned_at,
            false
        );

        wp_send_json_success(array(
          'message'       => 'Site scan completed.',
          'cookies_found' => $cookies_found,
          'categories'    => $categories,
          'scanned_at'    => $scanned_at,
        ));
    }

    public function ajax_dismiss_cookie()
    {
        check_ajax_referer('clearopt_dismiss_cookie', 'nonce');

        if (!current_user_can('manage_options')) :
            wp_send_json_error(array(
                'message' => 'You do not have permission to dismiss this cookie.',
            ), 403);
        endif;

        $cookie_name = isset($_POST['cookie_name'])
            ? sanitize_text_field(wp_unslash($_POST['cookie_name']))
            : '';

        $cookie_domain = isset($_POST['cookie_domain'])
            ? sanitize_text_field(wp_unslash($_POST['cookie_domain']))
            : '';

        if (empty($cookie_name)) :
            wp_send_json_error(array(
                'message' => 'Cookie name is required.',
            ), 400);
        endif;

        $cookie_key = strtolower($cookie_name . '|' . $cookie_domain);

        $dismissed_cookies = get_option(
            'clearopt_dismissed_cookies',
            array()
        );

        if (!is_array($dismissed_cookies)) :
            $dismissed_cookies = array();
        endif;

        if (!in_array($cookie_key, $dismissed_cookies, true)) :
            $dismissed_cookies[] = $cookie_key;
        endif;

        update_option(
            'clearopt_dismissed_cookies',
            $dismissed_cookies,
            false
        );

        wp_send_json_success(array(
            'message' => 'Cookie dismissed.',
        ));
    }

    public function ajax_dismiss_scan_notice()
    {
        check_ajax_referer('clearopt_dismiss_scan_notice', 'nonce');

        if (!current_user_can('manage_options')) :
            wp_send_json_error(
                array('message' => 'You do not have permission to dismiss this notice.'),
                403
            );
        endif;

        update_option(
            'clearopt_scan_notice_dismissed',
            true,
            false
        );

        wp_send_json_success();
    }

    public function ajax_ignore_service()
    {
        check_ajax_referer('clearopt_ignore_service', 'nonce');

        if (!current_user_can('manage_options')) :
            wp_send_json_error(array(
                'message' => 'You do not have permission to ignore this service.',
            ), 403);
        endif;

        $service = isset($_POST['service'])
            ? sanitize_key(wp_unslash($_POST['service']))
            : '';

        if (empty($service)) :
            wp_send_json_error(array(
                'message' => 'Service is required.',
            ), 400);
        endif;

        $ignored_services = get_option(
            'clearopt_ignored_services',
            array()
        );

        if (!is_array($ignored_services)) :
            $ignored_services = array();
        endif;

        if (!in_array($service, $ignored_services, true)) :
            $ignored_services[] = $service;
        endif;

        update_option(
            'clearopt_ignored_services',
            $ignored_services,
            false
        );

        wp_send_json_success(array(
            'message' => 'Service ignored.',
            'service' => $service,
        ));
    }

    public function ajax_reclassify_cookie()
    {
        check_ajax_referer('clearopt_reclassify_cookie', 'nonce');

        if (!current_user_can('manage_options')) :
            wp_send_json_error(array(
                'message' => 'You do not have permission to reclassify this cookie.',
            ), 403);
        endif;

        $cookie_name = isset($_POST['cookie_name'])
            ? sanitize_text_field(wp_unslash($_POST['cookie_name']))
            : '';

        $cookie_domain = isset($_POST['cookie_domain'])
            ? sanitize_text_field(wp_unslash($_POST['cookie_domain']))
            : '';

        $category = isset($_POST['category'])
            ? sanitize_key(wp_unslash($_POST['category']))
            : '';

        if (empty($cookie_name)) :
            wp_send_json_error(array(
                'message' => 'Cookie name is required.',
            ), 400);
        endif;

        if (!in_array($category, array('necessary', 'analytics', 'marketing', 'unknown'), true)) :
            wp_send_json_error(array(
                'message' => 'Invalid cookie category.',
            ), 400);
        endif;

        $cookie_key = strtolower($cookie_name . '|' . $cookie_domain);

        $dismissed_cookies = get_option(
          'clearopt_dismissed_cookies',
          array()
        );
        
        if (is_array($dismissed_cookies)) :
        
            $dismissed_cookies = array_values(
                array_filter(
                    $dismissed_cookies,
                    function ($dismissed_cookie) use ($cookie_key) {
                        return $dismissed_cookie !== $cookie_key;
                    }
                )
            );
        
            update_option(
                'clearopt_dismissed_cookies',
                $dismissed_cookies,
                false
            );
        
        endif;

        $overrides = get_option(
            'clearopt_cookie_classification_overrides',
            array()
        );

        if (!is_array($overrides)) :
            $overrides = array();
        endif;

        $overrides[$cookie_key] = $category;

        update_option(
            'clearopt_cookie_classification_overrides',
            $overrides,
            false
        );

        wp_send_json_success(array(
            'message' => 'Cookie classification updated.',
            'category' => $category,
        ));
    }

    public function ajax_reclassify_service()
    {
        check_ajax_referer('clearopt_reclassify_service', 'nonce');

        if (!current_user_can('manage_options')) :
            wp_send_json_error(array(
                'message' => 'You do not have permission to reclassify this service.',
            ), 403);
        endif;

        $service = isset($_POST['service'])
            ? sanitize_key(wp_unslash($_POST['service']))
            : '';

        $category = isset($_POST['category'])
            ? sanitize_key(wp_unslash($_POST['category']))
            : '';

        if (empty($service)) :
            wp_send_json_error(array(
                'message' => 'Service is required.',
            ), 400);
        endif;

        if (!in_array($category, array('necessary', 'analytics', 'marketing', 'unknown'), true)) :
            wp_send_json_error(array(
                'message' => 'Invalid service category.',
            ), 400);
        endif;

        $ignored_services = get_option(
            'clearopt_ignored_services',
            array()
        );
        
        if (is_array($ignored_services)) :
        
            $ignored_services = array_values(
                array_filter(
                    $ignored_services,
                    function ($ignored_service) use ($service) {
                        return $ignored_service !== $service;
                    }
                )
            );
        
            update_option(
                'clearopt_ignored_services',
                $ignored_services,
                false
            );
        
        endif;

        $overrides = get_option(
            'clearopt_service_classification_overrides',
            array()
        );

        if (!is_array($overrides)) :
            $overrides = array();
        endif;

        $overrides[$service] = $category;

        update_option(
            'clearopt_service_classification_overrides',
            $overrides,
            false
        );

        wp_send_json_success(array(
            'message'  => 'Service classification updated.',
            'service'  => $service,
            'category' => $category,
        ));
    }

    public function ajax_block_service()
    {
        check_ajax_referer('clearopt_block_service', 'nonce');

        if (!current_user_can('manage_options')) :
            wp_send_json_error(array(
                'message' => 'You do not have permission to block this service.',
            ), 403);
        endif;

        $service = isset($_POST['service'])
            ? sanitize_key(wp_unslash($_POST['service']))
            : '';

        $service_categories = array(
            'meta-pixel'        => 'marketing',
            'microsoft-clarity' => 'analytics',
        );

        if (!isset($service_categories[$service])) :
            wp_send_json_error(array(
                'message' => 'This service is not supported for blocking.',
            ), 400);
        endif;

        if (!$this->can_block_cookie_service(array('service' => $service))) :
            wp_send_json_error(array(
                'message' => 'ClearOpt cannot currently control this service.',
            ), 400);
        endif;

        $blocked_services = get_option(
            'clearopt_blocked_services',
            array()
        );

        if (!is_array($blocked_services)) :
            $blocked_services = array();
        endif;

        $blocked_services[$service] = $service_categories[$service];

        update_option(
            'clearopt_blocked_services',
            $blocked_services,
            false
        );

        wp_send_json_success(array(
            'message'  => 'Service will be blocked until consent is granted.',
            'service'  => $service,
            'category' => $service_categories[$service],
        ));
    }

    public function render_cookie_banner()
    {
        $settings = $this->get_settings();

        if ($settings['enable_banner'] !== '1') :
            return;
        endif;

        $banner_class  = 'clearopt-cookie-dialog--' . $settings['banner_layout_style'];

        if ($settings['banner_layout_style'] === 'floating_box') :
            $banner_class .= ' clearopt-cookie-dialog--' . $settings['floating_banner_position'];
        else :
            $banner_class .= ' clearopt-cookie-dialog--' . $settings['banner_position'];
        endif; 
        
        $banner_class .= ' clearopt-cookie-dialog--' . $settings['banner_theme'];

        $banner_class .= ' clearopt-accent--' . $settings['accent_color'];

        $banner_message = str_replace(
          '{{privacy_policy_url}}',
          esc_url($settings['privacy_policy_url']),
          $settings['banner_message']
        ); ?>

        <div class="clearopt-cookie-dialog <?php echo esc_attr($banner_class); ?>" id="clearopt-cookie-dialog" hidden>
            <div class="clearopt-cookie-dialog__inner">
                <p class="clearopt-message">
                  <?php echo wp_kses_post($banner_message); ?>
                </p>

                <div class="clearopt-cookie-dialog__actions">
                  <button type="button" id="clearopt-cookie-agree">
                    Accept All Cookies
                  </button>
                </div>
            </div>
        </div>

        <div
            class="clearopt-cookie-modal clearopt-cookie-modal--<?php echo esc_attr($settings['banner_theme']); ?> clearopt-accent--<?php echo esc_attr($settings['accent_color']); ?>"
            id="clearopt-cookie-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="cookie-preferences-title"
            hidden
        >
            <div class="clearopt-cookie-modal__overlay" id="clearopt-cookie-modal-overlay"></div>

            <div class="clearopt-cookie-modal__panel" id="cookie-preferences" tabindex="-1">
                <button
                    type="button"
                    class="clearopt-cookie-modal__close"
                    id="clearopt-close-cookie-settings"
                    aria-label="Close cookie settings"
                >
                    ×
                </button>

                <h2 id="cookie-preferences-title"><?php echo esc_html($settings['modal_title']); ?></h2>

                <div class="clearopt-cookie-modal__option">
                    <label class="clearopt-cookie-switch">
                        <input type="checkbox" checked disabled>
                        <span class="clearopt-cookie-switch__control" aria-hidden="true"></span>
                        <span class="clearopt-cookie-switch__label">Necessary Cookies</span>
                    </label>

                    <div class="clearopt-cookie-modal__option-copy">
                        <p>These cookies are required for the website to function.</p>
                    </div>
                </div>

                <div class="clearopt-cookie-modal__option">
                    <label class="clearopt-cookie-switch">
                        <input type="checkbox" id="clearopt-cookie-analytics">
                        <span class="clearopt-cookie-switch__control" aria-hidden="true"></span>
                        <span class="clearopt-cookie-switch__label">Analytics Cookies</span>
                    </label>

                    <div class="clearopt-cookie-modal__option-copy">
                        <p>These help us understand how visitors use the website.</p>
                    </div>
                </div>

                <div class="clearopt-cookie-modal__option">
                    <label class="clearopt-cookie-switch">
                        <input type="checkbox" id="clearopt-cookie-marketing">
                        <span class="clearopt-cookie-switch__control" aria-hidden="true"></span>
                        <span class="clearopt-cookie-switch__label">Marketing Cookies</span>
                    </label>

                    <div class="clearopt-cookie-modal__option-copy">
                        <p>These help support advertising and marketing features.</p>
                    </div>
                </div>

                <div class="clearopt-cookie-modal__actions">
                    <button type="button" id="clearopt-cookie-accept-all">
                        Accept All
                    </button>

                    <button type="button" id="clearopt-cookie-reject">
                        Reject Non-Essential
                    </button>

                    <button type="button" id="clearopt-cookie-save">
                        Save Preferences
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_service_health_status(array $service_data): string
    {

        $service = $service_data['service'] ?? '';

        $ignored_services = get_option(
            'clearopt_ignored_services',
            array()
        );
        
        if (
            is_array($ignored_services) &&
            in_array($service, $ignored_services, true)
        ) {
            return 'dismissed';
        }

        $category = $service_data['category'] ?? 'unknown';
        $cookies = $service_data['cookies'] ?? array();

        if ($category === 'necessary') {
            return 'green';
        }

        if ($category === 'unknown') {
            return 'yellow';
        }

        if (in_array($category, array('analytics', 'marketing'), true)) {

          $settings = $this->get_settings();
      
          $default_enabled = $category === 'analytics'
              ? ($settings['analytics_default'] ?? '0') === '1'
              : ($settings['marketing_default'] ?? '0') === '1';
      
          $seen_at_baseline = false;
      
          foreach ($cookies as $cookie) {
              if (($cookie['first_seen_state'] ?? '') === 'baseline') {
                  $seen_at_baseline = true;
                  break;
              }
          }
      
          if ($default_enabled) {
              return 'green';
          }
          
          return $seen_at_baseline ? 'red' : 'green';
        }

        return 'yellow';
    }

    private function get_cookie_health_status(array $cookie): string
    {

        $cookie_key = strtolower(
          ($cookie['name'] ?? '') . '|' . ($cookie['domain'] ?? '')
        );
        
        $dismissed_cookies = get_option(
            'clearopt_dismissed_cookies',
            array()
        );
        
        if (
            is_array($dismissed_cookies) &&
            in_array($cookie_key, $dismissed_cookies, true)
        ) {
            return 'dismissed';
        }


        $category = $cookie['category'] ?? 'unknown';
        $first_seen_state = $cookie['first_seen_state'] ?? '';

        if ($category === 'necessary') {
            return 'green';
        }

        if ($category === 'unknown') {
            return 'yellow';
        }

        if (in_array($category, array('analytics', 'marketing'), true)) {

          $settings = $this->get_settings();
      
          $default_enabled = $category === 'analytics'
              ? ($settings['analytics_default'] ?? '0') === '1'
              : ($settings['marketing_default'] ?? '0') === '1';
      
          if ($first_seen_state === 'baseline') {
              return $default_enabled ? 'green' : 'red';
          }
          
          if ($first_seen_state === 'accepted') {
              return 'green';
          }
        }

        return 'yellow';
    }

    private function can_block_cookie_service(array $cookie): bool
    {
        $service = $cookie['service'] ?? '';
        $settings = $this->get_settings();

        if (
            $service === 'meta-pixel' &&
            !empty($settings['meta_pixel_id'])
        ) {
            return true;
        }

        if (
            $service === 'microsoft-clarity' &&
            !empty($settings['clarity_project_id'])
        ) {
            return true;
        }

        return false;
    }

    private function is_external_service_load_candidate(array $cookie): bool
    {
        if (($cookie['first_seen_state'] ?? '') !== 'baseline') {
            return false;
        }

        $service = $cookie['service'] ?? '';
        $settings = $this->get_settings();

        if (
            $service === 'meta-pixel' &&
            !empty($settings['meta_pixel_id']) &&
            ($settings['marketing_default'] ?? '0') !== '1'
        ) {
            return true;
        }

        if (
            $service === 'microsoft-clarity' &&
            !empty($settings['clarity_project_id']) &&
            ($settings['analytics_default'] ?? '0') !== '1'
        ) {
            return true;
        }

        return false;
    }

}

new ClearOpt_Cookie_Consent();