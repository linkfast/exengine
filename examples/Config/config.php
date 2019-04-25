<?php
/**
 * Example configuration class for ExEngine X
 *
 * The definition of this class is completely optional.
 */
class Config extends ExEngine\BaseConfig
{
    // Here you can override the defaults, check docs for all names or the CoreX.php file.
//    protected $usePrettyPrint = true;
//    protected $showStackTrace = false;

    // Please note that you can´t use ExEngine instance in an overridden constructor, this code runs very,
    // very early (even before ExEngine is initiated).
//    public function __construct($launcherFolderPath)
//    {
//        // You can set some very early code here, like session_start() for example.
//        // code...
//        // code...
//        // more code...
//
//        // Then you must call parent constructor
//        parent::__construct($launcherFolderPath);
//    }

    // Set your database connection information here.
    // ExEngine X has defaults for RedBeanPHP (SQLite testing) and POMM (exactly as it explains the Quick Pomm2 Setup
    // Guide), if using alternate configurations (RedBeanPHP:MySQL/PostgreSQL/etc or different POMM settings), you can
    // override here.
//    public function dbInit()
//    {
//        \R::setup( 'pgsql:host=localhost;dbname=mydbuser',
//            'postgres', '' );
//    }
}
