<?php
/**
 * ExEngine Filter Class Example
 *
 * Filters are executed in every request before any controller is processed.
 * You can set here a small script to provide functionality before the controller is executed, is mainly intended
 * to provide security to your application, like session handling, tokens, encryption, etc.
 *
 * Filters differ from the Configuration instance in that Filters have access to fully-initialized ExEngine's CoreX,
 * that means that if you have configured a host_init, imported libs in Config or Launcher can be used from here and
 * execute just before the controller's method requested, not in very early time.
 *
 * To stop any execution you can throw a 'ResponseException'.
 *
 * To register a filter add it to your Config's __construct override like this:
 * $this->registerFilter(new FilterExample());
 *
 * Also you can use dependency injection here.
 *
 * Last updated: 7 Jul 2020
 */

class FilterExample extends \ExEngine\Filter
{

    private $auth;

    public function __construct(MyAuthenticationService $myAuthenticationService)
    {
        $this->auth = $myAuthenticationService;
    }

    function requestFilter(\ExEngine\ControllerMethodMeta $methodMeta, array $filtersData)
    {
        // Exclude some methods from this filter.
        switch ($methodMeta->getControllerName()) {
            case 'ExampleController':
                switch ($methodMeta->getMethodName()) {
                    case 'Login':
                    case 'Authenticate':
                    case 'NotAuthenticated':
                        return [];
                }
        }
        // After that, a session is required.
        session_start();
        // Now we will check for the session authentication
        if ($_SESSION['AUTHENTICATED'] != true) {
            // You can stop all, or redirect.
            // throw new \ExEngine\ResponseException('Forbidden', 403);
            // redirect
            ee()->redirect('ExampleController', 'NotAuthenticated', $methodMeta->getMethodName());
        }
        // You can return any kind of data, it will be stored in a key-value variable in the CoreX instance
        // where the key is a string representation of this class from the controller using
        // ee()->filtersData()["FilterExample"].
        // Following filters in the chain can access this thru the $filtersData parameter.
        // Remember that ee()->filtersData() is only available when all filters are executed.
        return [
            "Some" => "Data",
            // Executing a service's function.
            "ServiceData" => $this->auth->test()
        ];
        // returning data is not required. This can be a void function.
    }

    /*
     * You must be extremely careful with Response Filters, the method return can be an object, an array or an
     * string.
     *
     * In this example we are assuming that the return is an string.
     */
    function responseFilter(\ExEngine\ControllerMethodMeta $controllerMeta, $rawControllerResponse)
    {
        if (is_string($rawControllerResponse))
            return '<html aria-help="Set By The Response Filter!">' . $rawControllerResponse . '</html>';
    }

}