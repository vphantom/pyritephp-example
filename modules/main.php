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

/**
 * Sanitize e-mail address
 *
 * @param string $email String to filter
 *
 * @return string
 */
function cleanEmail($email)
{
    // filter_var()'s FILTER_SANITIZE_EMAIL is way too permissive
    return preg_replace('/[^a-zA-Z0-9@.,_+-]/', '', $email);
}

/**
 * Strip low-ASCII and <>`|\"' from string
 *
 * @param string $string String to filter
 *
 * @return string
 */
function cleanText($string)
{
    return preg_replace(
        '/[<>`|\\"\']/',
        '',
        filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES|FILTER_FLAG_STRIP_LOW)
    );
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

        if (isset($_GET['email']) && isset($_GET['onetime'])) {

            // Account creation validation link
            if (!pass('login', $_GET['email'], null, $_GET['onetime'])) {
                trigger('http_status', 403);
                trigger('render', 'anonymous.html', array('onetime' => true));
                return;
            };
        } else {

            // Normal login
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
            $_POST['name'] = cleanText($_POST['name']);
            $success = pass('user_update', $_SESSION['user']['id'], $_POST);
        };

        // Change e-mail or password
        if (isset($_POST['email'])) {
            $_POST['email'] = cleanEmail($_POST['email']);
            if (!pass('form_validate', 'user_passmail')) {
                trigger('http_status', 440);
                trigger('render', 'anonymous.html');
                return;
            };
            $saved = true;
            $oldEmail = cleanEmail($_SESSION['user']['email']);
            if (pass('login', $oldEmail, $_POST['password'])) {
                if ($success = pass('user_update', $_SESSION['user']['id'], $_POST)) {
                    $name = cleanName($_SESSION['user']['name']);
                    trigger(
                        'email_send',
                        "{$name} <{$oldEmail}>",
                        'editaccount'
                    );
                    $newEmail = $_POST['email'];
                    if ($newEmail !== false  &&  $newEmail !== $oldEmail) {
                        trigger(
                            'email_send',
                            "{$name} <{$newEmail}>",
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

on(
    'route/register',
    function () {
        $created = false;
        $success = false;
        if (isset($_POST['email'])) {
            if (!pass('form_validate', 'registration')) {
                trigger('http_status', 440);
                trigger('render', 'anonymous.html');
                return;
            };
            $created = true;
            $_POST['email'] = cleanEmail($_POST['email']);
            $_POST['name'] = cleanText($_POST['name']);
            $_POST['onetime'] = true;
            if (($onetime = grab('user_create', $_POST)) !== false) {
                $success = true;
                $link = http_build_query(array( 'email' => $_POST['email'], 'onetime' => $onetime));
                trigger(
                    'email_send',
                    "{$_POST['name']} <{$_POST['email']}>",
                    'newaccount',
                    array(
                        'validation_link' => $link
                    )
                );
            };
        };
        trigger(
            'render',
            'register.html',
            array(
                'created' => $created,
                'success' => $success
            )
        );
    }
);
