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
    private static $_section = false;
    private static $_status = 200;
    private static $_template;
    private static $_safeBody = '';
    private static $_lang = 'en';  // Could be '', paranoid precaution

    /**
     * Initialize wrapper around Twig templating and display headers
     *
     * @return null
     */
    public static function startup()
    {
        global $PPHP;
        self::$_lang = $PPHP['config']['global']['default_lang'];
        $tplBase = __DIR__ . '/../templates';
        $twigLoader = new \Twig_Loader_Filesystem();

        // Don't choke if language from URL is bogus
        try {
            $twigLoader->addPath($tplBase . '/' . self::$_lang);
        } catch (Exception $e) {
        };

        // Be nice, don't even choke if templates aren't sorted by language
        if (self::$_lang !== $PPHP['config']['global']['default_lang']) {
            try {
                $twigLoader->addPath($tplBase . '/' . $PPHP['config']['global']['default_lang']);
            } catch (Exception $e) {
            };
        };

        $twigLoader->addPath($tplBase);
        $twig = new \Twig_Environment(
            $twigLoader,
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
        $twig->addFunction(
            new \Twig_SimpleFunction(
                'title', function () {
                    return call_user_func_array('self::title', func_get_args());
                }
            )
        );
        $twig->addFunction(
            new \Twig_SimpleFunction(
                'section', function () {
                    return call_user_func_array('self::section', func_get_args());
                }
            )
        );

        // Load utilities globally
        try {
            $twig->addGlobal('lib', $twig->loadTemplate('lib'));
        } catch (Exception $e) {
            echo $e->getMessage();
        };

        self::$_twig = $twig;

        try {
            self::$_template = $twig->loadTemplate('layout.html');

            if (self::$_status !== 200) {
                http_response_code(self::$_status);
            };
            self::$_template->displayBlock(
                'head',
                array(
                    'session' => $_SESSION,
                    'req' => grab('request'),
                    'post' => $_POST
                )
            );
        } catch (Exception $e) {
            echo $e->getMessage();
        };
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
        $body = ob_get_contents();
        ob_end_clean();
        try {
            self::$_template->displayBlock(
                'body',
                array(
                    'title' => self::$_title,
                    'section' => self::$_section,
                    'body' => self::$_safeBody,
                    'stdout' => $body,
                    'session' => $_SESSION,
                    'req' => grab('request'),
                    'post' => $_POST
                )
            );
        } catch (Exception $e) {
            echo $e->getMessage();
        };
    }

    /**
     * Set current language
     *
     * @param string $code Two-letter language code
     *
     * @return null
     */
    public static function setLang($code)
    {
        self::$_lang = $code;
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
     * Overwrite name of current section
     *
     * We're using this in navigation to decide which section to
     * highlight/expand.
     *
     * @param string $section Name of section
     *
     * @return null
     */
    public static function section($section)
    {
        self::$_section = $section;
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
            $args,
            array(
                'title' => self::$_title,
                'session' => $_SESSION,
                'req' => grab('request'),
                'post' => $_POST
            )
        );
        try {
            self::$_safeBody .= self::$_twig->render($name, $env);
        } catch (Exception $e) {
            echo $e->getMessage();
        };
    }

    /**
     * Render all blocks from a template
     *
     * @param string $name File name from within templates/
     * @param array  $args Associative array of variables to pass along
     *
     * @return array Associative array of all blocks rendered from the template
     */
    public static function renderBlocks($name, $args = array())
    {
        $env = array_merge(
            $args,
            array(
                'session' => $_SESSION,
                'req' => grab('request'),
                'post' => $_POST
            )
        );
        try {
            $template = self::$_twig->loadTemplate($name);
            $blockNames = $template->getBlockNames($env);
            $results = array();
            foreach ($blockNames as $blockName) {
                $results[$blockName] = $template->renderBlock($blockName, $env);
            };
        } catch (Exception $e) {
            echo $e->getMessage();
        };
        return $results;
    }
}

on('startup', 'Twigger::startup', 99);
on('shutdown', 'Twigger::shutdown', 1);
on('render', 'Twigger::render');
on('render_blocks', 'Twigger::renderBlocks');
on('title', 'Twigger::title');
on('section', 'Twigger::section');
on('http_status', 'Twigger::status');
on('language', 'Twigger::setLang');
