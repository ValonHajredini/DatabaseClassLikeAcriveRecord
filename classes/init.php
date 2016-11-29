<?php
spl_autoload_register(function ($class_name) {
    if ($class_name != 'init'){
        include $class_name . '.php';
    }
});
DB::setUsername('root');
DB::setHost('localhost');
DB::setPassword('');
DB::setDatabase('chatapi');
DB::connect();