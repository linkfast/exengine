<?php
/**
 * ExEngine Filter Class Example
 *
 * Filters are executed in every request before any controller is processed.
 * You can set here a small script to provide functionality before the controller is executed, is mainly intended
 * to provide security to your application, like session handling, tokens, encryption, etc.
 *
 * Filters differ from the Configuration instance in that Filters have access to fully-initialized ExEngine's CoreX,
 * that means that if you have configured a database, imported libs in Config or Launcher can be used from here.
 *
 * To stop any execution you can throw a 'ResponseException'.
 *
 * To register a filter add it to your Config's __construct override like this:
 * $this->registerFilter(new FilterExample());
 *
 * Last updated: 30 Apr 2019
 */

class FilterExample extends \ExEngine\Filter {
    function doFilter(\ExEngine\ControllerMeta $controllerMeta, $previousFilterData = null)
    {
        if (false) {
            throw new \ExEngine\ResponseException('Forbidden Error Message!', 403);
        }
        // You can return any kind of data, it will be passed to the next filter, then can be mutated and eventually
        // accessible from the controller using ee()->getFilterData().
        return [
          "Some" => "Data"
        ];
    }
}