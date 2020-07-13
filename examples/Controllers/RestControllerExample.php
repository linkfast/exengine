<?php
/**
 * This example uses the RedBeanPHP ORM to retrieve and insert data into a host_init.
 *
 * RedBeanPHP use is not mandatory, you can use any ORM or manual connection.
 *
 * For more information about host_init initializing in ExEngine check the following docs:
 * https://gitlab.com/linkfast-oss/exengine/wikis/config#database
 *
 * Last updated: 30 Apr 2019
 */

// In order to use REST methods, your controller class must extend the ExEngine\Rest class (defined in exengine.php).
class RestControllerExample extends ExEngine\RestController {

    var $bean = 'usuarios';

    function __construct()
    {
        // Initialize with the default initialize method, you can change this creating a custom configuration.
        // See the link in the top of this page.
        ee()->getConfig()->dbInit();
    }

    // Now define each REST method as a function name, all in lowercase, `get` and `post` for example:
    function get($id) { // Available from: [GET] http://localhost/index.php/RestExample/10
        $book = \RedBeanPHP\R::load($this->bean, $id);
        return array($book);
        // ExEngine will automatically serialize the array and encapsulate it before it answers the
        // request.
    }

    function post() {
        // Available from:
        // [POST] http://localhost/index.php/RestExample (Post Data: usuario=A&clave=B&edad=10)
        $book = \RedBeanPHP\R::dispense( $this->bean );
        $book->usuario = $_POST['usuario'];
        $book->clave = $_POST['clave'];
        $book->edad = $_POST['edad'];

        $id = \RedBeanPHP\R::store( $book );

        return "$id";
        // String responses are the only exception, they are not serialized or parsed or anything, it pass it
        // to the request response body as-is.
    }
}