<?php

/**
 * Index
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

$GLOBALS['PPHP'] = array();

// Load configuration
$PPHP['config'] = parse_ini_file('config.ini', true);

// Load watchdog as early as possible
require_once 'lib/watchdog.php';
if (array_key_exists('mail_errors_to', $PPHP['config']['global'])) {
    $watchdog->notify($PPHP['config']['global']['mail_errors_to']);
};

// Load dependencies provided by Composer
require_once __DIR__ . '/vendor/autoload.php';

// Supplements to sphido/event

/**
 * Trigger an event and get the last return value
 *
 * Parameters are passed as-is to trigger().
 *
 * @return mixed The last return value of the result stack.
 */
function grab()
{
    return array_pop(call_user_func_array('trigger', func_get_args()));
};

/**
 * Trigger an event and test falsehood of the last return value
 *
 * Parameters are passed as-is to trigger()
 *
 * @return bool Whether the last result wasn't false
 */
function pass()
{
    return array_pop(call_user_func_array('trigger', func_get_args())) !== false;
};

// Load core components before modular components
require_once 'lib/pdb.php';
require_once 'lib/router.php';

// Load modular components
foreach (glob(__DIR__ . '/modules/*.php') as $fname) {
    include_once $fname;
};

// Database
$PPHP['db'] = new PDB($PPHP['config']['db']['type'] . ':' . __DIR__ . '/' . $PPHP['config']['db']['sqlite_path']);

// From the command line means install mode
if (php_sapi_name() === 'cli') {
    trigger('install');
    return;
};

// Start up
trigger('startup');
trigger('title', 'PyritePHP');

// Router
Router::run();

// Shut down
trigger('shutdown');
