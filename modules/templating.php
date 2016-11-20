<?php

/**
 * Twigger
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
 * Twigger class
 *
 * @category  Library
 * @package   PyritePHP
 * @author    Stéphane Lavergne <lis@imars.com>
 * @copyright 2016 Stéphane Lavergne
 * @license   https://opensource.org/licenses/MIT  MIT
 * @link      https://github.com/vphantom/pyrite-php
 */
class Twigger
{
    private static $_twig;
    private static $_title = '';
    private static $_status = 200;
    private static $_template;
    private static $_safeBody = '';

    /**
     * Initialize wrapper around Twig templating and display headers
     *
     * @return null
     */
    public static function startup()
    {
        $twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem(__DIR__ . '/../templates'),
            array(
                // 'cache' => __DIR__ . '/var/twig_cache',
                'autoescape' => true,
            )
        );
        $twig->addFunction(
            new \Twig_SimpleFunction(
                'grab', function () {
                    return array_pop(call_user_func_array('trigger', func_get_args()));
                }
            )
        );
        $twig->addFunction(
            new \Twig_SimpleFunction(
                'pass', function () {
                    return array_pop(call_user_func_array('trigger', func_get_args())) !== false;
                }
            )
        );
        $twig->addFunction(
            new \Twig_SimpleFunction(
                'filter', function () {
                    return call_user_func_array('filter', func_get_args());
                }
            )
        );
        self::$_twig = $twig;
        self::$_template = $twig->loadTemplate('layout.html');

        if (self::$_status !== 200) {
            http_response_code(self::$_status);
        };
        echo self::$_template->renderBlock('head', array('http_status' => self::$_status, 'session' => $_SESSION));
        flush();
        ob_start();
    }

    /**
     * Clean up content capture and display main template
     *
     * @return null
     */
    public static function shutdown()
    {
        $name = 'body';
        if (self::$_status !== 200) {
            $name .= '_error';
        };
        $body = ob_get_contents();
        ob_end_clean();
        echo self::$_template->renderBlock(
            $name,
            array(
                'http_status' => self::$_status,
                'title' => self::$_title,
                'body' => self::$_safeBody,
                'stdout' => $body,
                'session' => $_SESSION
            )
        );
    }

    /**
     * Set HTTP response status code
     *
     * @param int $code New code (between 100 and 599)
     *
     * @return null
     */
    public static function status($code)
    {
        if ($code >= 100  &&  $code < 600) {
            self::$_status = (int)$code;
        };
    }

    /**
     * Prepend new section to page title
     *
     * @param string $prepend New section of title text
     * @param string $sep     Separator with current title
     *
     * @return null
     */
    public static function title($prepend, $sep = ' - ')
    {
        self::$_title = $prepend . (self::$_title !== '' ? ($sep . self::$_title) : '');
    }

    /**
     * Render a template file
     *
     * @param string $name File name from within templates/
     * @param array  $args Associative array of variables to pass along
     *
     * @return null
     */
    public static function render($name, $args = array())
    {
        $env = array_merge(
            $args, array(
                'session' => $_SESSION
            )
        );
        self::$_safeBody .= self::$_twig->render($name, $env);
    }
}

on('startup', 'Twigger::startup', 99);
on('shutdown', 'Twigger::shutdown', 1);
on('render', 'Twigger::render');
on('title', 'Twigger::title');
on('http_status', 'Twigger::status');

