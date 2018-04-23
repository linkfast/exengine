<?php
/**
 * Example configuration class for ExEngine X
 * 
 * The definition of this class is completely optional.
 */
class Config extends ExEngine\BaseConfig {

    // Here you can override the defaults, check docs for all names or the CoreX.php file.
    protected $usePrettyPrint = true;
    protected $showStackTrace = false;

    public function __construct()
    {
        // You can set some very early code here, like session_start() and session settings for example.
    }

    public function dbInit() {
        // Example of setting up RedBean ORM.
        \R::setup( 'pgsql:host=localhost;dbname=mydbuser',
            'postgres', '' );
    }
}