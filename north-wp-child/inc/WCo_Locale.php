<?php
class WCo_Locale {
    const DEFAULT_LOCALE = 'xx';

    // Init vars
    private $_id;
    private $_label;
    private $_currency;
    private $_country;
    private $_language;
    private $_klarna;

    // Instance vars
    private $_baseURL;
    private $_langURL;
    private $_currentURL;

    // Cache
    private static $_locales;
    private static $_languages;
    private static $_defaultLanguage;
    private static $_customRewrites = array();

    // Global instance
    private static $_locale = null;
    private static $_homeURL = null;

    public static function init() {
        // Init language
        global $sitepress;

        if ($sitepress)
            self::$_defaultLanguage = $sitepress->get_default_language();

        self::addAJAX('geoip', array(__CLASS__, 'geoIPAJAX'));

        add_filter('geoip_detect2_client_ip', function($ip, $ip_list) {
            return $ip_list[0];
        }, 10, 2);

        // Init rewrites
        add_filter('rewrite_rules_array', array(__CLASS__, 'addRewrites'));

        //'woocommerce_load_shipping_methods'

        add_action('woocommerce_flat_rate_shipping_add_rate', function($method, $rate) {
            global $woocommerce_wpml;

            if (!isset($woocommerce_wpml))
                return;

            if ($woocommerce_wpml->multi_currency->get_client_currency() == get_option('woocommerce_currency'))
                return;

            if (!isset($rate['id']))
                return;

            if (!isset($rate['cost']))
                return;

            if (!isset($method->rates[$rate['id']]))
                return;

            $method->rates[$rate['id']]->cost = $rate['cost'];
        }, 10, 2);

        if ((is_admin() && !is_ajax()) || (isset($_POST['icl_ajx_action']) && is_ajax()))
            return;

        // Init locale
        $localeID = !empty($_GET['locale'])?$_GET['locale']:self::_getURLLanguage();

        $locale = WCo_Locale::getLocale($localeID);

        if (!$locale && $ajaxLocale = self::_getAJAXLanguage())
            $locale = WCo_Locale::getLocale($ajaxLocale);

        if (!$locale)
            $locale = WCo_Locale::getLocale(self::DEFAULT_LOCALE);

        $locale->setupMain();

        self::$_locale = $locale;

        global $sitepress;
        $default = WCo_Locale::getLocale(self::DEFAULT_LOCALE);
        self::$_homeURL = trailingslashit($sitepress->language_url($default->language));

        // Add actions
        add_action('init', function () {
            if (!self::$_locale)
                return;

            self::$_locale->setupInit();

            if (!is_ajax()) {
                wp_enqueue_style('locale', get_stylesheet_directory_uri().'/locale/css/style.css', null, null);
                wp_enqueue_script('locale', get_stylesheet_directory_uri().'/locale/script.js', array('jquery'), null, true);
                wp_localize_script('locale', 'wco_locale', array(
                    'id' => self::$_locale->id,
                    'ajax' => add_query_arg('action', 'geoip', admin_url('admin-ajax.php'))
                ));

                add_filter('body_class', function ($classes) {
                    $classes[] = 'has-locale-bar';
                    return $classes;
                }, 9999);

                add_action('wp_footer', function () {
                    get_template_part('locale/bar');
                }, 9999);
            }
        }, 3);


        add_action('wp', function () {
            self::$_languages = apply_filters('wpml_active_languages', '', array(
                'skip_missing' => 0
            ));
        });

        add_action('init', function() {
            add_filter('home_url', function($url) {
                if (!self::$_locale)
                    return $url;

                return self::$_locale->getURL($url);
            });
        });
    }

    public static function addAJAX($name, $function) {
        add_action('wp_ajax_'.$name, $function);
        add_action('wp_ajax_nopriv_'.$name, $function);
    }

    public static function geoIPAJAX() {
        $result = array(
            'l' => self::DEFAULT_LOCALE,
        );

        if (function_exists('geoip_detect2_get_client_ip')) {
            $ip = geoip_detect2_get_client_ip();
            $data = geoip_detect2_get_info_from_ip($ip);

            $locale = self::_ISOLocale($data->country->isoCode);

            $result['l'] = $locale->id;
        }

        die(json_encode($result));
    }

    public static function addRewrites( $rules ) {
        $prefixRE = '(' . implode( '|', self::getCustomRewrites() ) . ')/';
        $newRules = array();

        $match_pattern = '/\$matches\[(\d)\]/si';

        $newRules[ $prefixRE . '?$' ] = 'index.php?locale=$matches[1]';

        foreach ( $rules as $rule => $match ) {
            if (strpos($rule, $prefixRE) === 0)
                continue;

            // Incrementing $match becaouse prefixes goes as capturing group.
            $new = preg_replace_callback( $match_pattern , function( $m ){
                    return sprintf('$matches[%d]', $m[1] + 1);
                }, $match).'&locale=$matches[1]';

            // Adding Prefix Group and New Value for our match.
            $newRules[ $prefixRE . $rule ] = $new;
            $newRules[ $rule ] = $match;
        }

        return $newRules;
    }

    public static function getCustomRewrites() {
        if (!empty(self::$_customRewrites))
            return self::$_customRewrites;

        $default = self::$_defaultLanguage;

        foreach(self::getLocales() as $l) {
            if ($default && $l->language != $default)
                continue;

            if ($l->id == self::DEFAULT_LOCALE)
                continue;

            self::$_customRewrites[] = $l->id;
        }

        return self::$_customRewrites;
    }

    public static function getLocales() {
        self::_defineLocales();

        return self::$_locales;
    }

    public static function getLocale($id) {
        if (!$id)
            return null;

        self::_defineLocales();

        if (isset(self::$_locales[$id]))
            return self::$_locales[$id];

        return null;
    }

    public static function currentLocale() {
        return self::$_locale;
    }

    private static function _getURLLanguage() {
        $url = untrailingslashit($_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ] );

        $url = wpml_strip_subdir_from_url ( $url );

        if ( strpos ( $url, 'http://' ) === 0 || strpos ( $url, 'https://' ) === 0 ) {
            $url_path = wpml_parse_url ( $url, PHP_URL_PATH );
        } else {
            $pathparts = array_filter ( explode ( '/', $url ) );
            if ( count ( $pathparts ) > 1 ) {
                unset( $pathparts[ 0 ] );
                $url_path = implode ( '/', $pathparts );
            } else {
                $url_path = $url;
            }
        }

        $fragments = array_filter ( (array) explode ( "/", $url_path ) );
        $lang      = array_shift ( $fragments );

        $lang_get_parts = explode( '?', $lang );
        $lang           = $lang_get_parts[ 0 ];

        return ($lang != $_SERVER['HTTP_HOST'])?$lang:null;
    }

    public static function _getAJAXLanguage() {
        if (!defined('DOING_AJAX'))
            return null;

        if (!isset($_REQUEST['action']))
            return null;

        if (strpos($_REQUEST['action'], 'kco_') !== 0 && strpos($_REQUEST['action'], 'klarna_checkout_') !== 0 && $_REQUEST['action'] != 'update_order_review')
            return null;

        $country = '';

        if (strpos($_REQUEST['action'], 'kco_') === 0 && isset($_REQUEST['country']))
            $country = $_REQUEST['country'];

        $mapping = array(
            'swe' => 'sv',
            'nor' => 'no',
            'aut' => 'at',
            'fin' => 'fi',
            'deu' => 'de',
        );

        if (isset($mapping[$country]))
            return $mapping[$country];

        return null;
    }

    // Construct
    public function __construct($id, $label, $currency, $country = null, $language = null, $klarna = false) {
        if (!$language)
            $language = $id;

        $this->_genericInit(get_defined_vars());

        global $sitepress;

        if ($sitepress) {
            $this->_langURL = trailingslashit($sitepress->language_url($this->id));

            add_action('wp', function () {
                if (isset(self::$_languages[$this->id]))
                    $this->_currentURL = $this->getURL(self::$_languages[$this->id]['url']);
                else
                    $this->_currentURL = $this->getURL(self::$_languages[self::$_defaultLanguage]['url']);
            }, 99);
        }
    }

    public function __get($name) {
        if (!property_exists($this, $name))
            $name = '_'.$name;

        if (property_exists($this, $name))
            return $this->{$name};

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    public function setupMain() {
        $country = $this->_country;
        $currency = $this->_currency;

        add_filter('klarna_country', function() use ($country) {
            return $country;
        });

        add_filter('wcml_client_currency', function() use ($currency) {
            return $currency;
        });

        if (!$this->_klarna)
            add_filter('klarna_checkout_url', function() {
                return '';
            });
    }

    public function setupInit() {
        global $woocommerce_wpml;

        $multi_currency =& $woocommerce_wpml->multi_currency;

        $multi_currency->set_client_currency($this->_currency);

        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ($available_gateways['klarna_checkout']) {
            $country = $this->_country;
            $klarna = $available_gateways['klarna_checkout'];

            add_filter('woocommerce_get_checkout_url', function ($url) use ($klarna, $country) {
                if (!empty($klarna->{'klarna_checkout_url_'.strtolower($country)}))
                    $url = $klarna->{'klarna_checkout_url_'.strtolower($country)};

                return self::$_locale->getURL($url);
            }, 99);
        }

        if (WC()->session) {
            WC()->session->set('klarna_country', $this->_country);
            WC()->session->set('klarna_euro_country', ($this->_currency == 'EUR') ? $this->_country : null);
        }
    }

    public function getURL($url) {
        if (self::$_locale->id != $this->id) {
            $url = str_replace(self::$_locale->langURL, self::$_homeURL, $url);
            $url = str_replace($this->langURL.self::$_locale->id.'/', $this->langURL, $url);
        }

        if (!in_array($this->id, self::getCustomRewrites()))
            return $url;

        if ($this->langURL && (strpos($url, $this->langURL) === false))
            return str_replace(self::$_homeURL, $this->langURL, $url);

        return $url;
    }

    public function render() {
        return sprintf(
            '<li><a class="site-%1$s" data-id="%1$s" href="%2$s">%3$s</a></li>',
            $this->id,
            $this->_currentURL,
            $this->_label
        );
    }

    private static function _ISOLocale($country) {
        $country = strtolower($country);

        if ($country == 'se')
            $country = 'sv';

        if ($locale = self::getLocale($country))
            return $locale;

        return self::getLocale(self::DEFAULT_LOCALE);
    }

    private static function _defineLocales() {
        if (empty(self::$_locales)) {
            $locales = array(
                new WCo_Locale('sv', 'Sweden | Sverige', 'SEK', 'SE', null, true),
                new WCo_Locale('no', 'Norway | Norge', 'NOK', 'NO', 'en', true),
                new WCo_Locale('fi', 'Finland | Suomi', 'EUR', 'FI', 'en', true),
                new WCo_Locale('fr', 'France', 'EUR'),
                new WCo_Locale(self::DEFAULT_LOCALE, 'International', 'EUR', null, 'en'),
            );

            global $woocommerce_wpml;

            $wcml_settings = $woocommerce_wpml->get_settings();

            foreach ($locales as $i => $o) {
                if (!empty($wcml_settings['currency_options'][$o->currency]['languages'][$o->language]))
                    self::$_locales[$o->id] = $o;
            }
        }
    }

    public static function removeFilterForcibly($tag, $function_to_remove, $priority = 10) {
        global $wp_filter;

        foreach ($wp_filter[$tag][$priority] as $k => $f) {
            if (self::_removeFilterForciblyMatch($function_to_remove, $k)) {
                unset($wp_filter[$tag][$priority][$k]);
                return true;
            }
        }

        return false;
    }

    public static function changeFilterPriority($tag, $function, $newPriority, $oldPriority = 10) {
        global $wp_filter;

        foreach ($wp_filter[$tag][$oldPriority] as $k => $f) {
            if (self::_removeFilterForciblyMatch($function, $k)) {
                $wp_filter[$tag][$newPriority][$k] = $wp_filter[$tag][$oldPriority][$k];
                unset($wp_filter[$tag][$oldPriority][$k]);
                return true;
            }
        }

        return false;
    }

    private static function _removeFilterForciblyMatch($function_to_remove, $function_to_check) {
        if ($function_to_check === $function_to_remove)
            return true;

        if (is_array($function_to_remove)) {
            $static = $function_to_remove[0] . '::' . $function_to_remove[1];
            if ($function_to_check === $static)
                return true;

            if (!function_exists('spl_object_hash')) {
                $noSPL = $function_to_remove[0] . $function_to_remove[1];

                if (preg_match('/^' . preg_quote($noSPL, '/') . '\d+$/', $function_to_check))
                    return true;
            }
            else
                $function_to_remove = $function_to_remove[1];
        }

        if (preg_match('/^[0-9a-f]{32}'.preg_quote($function_to_remove, '/').'$/', $function_to_check))
            return true;

        return false;
    }

    /**
     * Initialize all instance variables from constructor.
     * Call using $this->_genericInit(get_defined_vars())
     * This will take any constructor parameters and any
     * variables defined in constructor *before* call and
     * attempt to initialize corresponding instance variables.
     */
    private function _genericInit($params) {
        foreach ($params as $name => $value) {
            if (!property_exists($this, $name))
                $name = '_'.$name;

            if (!property_exists($this, $name))
                continue;

            $this->{$name} = $value;
        }
    }
}

WCo_Locale::init();