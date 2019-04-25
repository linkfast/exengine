<?php

// You can define your own data classes, the instance of this class will return always a JSON string (encapsulated).
class User extends \ExEngine\DataClass {

    protected $name = "The Unnamed";
    protected $age = 109;

}

class Test {

    // Anonymous data class (PHP 7+). Use http://localhost/index.php/Test/main to call this method.
    function main() : \ExEngine\DataClass {
        return new class extends \ExEngine\DataClass {
            protected $exampleProperty = "Hello World";
        };
    }

    // This function will return an object of the class defined on the top of this file.
    function mydataclass() : User {
        return new User();
    }

    // This function will return an array, all kind of arrays are converted to JSON string.
    function other() : array {
        return [
            "xArray" => "Hello"
        ];
    }

    // This function will return an string (safe-typed), strings are printed raw.
    function stringtest() : string {
        return "<h1>Hello World</h1>";
    }

    // This function will return an string, strings are printed raw. Also this function requires an argument that must be passed
    // in the query string.
    function prueba($arg1) {
        return "Hello $arg1";
    }

    // This function will load a database object using RedBean ORM, Rest controllers are prefered for data management but you
    // can do this if you want or need.
    function pruebaget() {
        // you can access the Config object anytime using this shortcut: ee()->getConfig().
        ee()->getConfig()->dbInit();

        $book = R::load('book', 4);

        return $book; // this returns an string.
    }

    // This function reads a POST argument.
    function post1() {
        $nombre = $_POST['name'];
        return "My name is $nombre";
    }

    // This one also.
    function post2() {
        $apellido = $_POST['last'];
        return "My last name is $apellido";
    }

}