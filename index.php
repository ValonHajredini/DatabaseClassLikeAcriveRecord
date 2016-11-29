<?php require 'classes/init.php';
print_r(User::objToJSON(User::selectJoin(['contacts' => 'users_id'])));