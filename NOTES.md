# Url Shortener (PHP >= 7.0)

## About the Shortener

* It uses a single flat file as the database
* The database file isn't created until the first URL is shortened
* Each row in the flat file is a JSON array
* It's a composer based project
* PHPUnit tests are located in tests/
* The main class files are documented for use with phpDocumenter

* I'm sure there is some room for improvement

### Future Design

If I were building this as a real project and was going to use a flat file database,
then I would use a nested folder structure and a text file for each shortened address
in order to make it more performant.  That would prevent having to stream/loop through
the single database file.

I would also add a config file and possibly a config class for handling the configuration
of the system instead of passing in the config to the constructor.