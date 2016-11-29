<?php
include_once 'DB.php';
/**
 * Created by PhpStorm.
 * User: hajre
 * Date: 11/28/2016
 * Time: 11:54 PM
 */
class User extends DB{
    public static function create( array $params =[]){
        return self::createRecord('users', $params);
    }
    public static function find($id){
        return self::findById('users',$id);
    }
    public static function all(){
        return self::fetchAll("SELECT * FROM users  ");
    }
    public static function update($id, array $params =[]){
       return self::updateById('users', $id,$params);
    }
    public static function delete($id){
        return self::deleteById('users',$id);
    }
    public static function deleteByColumn(array $columns =[]){
        return self::deleteByColumnName('users',$columns);
    }
    public static function json_return($obj){
        return json_encode($obj);
    }
    public static function findByColumn(array $columns = []){
        return self::findByColumnName('users',$columns);
    }
}