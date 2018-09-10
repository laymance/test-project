<?php
/**
 *
 * PHPUnit tests for the command line calls of the URL Shortener
 * - These will run the PHP CLI script from the command line for testing
 *
 */

use PHPUnit\Framework\TestCase;
use Shortener\Shorten;

class ShortenTest extends TestCase
{
    protected static $ShortenObj;
    protected static $db_file;
    protected static $shorturl_domain;

    public static function setUpBeforeClass()
    {
        /** RUNS TEST USING A TESTING DB FILE */
        self::$shorturl_domain = 'http://sucu.ri';
        self::$db_file = __DIR__ . '/../data/test-shorten.db.json';
        self::$ShortenObj = new Shorten(self::$db_file, self::$shorturl_domain);
    }

    public function testShortenVersionInfo()
    {
        $this->assertStringStartsWith('URL Shortener v', self::$ShortenObj->get_version_info());
    }

    public function testShortenUrlInvalidUrl()
    {
        // URL doesn't have correct form, isn't complete, missing http[s]://
        $this->expectException(InvalidArgumentException::class);
        self::$ShortenObj->shorten_url('www.abc123.com');
    }

    public function testShortenUrl()
    {
        $shortened_url = self::$ShortenObj->shorten_url('http://www.abc123.com');
        $this->assertStringStartsWith(self::$shorturl_domain, $shortened_url);

        return $shortened_url;
    }

    /**
     * @depends testShortenUrl
     */
    public function testShortUrlInfo($shortened_url)
    {
        $this->assertArrayHasKey('longurl', self::$ShortenObj->get_shorturl_info($shortened_url));
    }

    public function testShortUrlInfoInvalidUrl()
    {
        $this->assertFalse(self::$ShortenObj->get_shorturl_info(self::$shorturl_domain . '/invalid-url-not-in-db'));
    }

    public function testGetFieldName()
    {
        $this->assertSame('Long URL', self::$ShortenObj->get_field_name('longurl'));
        $this->assertSame('Short URL', self::$ShortenObj->get_field_name('shorturl'));
    }

    public function testGetInvalidFieldName()
    {
        $this->assertSame('x_invalid_field_key', self::$ShortenObj->get_field_name('x_invalid_field_key'));
    }

    static function tearDownAfterClass()
    {
        unlink(self::$db_file);
    }
}
