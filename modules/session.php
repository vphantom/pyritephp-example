<?php

/**
 * Session
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
 * Session class
 *
 * @category  Library
 * @package   PyritePHP
 * @author    Stéphane Lavergne <lis@imars.com>
 * @copyright 2016 Stéphane Lavergne
 * @license   https://opensource.org/licenses/MIT  MIT
 * @link      https://github.com/vphantom/pyrite-php
 */

class Session
{

    /**
     * Discover and initialize session
     *
     * @return null
     */
    public static function startup()
    {
        global $PPHP;
        $config = $PPHP['config']['session'];

        // Start a PHP-handled session and bind it to the current remote IP address as
        // a precaution per https://www.owasp.org/index.php/PHP_Security_Cheat_Sheet
        ini_set('session.gc_maxlifetime', $config['gc_maxlifetime'] * 60);
        ini_set('session.cookie_lifetime', $config['cookie_lifetime'] * 60);
        ini_set('session.cookie_httponly', true);
        session_start();
        if (isset($_SESSION['ip'])) {
            if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
                self::reset();
            };
        } else {
            self::_init();
        };
    }

    /**
     * Clean up and save session
     *
     * @return null
     */
    public static function shutdown()
    {
        session_write_close();
    }

    /**
     * Populate session with fresh starting values
     *
     * @return null
     */
    private static function _init()
    {
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user'] = null;
        $_SESSION['identified'] = false;
    }

    /**
     * Wipe out and re-initialize current session
     *
     * @return null
     */
    public static function reset()
    {
        session_unset();
        self::_init();
    }

    /**
     * Attempt to attach a user to current session
     *
     * @param string $email    E-mail address
     * @param string $password Plain text password (supplied via web form)
     *
     * @return bool Whether the operation succeeded
     */
    public static function login($email, $password)
    {
        if (is_array($user = grab('authenticate', $email, $password))) {
            self::reset();
            $_SESSION['user'] = $user;
            $_SESSION['identified'] = true;
            trigger('newuser');
            return true;
        } else {
            return false;
        };
    }

    /**
     * Refresh session cache of user data
     *
     * @param array $data New user information
     *
     * @return null
     */
    public static function reloadUser($data)
    {
        $_SESSION['user'] = $data;
    }
}

on('startup', 'Session::startup', 1);
on('shutdown', 'Session::shutdown', 99);
on('login', 'Session::login', 1);
on('logout', 'Session::reset', 1);
on('user_changed', 'Session::reloadUser', 1);
