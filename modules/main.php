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
            $oldEmail = $_SESSION['user']['email'];
            if (pass('login', $oldEmail, $_POST['password'])) {
                if ($success = pass('user_update', $_SESSION['user']['id'], $_POST)) {
                    trigger(
                        'email_send',
                        "{$_SESSION['user']['name']} <{$oldEmail}>",
                        'editaccount'
                    );
                    $newEmail = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
                    if ($newEmail !== false  &&  $newEmail !== $oldEmail) {
                        trigger(
                            'email_send',
                            "{$_SESSION['user']['name']} <{$newEmail}>",
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
