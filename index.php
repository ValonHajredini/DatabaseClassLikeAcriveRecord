<?php
require 'classes/DB.php';
require 'classes/User.php';
DB::setUsername('root');
DB::setHost('localhost');
DB::setPassword('');
DB::setDatabase('chatapi');
//DB::setdbCredentials('localhost', 'chatapi', 'root', '');
DB::connect();
echo "Last inserted id: ". User::create(['username' => 'username', 'password' => '112233']);
echo '<hr>';
print_r(User::find(197));
echo " <hr>";
//echo User::deleteByColumn(['password' => '112233'])." rows deleted";
echo '<hr>';
print_r(User::all());
echo '<hr>';
echo User::update(201, ['username' => 'updated']) ." Rows updated";
echo " <hr>";
echo User::delete(201) ." row deleted";
echo " <hr>";
echo "JSON single record";
echo "<pre>";
echo User::json_return(User::find(226));
echo "</pre>";
echo " <hr>";
echo "JSON Multi record";
echo "<pre>";
echo User::json_return(User::all());
echo "</pre>";
echo " <hr>";
print_r(User::findByColumn(['password' => '112233']));
echo " <hr>";
echo User::json_return(User::findByColumn(['password' => '111']));
echo " <hr>";
