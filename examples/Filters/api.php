<?php

include_once '../../src/exengine.php';
include_once 'FilterExample.php';

class MyAuthenticationService {
    function test() {
        return "Data from a user defined service.";
    }
}

class FilterExampleConfig extends \ExEngine\BaseConfig {

    protected $defaultControllerFunction = 'ExampleController/Login';

    function __construct($launcherFolderPath)
    {
        parent::__construct($launcherFolderPath);
        $this->registerService(MyAuthenticationService::class, true);
        $this->registerFilter(FilterExample::class);
    }

}

new \ExEngine\CoreX(new FilterExampleConfig(__DIR__));