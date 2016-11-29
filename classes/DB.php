<?php
class DB{
    private static $_USERNAME, $_PASSWORD, $_HOSTNAME, $_DATABASE, $_DBLINK = 'default', $_DBOBJ = null;
    private static $_count_fetch = 0, $_count_fetch_all = 0, $_count_exec = 0, $_count_sql_query = 0;

    public static function setUsername($value){
        self::$_USERNAME = $value;
    }
    public static function setPassword($value){
        self::$_PASSWORD = $value;
    }
    public static function setHost($value){
        self::$_HOSTNAME = $value;
    }
    public static function setDatabase($value){
        self::$_DATABASE = $value;
    }
//    public static function setdbCredentials($host, $dbname, $username, $password){
//        self::$_HOSTNAME = $host;
//        self::$_DATABASE = $dbname;
//        self::$_USERNAME = $username;
//        self::$_PASSWORD = $password;
//    }
    public static function setDbLink($value){
        self::$_DBLINK = $value;
    }
    public static function connect(){
        try{
            if (!isset(self::$_DBOBJ[self::$_DBLINK]) || self::$_DBOBJ[self::$_DBLINK] === null){
                self::$_DBOBJ[self::$_DBLINK] = new PDO("mysql:host=".self::$_HOSTNAME.";dbname=".self::$_DATABASE."", self::$_USERNAME, self::$_PASSWORD);
                self::$_DBOBJ[self::$_DBLINK]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                self::$_DBOBJ[self::$_DBLINK]->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                return self::$_DBOBJ[self::$_DBLINK];
            }

        }catch (PDOException $e){
            echo $e->getMessage();
            exit();
        }
    }
    private static function _bindparam($value){
        switch (strtolower($value)){
            case 'int':
                return PDO::PARAM_INT;
            break;

            case 'str':
                return PDO::PARAM_STR;
            break;

            case 'bool':
                return PDO::PARAM_BOOL;
            break;

            case 'null':
                return PDO::PARAM_NULL;
            break;
        }
    }
    private static function _prepare($sqlQuery){
        if(($query = self::$_DBOBJ[self::$_DBLINK]->prepare($sqlQuery)) != false){
            return $query;
        }else{
            return false;
        }
    }
    private static function _executeQuery($sqlquery, array $bindparams =[], $method =''){
        $query = self::_prepare($sqlquery);
        if($query !== false){
            if(count($bindparams) > 0 ){
                foreach($bindparams AS $v){
                    $query->bindParam(":{$v[0]}", $v[1], self::_bindparam($v[2]));
                }
            }
            $query->execute();
            switch ($method){
                case 'fetch':
                    $row = $query->fetch(PDO::FETCH_OBJ);
                    return $row;
                    break;

                case 'fetchAll':
                    $row = $query->fetchAll(PDO::FETCH_OBJ);
                    return $row;
                    break;
                case 'fetchByCilumnName':
                    $row = $query->fetchAll(PDO::FETCH_OBJ);
                    return $row;
                    break;

                case 'execute':
                    return $row = self::$_DBOBJ[self::$_DBLINK]->lastInsertId();
                    break;

                case 'delete':
                    return $query->rowCount();
                break;
                case 'update':
                    return $query->rowCount();

                break;

                default:
                    return false;
                break;

            }

        } else {
            return false;
        }
    }
    public static function prepareArray(array $inputArray = []){
        $preperingArray = [];
        foreach($inputArray as $key => $values){
            $field = [];
            $field[] = $key;
            $field[] = $values;
            if (is_int($values)){
                $field[] = 'int';
            }elseif (is_string($values)){
                $field[] = 'str';
            }
            $preperingArray[] = $field;

        }
        return $preperingArray;
    }
//    Public functiones
    public static function fetch($sqlquery, array $bindparams = []){
        self::$_count_fetch ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'fetch');
    }
    public static function deleteRecord($sqlquery, array $bindparams = []){
        self::$_count_fetch ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'delete');
    }
    public static function update($sqlquery, array $bindparams = []){
        self::$_count_fetch ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'update');
    }
    public static function fetchAll($sqlquery, array $bindparams = []){
        self::$_count_fetch_all ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'fetchAll');
    }
    public static function fetchByCilumnName($sqlquery, array $bindparams = []){
        self::$_count_fetch_all ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'fetchByCilumnName');
    }
    public static function exec($sqlquery, array $bindparams =[]){
        self::$_count_exec ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'execute');
    }
    public static function getStatistics(){
        return [
            'fetch' => self::$_count_fetch,
            'fetchAll' => self::$_count_fetch_all,
            'exec' => self::$_count_exec,
            'sql_query' => self::$_count_sql_query
        ];
    }
//    Custom functiones
    public static function allRecords($table){
        return self::fetchAll("SELECT * FROM $table  ");
    }
    public static function findById($table,$id){
        return self::fetch("SELECT * FROM $table WHERE id = :id", [['id',$id, 'int']]);
    }
//    Create methode
    public static function createRecord($table, array $params =[]){
//        The $params must be like:
//        $params[] = ['colName_1' => 'colValue_1', 'colName_2' => 'colValue_2', ..., 'colName_n' => 'colValue_n']
        $array = self::prepareArray($params);
        $paramsKeys = array_keys($params);
        $queryParams = implode(', ',$paramsKeys);
        $queryBindParams = [];
        foreach($paramsKeys as $key){
            $queryBindParams[] = ":".$key;
        }
        $queryBindParams = implode(', ',$queryBindParams);
//        Returns last inserted id
        return self::exec("INSERT INTO $table ($queryParams) VALUES($queryBindParams) ", $array);

    }
//  -------------------------------------------------------------
//  Delete methodes
    public static function deleteById($table,$id){

        return self::deleteRecord("DELETE FROM $table where id = :id", [['id',$id, 'int']]);
    }
    public static function deleteByColumnName($table,array $column = []){
//        The $params must be like:
//        column[] = ['colName_1' => 'colValue_1', 'colName_2' => 'colValue_2', ..., 'colName_n' => 'colValue_n']
        $arrayKeys = $paramsKeys = array_keys($column);
        $paramArray = [];
        foreach ($arrayKeys as $key){
            $paramArray[] ="$key = :$key";
        }
        $paramsInQuery = implode(' and ', $paramArray);
//      Returns number of delleted rows
        return self::deleteRecord("DELETE FROM $table where $paramsInQuery ", self::prepareArray($column));
    }
    public static function findByColumnName($table,array $column = []){
//        The $params must be like:
//        column[] = ['colName_1' => 'colValue_1', 'colName_2' => 'colValue_2', ..., 'colName_n' => 'colValue_n']
        $arrayKeys = $paramsKeys = array_keys($column);
        $paramArray = [];
        foreach ($arrayKeys as $key){
            $paramArray[] ="$key = :$key";
        }
        $paramsInQuery = implode(' and ', $paramArray);
//      Returns number of delleted rows
//      SELECT * from users where username = :username and password = :password
        return self::fetchByCilumnName("SELECT * FROM $table WHERE $paramsInQuery ", self::prepareArray($column));
    }
//  -------------------------------------------------------------------------

//  Update Methode
    public static function updateById($table, $id, array $parameters = []){
        //        The $params must be like:
//        $parameters[] = ['colName_1' => 'colValue_1', 'colName_2' => 'colValue_2', ..., 'colName_n' => 'colValue_n']
        $arrayKeys = $paramsKeys = array_keys($parameters);
        $paramArray = [];
        foreach ($arrayKeys as $key){
            $paramArray[] ="$key = :$key";
        }
        $paramsInQuery = implode(', ', $paramArray);
//        Returns number of affected rows
        return self::update("UPDATE $table SET $paramsInQuery  where id = $id ", self::prepareArray($parameters));
    }
//  ------------------------------------------------------------------------
}