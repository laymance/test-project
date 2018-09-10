<?php
/**
 * This file is part of the URL Shortener project.
 *
 * Provides URL shortening functions
 *
 * @author Rick Laymance <rick@laymance.com>
 * @version 1.0
 * @package Shortener\Shorten
 */

namespace Shortener;

/**
 * The main Shorten class, provides url shortening, search and info request actions
 */
class Shorten
{
    /**
     * Version number of the shortener project
     * 
     * @var string
     */
    public $version            = '1.0';

    /**
     * Var for the database filename
     * 
     * @var string
     */
    private $database_filename = 'shortener.db.json';

    /**
     * Default length for the shortener url hash
     * 
     * @var int
     */
    private $min_shorturl_len  = 5;

    /**
     * Var for the domain used in short urls
     * 
     * @var string
     */
    private $short_domain      = 'http://';

    /**
     * Database field names and their descriptions
     * 
     * @var array
     */
    private $db_fields         = ['created_at' => 'Created',
                                  'longurl'    => 'Long URL',
                                  'shorturl'   => 'Short URL'];

    /**
     * Constructor
     * 
     * @param string|null $database_filename Name of database file to use
     * @param string|null $short_domain Domain name to use for shortened urls
     * @return void
     */
    public function __construct($database_filename = '', $short_domain = '')
    {
        // If a config file is passed in, use it instead of the default
        if ($database_filename and trim($database_filename) !== '') {
            $this->database_filename = $database_filename;
        }

        if ($short_domain and trim($short_domain) !== '') {
            $this->short_domain = $short_domain;
        }
    }

    /**
     * Version text retrieval function
     * 
     * @return string Returns string with name and version number
     */
    public function get_version_info()
    {
        return 'URL Shortener v' . $this->version;
    }

    /**
     * Shorten URL function
     * 
     * Takes a supplied URL and creates a shortened URL for it, and writes it
     * to the database for future unshortening.
     * 
     * @param string $url Url to shorten
     * @throws InvalidArgumentException
     * @return string Shortened URL
     */
    public function shorten_url($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL given');
        }

        $url_hash = $this->hash_generator($url, $this->min_shorturl_len);

        // Find a unique shortened url.  With quick and dirty way to accomodate hash collisions.
        // Ideally this wouldn't use a single db file and we would use folder structures and a
        // file per shortened url which would be easier to manage. Increases length of hash on
        // each collision and try again.
        $cnt = 0;
        $hash_length = $this->min_shorturl_len;

        while ($cnt < 10 and $this->get_url_from_db($this->short_domain . '/' . $url_hash, 'short')) {
            $url_hash = $this->hash_generator($url, ($hash_length + $cnt));
            $cnt++;
        }

        if ($cnt == 10 and $this->get_url_from_db($this->short_domain . '/' . $url_hash, 'short')) {
            throw new \Exception('Could not generate a unique short url');
        }

        // Now that we have our short hash, commit it to the db
        $this->write_url_to_db($url, $this->short_domain . '/' . $url_hash);

        return $this->short_domain . '/' . $url_hash;
    }

    /**
     * Information retriever for a short URL
     * 
     * @param string $url Short url to retrieve information for
     * @return array Database information for the shortened url
     */
    public function get_shorturl_info($url)
    {
        $info = $this->get_url_from_db($url, 'short');
        return $info;
    }

    /**
     * Field descriptive name retrieval
     * 
     * Retrieves the descriptive name for a database field, if available,
     * otherwise it returns the string as passed in
     * 
     * @param string $field Database field name
     * @return string Descriptive name for passed in field
     */
    public function get_field_name($field)
    {
        if (isset($this->db_fields[$field])) {
            return $this->db_fields[$field];
        }

        return $field;
    }

    /**
     * Retrieves a url's information from the database
     * 
     * @access protected
     * @param string $url Url to retrieve from the database
     * @param string $type Specifies the type that the $url is, either short or long
     * @return string|bool Returns array of database info, if found, false if not
     */
    protected function get_url_from_db($url, $type = 'short')
    {
        // DB file may not exist, will be created on first shortening operation
        if (!file_exists($this->database_filename)) {
            return false;
        }

        $handle = fopen($this->database_filename, 'r');

        while ($row = fgets($handle)) {
            $short_array = json_decode($row, true);

            if (($type == 'short' and $short_array['shorturl'] == $url) or ($type == 'long' and $short_array['longurl'] == $url)) {
                fclose($handle);
                return $short_array;
            }
        }

        fclose($handle);

        return false;
    }

    /**
     * Writes a new URL to the database
     * 
     * @access protected
     * @param string $url The full/long url
     * @param string $shorturl The shortened url associated with the long url
     * @return void
     */
    protected function write_url_to_db($url, $shorturl)
    {
        $row_data = array(
            'created_at' => date('Y-m-d H:i:s'),
            'shorturl'   => $shorturl,
            'longurl'    => $url,
        );

        $handle = fopen($this->database_filename, 'a');
        fputs($handle, json_encode($row_data) . PHP_EOL);
        fclose($handle);
    }

    /**
     * Hash generation function
     * 
     * A very simple function to return a hash of a URL, of the given length,
     * for use in the shortened url
     * 
     * @access protected
     * @param string $url The url to generate the hash from
     * @param int $length The length of the hash to be returned, defaults to 5
     * @return string The hash generated by using the given url
     */
    protected function hash_generator($url, $length = 5)
    {
        // Simple hash generator, "quick and dirty", could be expanded upon
        if ($length > 160) $length = 160;

        $hash = '';

        while (strlen($hash) < $length) {
            $hash .= md5($url . microtime() . mt_rand(0, 1000));
        }

        return substr($hash, 0, $length);
    }
}