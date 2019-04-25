<?php
/**
 * This example uses the RedBeanPHP ORM to retrieve and insert data into a database, any ORM or manual db connections
 * can be used.
 */

// In order to use Rest methods, your controller class must extend the ExEngine\Rest class (defined in CoreX.php).
class RestExample extends ExEngine\Rest {

    var $bean = 'usuarios';

    function __construct()
    {
        ee()->getConfig()->dbInit();
    }

    // Now define each Rest method as a function name, all in lowercase, get and post examples:
    function get($id) {
        $book = \RedBeanPHP\R::load($this->bean, $id);
        return array($book);
    }

    function post() {
        $book = \RedBeanPHP\R::dispense( $this->bean );
        $book->usuario = $_POST['usuario'];
        $book->clave = $_POST['clave'];
        $book->edad = $_POST['edad'];

        $id = \RedBeanPHP\R::store( $book );

        return "$id";
    }

}