<?php

include_once '../../src/exengine.php';
include_once 'FilterExample.php';

class FilterExampleConfig extends \ExEngine\BaseConfig {

    protected $defaultControllerFunction = 'ExampleController/Login';

    function __construct($launcherFolderPath)
    {
        parent::__construct($launcherFolderPath);
        $this->registerFilter(new FilterExample());
    }

}

new \ExEngine\CoreX(new FilterExampleConfig(__DIR__));