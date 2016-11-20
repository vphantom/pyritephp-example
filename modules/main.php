<?php

/**
 * Main
 *
 * Main routes for our application
 *
 * PHP version 5
 *
 * @category  Application
 * @package   PyritePHP
 * @author    Stéphane Lavergne <lis@imars.com>
 * @copyright 2016 Stéphane Lavergne
 * @license   https://opensource.org/licenses/MIT  MIT
 * @link      https://github.com/vphantom/pyrite-php
 */

namespace Main;

/**
 * Enforce mandatory login for accessing a component
 *
 * @return bool Whether user is logged in and can thus proceed
 */
function isGuest()
{
    if (!$_SESSION['USER_OK']) {
        trigger('http_status', 403);
        trigger('render', 'register.html');
        return true;
    };
    return false;
}

on(
    'route/main',
    function () {
        if ($_SESSION['USER_OK']) {
            trigger('route/dashboard');
        } else {
            trigger('render', 'register.html');
        };
    }
);

on(
    'route/login',
    function () {
        if (!pass('form_validate', 'login-form')) {
            trigger('http_status', 440);
            trigger('render', 'register.html');
            return;
        };
        if (!pass('login', $_POST['email'], $_POST['password'])) {
            trigger('http_status', 403);
            trigger('render', 'register.html', array('try_again' => true));
            return;
        };
        trigger('route/main');
    }
);

on(
    'route/dashboard',
    function () {
        if (isGuest()) return;
        // TODO: Your application's authenticated interface starts here.
    }
);
