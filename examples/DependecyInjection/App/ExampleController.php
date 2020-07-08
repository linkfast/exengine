<?php
/**
 * Class ExampleController
 * This class is an example of a controller that uses a service and return data
 * from it.
 *
 * Last Update: 19 Jun 2020
 */
class ExampleController {

    private $singletonService;
    private $myService;


    public function __construct(SingletonService $singletonService, MyService $myService)
    {
        $this->singletonService = $singletonService;
        $this->singletonService->add("Data from the Controller itself.");
        $this->myService = $myService;
    }

    function index()
    {
        return $this->myService->testFunction();
    }
}