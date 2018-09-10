<?php
/**
 *
 * PHPUnit tests for the command line UI class. Please note that most all
 * functions in this UI class return text with PHP_EOL at the end (because
 * they are being shown on the command line), so we have to account for this
 * in the tests.
 * 
 * Also, the constructor() and run() are the only public methods, and run()
 * depends on command line arguments passed in (which can't be set at runtime).
 * 
 * All of the classes methods are tested via the CliTest.php file by running
 * them from the command line iteself.
 *
 */

use PHPUnit\Framework\TestCase;
use Shortener\Shorten;
use Shortener\TextUI;

class cliUITest extends TestCase
{
    protected static $db_file;
    protected static $shorturl_domain;

    public static function setUpBeforeClass()
    {
        /** RUNS TEST USING A TESTING DB FILE */
        self::$shorturl_domain = 'http://sucu.ri';
        self::$db_file = __DIR__ . '/../data/test-shorten.db.json';
    }

    public function testInstantiateWithoutShortenObject()
    {
        $this->expectException('ArgumentCountError');
        $cliui = new TextUI();
    }

    public function testRunWithoutCliArguments()
    {
        $this->expectOutputRegex('/Usage\:\s+php\s+cli\.php/');
        $ShortenObj = new Shorten(self::$db_file, self::$shorturl_domain);
        $cliUIObj = new TextUI($ShortenObj);
        $cliUIObj->run();
    }
}
