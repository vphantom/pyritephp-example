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
    if (!$_SESSION['identified']) {
        trigger('http_status', 403);
        trigger('render', 'anonymous.html');
        return true;
    };
    return false;
}

on(
    'route/main',
    function () {
        if (!$_SESSION['identified']) {
            trigger('render', 'anonymous.html');
            return;
        };
        if (isGuest()) return;
        // TODO: Your application's authenticated interface starts here.
        echo "<p>Dashboard will go here</p>\n";
    }
);

on(
    'route/login',
    function () {
        $req = grab('request');
        if (!pass('form_validate', 'login-form')) {
            trigger('http_status', 440);
            trigger('render', 'anonymous.html');
            return;
        };
        if (!pass('login', $_POST['email'], $_POST['password'])) {
            trigger('http_status', 403);
            trigger('render', 'anonymous.html', array('try_again' => true));
            return;
        };
        trigger('http_redirect', $req['base'] . '/');
    }
);

on(
    'route/logout',
    function () {
        $req = grab('request');
        trigger('logout');
        trigger('http_redirect', $req['base'] . '/');
    }
);

on(
    'route/user+prefs',
    function () {
        if (isGuest()) return;
        $saved = false;
        $success = false;

        // Settings & Information
        if (isset($_POST['name'])) {
            if (!pass('form_validate', 'user_prefs')) {
                trigger('http_status', 440);
                trigger('render', 'anonymous.html');
                return;
            };
            $saved = true;
            $success = pass('user_update', $_SESSION['user']['id'], $_POST);
        };

        // Change e-mail or password
        if (isset($_POST['email'])) {
            if (!pass('form_validate', 'user_passmail')) {
                trigger('http_status', 440);
                trigger('render', 'anonymous.html');
                return;
            };
            $saved = true;
            if (pass('login', $_SESSION['user']['email'], $_POST['password'])) {
                if ($success = pass('user_update', $_SESSION['user']['id'], $_POST)) {
                    trigger(
                        'email_send',
                        "{$_SESSION['user']['name']} <{$_SESSION['user']['email']}>",
                        'editaccount'
                    );
                    if ($_POST['email'] !== $_SESSION['user']['email']) {
                        // TODO: Security audit: does this get passed to Sendmail via RFC822 headers or via command line?
                        trigger(
                            'email_send',
                            "{$_SESSION['user']['name']} <{$_POST['email']}>",
                            'editaccount'
                        );
                    };
                };
            };
        };

        trigger(
            'render',
            'user_prefs.html',
            array(
                'saved' => $saved,
                'success' => $success
            )
        );
    }
);
