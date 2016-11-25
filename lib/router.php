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
        self::$_PATH = explode('/', trim($_SERVER['PATH_INFO'], '/'));
        while (count(self::$_PATH) > 0 && self::$_PATH[0] === '') {
            array_shift(self::$_PATH);
        };

        // Eat up initial directory as language if it's 2 characters
        $lang = 'en';
        if (isset(self::$_PATH[0]) && strlen(self::$_PATH[0]) === 2) {
            $lang = strtolower(array_shift(self::$_PATH));
        };
        trigger('lang_changed', $lang);

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
}

on('startup', 'Router::startup', 50);
