# ExEngine Microframework

## Quick start

1. Install using `composer` or 
[download a release](https://gitlab.com/linkfast-oss/exengine/releases).

    ```
    composer install linkfast-oss/exengine
    ````

2. Create an instance launcher

    Create an `index.php` file in the root of the folder exposed to the HTTP server, and include there `vendor.php` if using `composer` or the `CoreX.php` file.

    ```php
    <?php
        include_once 'exengine.php';
        // or
        include_once 'vendor/autoload.php';

        new \ExEngine\CoreX(__DIR__);
    ```
    
    You can create a custom `config` class with settings for database connection, etc.

3. Create a folder called `._` relative to `index.php`. Inside of this new folder, create a file called `Test.php` with the following contents:

    ```php
    <?php
       # File ´._/Test.php´
        class Test {
            function helloworld() {
                return "<h1>Hello World</h1>";
            }
        }
    ```

4. Open your browser and navigate to: `http://myserverhost/index.php/Test/helloworld`

5. Take a look to the `Examples` folder, profit.

## Creating a REST controller

ExEngine allows easy REST controllers creation, you just have to extend a parent class and write the HTTP methods responses.

```php
<?php
    class RestExample extends \ExEngine\Rest {
        function get($id) {
            return "Hello $id";
        }

        function post() {
            $data = $_POST['data'];
            return "Data: $data";
        }
        // function put()
        // function delete()
        // function options()
        // etc.
    }
```

Test your Rest controller using standard HTTP methods: 

GET `http://myserverhost/index.php/RestExample/1`

POST `http://myserverhost/index.php/RestExample/`

OPTIONS `http://myserverhost/index.php/RestExample/`

## Writing a JSON api

ExEngine converts anything except `strings` functions results to JSON, encapsulating in an standard response.

Example successful response:
```json
{
    "took":0,
    "code":200,
    "data":{
        "response": "from",
        "the": "function"
    },
    "error":false
}
```

To get the previous response you should write the following function:

```php
    // ...
    function test() {
        return [
            "response" => "from",
            "the" => "function"
        ]
    }
```

## Documentation

Check out some [examples here](https://gitlab.com/linkfast-oss/exengine/tree/master/examples) and
 a detailed documentation in our [wiki](https://gitlab.com/linkfast-oss/exengine/wikis/home).

## Issues

Please leave an issue to our GitLab.com project using this [link](https://gitlab.com/linkfast-oss/exengine/issues/new).

## License

```
The MIT License (MIT)

Copyright (c) 2018, 2019 LINKFAST S.A. (http://linkfast.io)
Copyright (c) 2018, 2019 Giancarlo A. Chiappe Aguilar (gchiappe@linkfast.io)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
OR OTHER DEALINGS IN THE SOFTWARE.
```