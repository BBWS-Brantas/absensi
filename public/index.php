<?php

// Check PHP version.
if (PHP_VERSION_ID < 80100) {
    exit(sprintf('Your PHP version must be 8.1.0 or higher to run CodeIgniter. Current version: %s', PHP_VERSION));
}

/**
 * @var bool $useKint
 */
$useKint = true;

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
chdir(__DIR__);

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with the framework's own class autoloader.
 */

// Load our paths config file
// This is the line that might need to be changed, depending on your folder structure.
require __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();

require $paths->systemDirectory . '/Boot.php';

exit(CodeIgniter\Boot::bootWeb($paths));