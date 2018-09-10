<?php
/**
 * This file is part of the URL Shortener project.
 *
 * Provides handling of the command line interactions.
 *
 * @author Rick Laymance <rick@laymance.com>
 * @version 1.0
 * @package Shortener\TextUI
 */

namespace Shortener;

/**
 * This class provides the functions for interaction and running via the command line
 */
class TextUI
{
    /**
     * Var for the Shorten object passed in via the constructor
     * 
     * @var string
     */
    private $shortener = null;

    /**
     * Var for the short command argument options
     * 
     * @var string
     */
    protected $short_options = '';

    /**
     * Var for the long command argument options
     * 
     * @var array
     */
    protected $long_options = [
        'shorten:',
        'search:',
        'info:'
    ];

    /**
     * Constructor
     * 
     * @param Shorten $shorten The Shorten object
     * @return void
     */
    public function __construct(Shorten $shorten)
    {
        $this->shortener = $shorten;
    }

    /**
     * The main command line runner and processor
     * 
     * @return void
     */
    public function run()
    {
        // See what command line switches were used
        $cmd_options = getopt($this->short_options, $this->long_options);

        if (count($cmd_options) > 1 or count($cmd_options) == 0) {
            // Show the usage screen if they supply more than one switch, or
            // if they don't supply any switches
            $this->show_usage();
        } else {
            // Process the command line switches given
            foreach ($cmd_options as $cmd_switch => $switch_value) {
                switch ($cmd_switch) {
                    case 'shorten':
                        $this->shorten($switch_value);
                        break;

                    case 'search':
                        $this->search($switch_value);
                        break;

                    case 'info':
                        $this->info($switch_value);
                        break;

                    default:
                        $this->show_usage();
                        break;
                }
            }
        }
    }

    /**
     * Shorten function, echoes output to screen
     * 
     * @access private
     * @param string $url The url to shorten
     * @throws Exception
     * @return void
     */
    private function shorten($url){
        try {
            echo $this->shortener->shorten_url($url);
        } catch (\Exception $e){
            echo 'ERROR: ' . $e->getMessage();
        }

        echo PHP_EOL;
    }

    /**
     * URL search function, echoes output to screen
     * 
     * @access private
     * @param string $url The URL to search the database for
     * @return void
     */
    private function search($url){
        $info = $this->shortener->get_shorturl_info($url);

        if ( ! $info ){
            echo 'URL not found';
        } else {
            echo $info['longurl'];
        }

        echo PHP_EOL;
    }

    /**
     * Information retriever for a URL, echoes output to screen
     * 
     * @access private
     * @param string $url Url to retrieve information for
     * @return void
     */
    private function info($url){
        $info = $this->shortener->get_shorturl_info($url);

        if ( ! $info ){
            echo 'URL not found' . PHP_EOL;
        } else {
            echo PHP_EOL;

            foreach($info as $key=>$val){
                echo $this->shortener->get_field_name($key) . ': ' . $val . PHP_EOL;
            }

            echo PHP_EOL;
        }
    }

    /**
     * Command line usage screen, echoes output to screen
     * 
     * @access private
     * @return void
     */
    private function show_usage()
    {
        echo $this->shortener->get_version_info() . PHP_EOL;
        print <<< EOT

Usage: php cli.php [options]

Options:

  --shorten <url>         Generates a shortened url for the url provided
  --search <short-url>    Returns long url associated with a shortened url
  --info <short-url>      Return information associated with a shortened url


EOT;
    }
}
