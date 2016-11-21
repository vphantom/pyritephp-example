<?php

/**
 * User
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
 * User class
 *
 * @category  Library
 * @package   PyritePHP
 * @author    Stéphane Lavergne <lis@imars.com>
 * @copyright 2016 Stéphane Lavergne
 * @license   https://opensource.org/licenses/MIT  MIT
 * @link      https://github.com/vphantom/pyrite-php
 */
class User
{

    /**
     * Create database tables if necessary
     *
     * @return null
     */
    public static function install()
    {
        global $db;
        echo "    Installing users...\n";
        $db->exec(
            "
            CREATE TABLE IF NOT EXISTS 'users' (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                email        VARCHAR(255),
                passwordHash VARCHAR(255),
                name         VARCHAR(255)
            )
            "
        );
        if (!$db->selectAtom("SELECT id FROM users WHERE id='1'")) {
            echo "Creating admin user...\n";
            $email = readline("E-mail address: ");
            $pass1 = true;
            $pass2 = false;
            while ($pass1 !== $pass2) {
                if ($pass1 !== true) {
                    echo "  * Password confirmation mis-match.\n";
                };
                $pass1 = readline("Password: ");
                $pass2 = readline("Password again: ");
            };
            $db->exec(
                "
                INSERT INTO users VALUES
                (1, ?, ?, 'Administrator')
                ",
                array(
                    $email,
                    password_hash($pass1, PASSWORD_DEFAULT)
                )
            );
        };
        echo "    done!\n";
    }

    /**
     * Load and authenticate a user
     *
     * @param string $email    E-mail address
     * @param string $password Plain text password (supplied via web form)
     *
     * @return array|bool Associative array for the user or false if not authorized
     */
    public static function login($email, $password)
    {
        global $db;

        if ($user = $db->selectSingleArray("SELECT * FROM users WHERE email=?", array($email))) {
            if (password_verify($password, $user['passwordHash'])) {
                return $user;
            };
        };

        return false;
    }
}

on('install', 'User::install');
on('authenticate', 'User::login');
