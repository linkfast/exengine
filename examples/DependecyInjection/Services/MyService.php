<?php
/**
 * ExEngine Dependency Injection Example
 *
 * This is a service that we will register as a service,
 * in this mode, every time this service is injected a new instance is made.
 * For example, the injected service in the filter is not the same as the filter injected in the controller.
 *
 * Check singleton services for the other kind of them.
 *
 * Last updated: 4 Nov 2020
 */

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