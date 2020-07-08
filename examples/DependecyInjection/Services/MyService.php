<?php

class MyService {

    private $singletonService;

    public function __construct(SingletonService $singletonService)
    {
        // This should be done every time a controller, service or filter requires this service.
        $this->singletonService = $singletonService;
        $this->singletonService->add("Data from MyService!");
    }

    function testFunction() {
        return print_r($this->singletonService->getData(), true);
    }

}