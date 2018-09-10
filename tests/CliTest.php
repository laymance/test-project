<?php
/**
 *
 * PHPUnit tests for the command line calls of the URL Shortener
 * - These will run the PHP CLI script from the command line for testing
 *
 */

use PHPUnit\Framework\TestCase;

class CliTest extends TestCase
{
    protected static $filepath = '';

    public static function setUpBeforeClass()
    {
        self::$filepath = realpath(dirname(__FILE__) . '/../src');
    }

    public function testCliInvalidShortenUrl()
    {
        $this->assertSame('ERROR: Invalid URL given' . PHP_EOL, shell_exec('php ' . self::$filepath . '/cli.php --shorten www.testurl.com'));
    }

    public function testCliEmptyShortenUrl()
    {
        $this->assertRegExp('/Usage\:\s+php\s+cli\.php/', shell_exec('php ' . self::$filepath . '/cli.php --shorten'));
    }

    public function testCliShorten()
    {
        // We use the output of this test in other tests below
        $output = shell_exec('php ' . self::$filepath . '/cli.php --shorten http://www.testurl.com');
        $this->assertRegExp('/^http:\/\/sucu\.ri\/.*/m', $output);

        return $output;
    }

    /**
     * @depends testCliShorten
     */
    public function testCliSearch($shortened_url = '')
    {
        $this->assertEquals('http://www.testurl.com' . PHP_EOL, shell_exec('php ' . self::$filepath . '/cli.php --search ' . $shortened_url));
    }

    public function testCliInvalidSearchUrl()
    {
        $this->assertEquals('URL not found' . PHP_EOL, shell_exec('php ' . self::$filepath . '/cli.php --search abc123.com'));
    }

    public function testCliEmptySearchUrl()
    {
        $this->assertRegExp('/Usage\:\s+php\s+cli\.php/', shell_exec('php ' . self::$filepath . '/cli.php --search'));
    }

    /**
     * @depends testCliShorten
     */
    public function testCliInfo($shortened_url = '')
    {
        $this->assertRegExp('/(Created:\s.*)\s(Short\sURL:\s.*)\s(Long\sURL:\shttp:\/\/www\.testurl\.com)/m', shell_exec('php ' . self::$filepath . '/cli.php --info ' . $shortened_url));
    }

    public function testCliEmptyInfoUrl()
    {
        $this->assertRegExp('/Usage\:\s+php\s+cli\.php/', shell_exec('php ' . self::$filepath . '/cli.php --info'));
    }

    public function testCliInvalidInfoUrl()
    {
        $this->assertEquals('URL not found' . PHP_EOL, shell_exec('php ' . self::$filepath . '/cli.php --info abc123.com'));
    }

    public function testCliInvalidSwitches()
    {
        $this->assertRegExp('/Usage\:\s+php\s+cli\.php/', shell_exec('php ' . self::$filepath . '/cli.php --notvalid yes'));
    }

    public function testCliUsageOptions()
    {
        $this->assertRegExp('/Usage\:\s+php\s+cli\.php/', shell_exec('php ' . self::$filepath . '/cli.php'));
    }
}
