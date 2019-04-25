<?php
/**
 * ExEngine Instance Launcher File Example
 *
 * Add here all the library dependencies of your project.
 */

 // Include ExEngine itself.
include_once 'CoreX.php';

// Include the file containing the definition of your config, this is optional. Also you can define configuration here.
include_once 'config.php';

// Include whatever library you want, for example, I'm including RedBean ORM in postgres flavor.
include_once 'redbean/rb-postgres.php';

/**
 * Instantiate configuration (if exists) and start the application.
 * 
 * Alternate instantiation without config class: new \ExEngine\CoreX();
 */
new \ExEngine\CoreX(new Config(__DIR__));
