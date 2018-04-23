<?php
/**
 * This example uses the RedBean ORM to retrieve and insert data into a database, any orm or manual db connections
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
        $book = R::load($this->bean, $id);
        return array($book);
    }

    function post() {
        $book = R::dispense( $this->bean );
        $book->usuario = $_POST['usuario'];
        $book->clave = $_POST['clave'];
        $book->edad = $_POST['edad'];

        $id = R::store( $book );

        return "$id";
    }

}