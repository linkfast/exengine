<?php
/**
 * ExEngine Dependency Injection Example
 *
 * This is a service that we will register as a singleton service,
 * its behavior will be like a static function every time we need it in the
 * request lifecycle.
 *
 * Last updated: 7 Jul 2020
 */

class SingletonService
{
    private $data = [];
    public function add($element) {
        $this->data[] = $element;
    }
    public function getData() {
        return $this->data;
    }
}