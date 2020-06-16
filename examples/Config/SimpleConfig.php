<?php
/**
 * Example configuration class for ExEngine Microframework
 *
 * The definition of this class is completely optional, please check the documentation provided in the
 * wiki for more information of configuring ExEngine:
 * https://gitlab.com/linkfast-oss/exengine/wikis/Configuration
 *
 * Last updated: 15 Jun 2020
 */
class Config extends ExEngine\BaseConfig
{
    // Here you can override the defaults, check docs for all names.
//    protected $usePrettyPrint = true;
//    protected $showStackTrace = false;
//    protected $defaultStaticAppStart = 'index.html';

    // Please note that you can´t use ExEngine instance in an overridden constructor, this code runs very,
    // very early (even before ExEngine is initiated).
//    public function __construct($launcherFolderPath)
//    {
//        // You can set some very early code here.
//        // code...
//        // code...
//        // more code...
//
//        // Then you MUST call parent constructor
//        parent::__construct($launcherFolderPath);
//    }

    // Set your host_init connection information here.
    // ExEngine has defaults for RedBeanPHP (SQLite testing) and POMM (exactly as it explains the Quick Pomm2 Setup
    // Guide), if using alternate configurations (RedBeanPHP:MySQL/PostgreSQL/etc or custom POMM settings), you can
    // override here.
//    public function dbInit()
//    {
//        \R::setup( 'pgsql:host=localhost;dbname=mydbuser',
//            'postgres', '' );
//    }
}
