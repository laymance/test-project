<?php
/**
 * Simple URL Shortener
 * 
 * A simple URL shortener using a flat-file database system
 * and running on PHP > 7.0
 * 
 * @author      Rick Laymance <rick@laymance.com>
 * @version     1.0
 */

// Use composer's autoloader
require __DIR__ . '/../vendor/autoload.php';

// Instantiate our shortener system so that we can pass it to the command 
// line handler - pass in our domain name for the shortener - on a "full"
// system we would have a configuration file/class and handling
$shortener = new Shortener\Shorten(__DIR__ . '/../data/shortener.db.json', 'http://sucu.ri');

// Call our command line handler, pass in the shortener dependency
$cmd = new Shortener\TextUI($shortener);
$cmd->run();