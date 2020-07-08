<?php

include_once '../../src/exengine.php';
include_once 'Services/SingletonService.php';
include_once 'Services/MyService.php';

class ServiceExampleConfig extends \ExEngine\BaseConfig {

    protected $defaultControllerFunction = 'ExampleController/index';

    function __construct($launcherFolderPath)
    {
        parent::__construct($launcherFolderPath);
        $this->registerService(SingletonService::class, true);
        $this->registerService(MyService::class);
    }

}

new \ExEngine\CoreX(new ServiceExampleConfig(__DIR__));