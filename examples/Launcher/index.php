<?php
/**
 * ExEngine Instance Launcher Example
 *
 * Last updated: 30 Apr 2019
 */

// Install exengine.php file into your libs folder and include it, here, for example, I'm loading the core
// included in this git repo.
include_once '../../src/exengine.php';
// Or, if you are using Composer, load it here:
//require __DIR__ . '/vendor/autoload.php';

// Include the file containing the definition of your config, this is optional. Also you can define configuration here.
//include_once 'config.php';

// Here include whatever you want to be included, for example, I'm including the RedBeam ORM in postgres flavor
//include_once 'redbean/rb-postgres.php';
//

/**
 * Instantiate configuration (if exists) and start the application.
 * 
 * Alternate instantiation without config class: new \ExEngine\CoreX(__DIR__);
 */
new \ExEngine\CoreX(new Config(__DIR__));
