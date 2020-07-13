<?php
/**
 * Class ExampleController
 * This class is an example of a controller that uses a session to protect a method.
 * Check the FilterExample.php file to understand the logic of this.
 *
 * Last Update: 19 Jun 2020
 */
class ExampleController {

    function __default() {
        return "index";
    }

    function index() {
        return
            "<h1>Welcome to ExEngine</h1>" .
            '<p><a href="'.ee()->meta()->link("response").'">Check some API response here.</a></p>';
    }

    function response() {
        return
            [
                "Some" => "Response",
                "From" => "A Server API"
            ];
    }

}