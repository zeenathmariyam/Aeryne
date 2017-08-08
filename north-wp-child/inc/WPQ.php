<?php
class WPQ {
    private static $_queryStack = array();
    private static $currentArgs = null;

    public static function hasResults($args = array(), $paginate = false) {
        $query = self::_createQuery($args, $paginate);
        return $query->have_posts();
    }

    public static function get($args = array(), $paginate = false) {
        $query = self::_createQuery($args, $paginate);
        return $query->get_posts();
    }

    public static function loop($args = array(), $paginate = false) {
        self::startOrContinue($args, $paginate);

        if (!have_posts()) {
            self::end();
            return false;
        }

        the_post();
        return true;
    }

    public static function startOrContinue($args = array(), $paginate = false) {
        if (self::$currentArgs == $args)
            return;

        self::start($args, $paginate);
    }

    public static function start($args = array(), $paginate = false) {
        self::_query($args, $paginate);
    }

    public static function end() {
        if (empty(self::$_queryStack))
            return;

        global $wp_query;

        $wp_query = array_pop(self::$_queryStack);
        wp_reset_postdata();
    }

    private static function _query($args = array(), $paginate = false) {
        global $wp_query;

        self::$_queryStack[] = $wp_query;
        $wp_query = self::_createQuery($args, $paginate);
        self::$currentArgs = $args;
    }

    private static function _createQuery($args = array(), $paginate = false) {
        if ($paginate)
            $args['paged'] = self::_getCurrentPage();

        return new WP_Query($args);
    }

    private static function _getCurrentPage() {
        $page = (get_query_var('page')) ? get_query_var('page') : get_query_var('paged');
        return ($page) ? $page : 1;
    }
}