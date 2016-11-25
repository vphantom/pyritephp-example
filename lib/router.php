<?php

/**
 * URL Router
 *
 * PHP version 5
 *
 * @category  Library
 * @package   PyritePHP
 * @author    Stéphane Lavergne <lis@imars.com>
 * @copyright 2016 Stéphane Lavergne
 * @license   https://opensource.org/licenses/MIT  MIT
 * @link      https://github.com/vphantom/pyrite-php
 */

/**
 * Router class
 *
 * PHP version 5
 *
 * @category  Library
 * @package   PyritePHP
 * @author    Stéphane Lavergne <lis@imars.com>
 * @copyright 2016 Stéphane Lavergne
 * @license   https://opensource.org/licenses/MIT  MIT
 * @link      https://github.com/vphantom/pyrite-php
 */
class Router
{
    private static $_base = null;
    private static $_PATH = array();
    private static $_req = array();

    /**
     * Build route from requested URL
     *
     * - Extract language from initial '/xx/'
     * - Build route/foo+bar if handled, route/foo otherwise
     * - Default to route/main
     * - Trigger 404 status if not handled at all
     *
     * @return null
     */
    public static function startup()
    {
        self::$_req['status'] = 200;
        self::$_req['redirect'] = false;

        self::$_PATH = explode('/', trim($_SERVER['PATH_INFO'], '/'));
        while (count(self::$_PATH) > 0 && self::$_PATH[0] === '') {
            array_shift(self::$_PATH);
        };

        // Eat up initial directory as language if it's 2 characters
        $lang = PV_DEFAULT_LANG;
        if (isset(self::$_PATH[0]) && strlen(self::$_PATH[0]) === 2) {
            $lang = strtolower(array_shift(self::$_PATH));
        };
        self::$_req['lang'] = $lang;
        self::$_req['default_lang'] = PV_DEFAULT_LANG;
        self::$_req['base'] = ($lang === PV_DEFAULT_LANG ? '' : "/{$lang}");
        self::$_req['path'] = implode('/', self::$_PATH);
        self::$_req['query'] = ($_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
        trigger('language', $lang);

        if (isset(self::$_PATH[1]) && listeners('route/' . self::$_PATH[0] . '+' . self::$_PATH[1])) {
            self::$_base = array_shift(self::$_PATH) . '+' . array_shift(self::$_PATH);
        } elseif (isset(self::$_PATH[0])) {
            if (listeners('route/' . self::$_PATH[0])) {
                self::$_base = array_shift(self::$_PATH);
            } else {
                trigger('http_status', 404);
            };
        } elseif (listeners('route/main')) {
            self::$_base = 'main';
        } else {
            trigger('http_status', 404);
        };
    }

    /**
     * Trigger handler for current route
     *
     * @return null
     */
    public static function run()
    {
        if (self::$_base !== null  &&  !pass('route/' . self::$_base, self::$_PATH)) {
            trigger('http_status', 500);
        };
    }

    /**
     * Return request data
     *
     * Keys provided:
     *
     * lang: Current language code
     * base: Prepend this before '/' to get an absolute URL for current language
     * path: Current component's URL
     * query: GET query string, if any
     * status: Integer of current HTTP status
     * redirect: False or string intended for URL redirection
     *
     * @return array Associative data
     */
    public static function getRequest()
    {
        return self::$_req;
    }

    /**
     * Set HTTP response status code
     *
     * @param int $code New code (between 100 and 599)
     *
     * @return null
     */
    public static function setStatus($code)
    {
        if ($code >= 100  &&  $code < 600) {
            self::$_req['status'] = $code;
        };
    }

    /**
     * Set HTTP redirect URL
     *
     * This only sets req.redirect: it is up to other components or templates
     * to act upon it.
     *
     * @param string $url The new location (can be relative)
     *
     * @return null
     */
    public static function setRedirect($url)
    {
        self::$_req['redirect'] = $url;
    }
}

on('startup', 'Router::startup', 50);
on('request', 'Router::getRequest');
on('http_status', 'Router::setStatus');
on('http_redirect', 'Router::setRedirect');
