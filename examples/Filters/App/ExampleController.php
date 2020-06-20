<?php
/**
 * Class ExampleController
 * This class is an example of a controller that uses a session to protect a method.
 * Check the FilterExample.php file to understand the logic of this.
 *
 * Last Update: 19 Jun 2020
 */
class ExampleController {

    function Login() {
        return '
<h1>Hello!</h1>
        <p>Click <a href="ProtectedMethod">here</a> to try to access a protected page.</p>
        <form method="post" action="'.ee()->meta()->link('Authenticate').'"><button type="submit">Click Here To Authenticate</button></form>';
    }

    function Authenticate() {
        @session_start();
        $_SESSION['AUTHENTICATED'] = true;
        return '
<h1>Authentication Successful!</h1>
        <p>You are now authenticated, click <a href="'.ee()->meta()->link('ProtectedMethod').'">here</a> to test if it works.</p>';
    }

    function Logout() {
        @session_destroy();
        header('Location: ' . ee()->meta()->link('Login'));
    }

    function NotAuthenticated($page) {
        return '
        <h1>Not possible</h1>
        <p>You must be authenticated in order to access '. $page .'</p><p>
        Click <a href="'.ee()->meta()->link('Login').'">here</a> to go to login page.</p>
        </p>';
    }

    function ProtectedMethod() {
        return '
<h1>Page With Session Check</h1>
        <p>WOO HOO I Was Hidden. <a href="'.ee()->meta()->link('Logout').'">Logout</a></p>
        <p>Also here are the filter´s data: ' . print_r(ee()->filtersData(), true) . '</p>';
    }

}