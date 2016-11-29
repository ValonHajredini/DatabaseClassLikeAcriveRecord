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


                case 'fetchAllJoins':
                    $row = $query->fetchAllJoins(PDO::FETCH_OBJ);
                    return $row;
                    break;


                case 'fetchByColumnName':
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
    //  ----------------------------------------------------------
    //    Public functiones
    private static function fetch($sqlquery, array $bindparams = []){
        self::$_count_fetch ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'fetch');
    }
    private static function deleteRecord($sqlquery, array $bindparams = []){
        self::$_count_fetch ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'delete');
    }
    private static function updateRecord($sqlquery, array $bindparams = []){
        self::$_count_fetch ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'update');
    }
    private static function fetchAll($sqlquery, array $bindparams = []){
        self::$_count_fetch_all ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'fetchAll');
    }


    private static function fetchByColumnName($sqlquery, array $bindparams = []){
        self::$_count_fetch_all ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'fetchByColumnName');
    }
    private static function exec($sqlquery, array $bindparams =[]){
        self::$_count_exec ++;
        self::$_count_sql_query ++;
        return self::_executeQuery($sqlquery, $bindparams, 'execute');
    }
    //    Custom functiones
    public static function all(){
        if (isset(User::$table)){
            $table = User::$table;
        }else {
            $table = strtolower(get_called_class()).'s';
        }
        return self::fetchAll("SELECT * FROM $table  ");
    }
    public static function find($id){
        if (isset(User::$table)){
            $table = User::$table;
        }else {
            $table = strtolower(get_called_class()).'s';
        }
        return self::fetch("SELECT * FROM $table WHERE id = :id", [['id',$id, 'int']]);
    }
    public static function findByColumnName(array $column = []){
//        The $params must be like:
//        column[] = ['colName_1' => 'colValue_1', 'colName_2' => 'colValue_2', ..., 'colName_n' => 'colValue_n']
        if (isset(User::$table)){
            $table = User::$table;
        }else {
            $table = strtolower(get_called_class()).'s';
        }
        $arrayKeys = $paramsKeys = array_keys($column);
        $paramArray = [];
        foreach ($arrayKeys as $key){
            $paramArray[] ="$key = :$key";
        }
        $paramsInQuery = implode(' and ', $paramArray);
//      Returns number of delleted rows
//      SELECT * from users where username = :username and password = :password
        return self::fetchByColumnName("SELECT * FROM $table WHERE $paramsInQuery ", self::prepareArray($column));
    }
    public static function selectJoin(array $joinTable = []){
        foreach ($joinTable as $key => $table){
            $joinTableName = $key;
            $joinTableForeignKey = $table;
        }
        if (isset(User::$table)){
            $table = User::$table;
        }else {
            $table = strtolower(get_called_class()).'s';
        }
        return self::fetchAll("SELECT * from $table JOIN $joinTableName ON $table.id = $joinTableName.$joinTableForeignKey");
    }
    //    Create methode
    public static function create(array $params =[]){
        //  The $params must be like:
        //  $params[] = ['colName_1' => 'colValue_1', 'colName_2' => 'colValue_2', ..., 'colName_n' => 'colValue_n']
        if (isset(User::$table)){
            $table = User::$table;
        }else {
            $table = strtolower(get_called_class()).'s';
        }
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
    //  Delete methodes
    public static function delete($id){
        if (isset(User::$table)){
            $table = User::$table;
        }else {
            $table = strtolower(get_called_class()).'s';
        }
        return self::deleteRecord("DELETE FROM $table where id = :id", [['id',$id, 'int']]);
    }
    public static function deleteByColumnName(array $column = []){
//        The $params must be like:
//        column[] = ['colName_1' => 'colValue_1', 'colName_2' => 'colValue_2', ..., 'colName_n' => 'colValue_n']
        if (isset(User::$table)){
            $table = User::$table;
        }else {
            $table = strtolower(get_called_class()).'s';
        }
        $table = strtolower(get_called_class()).'s';
        $arrayKeys = $paramsKeys = array_keys($column);
        $paramArray = [];
        foreach ($arrayKeys as $key){
            $paramArray[] ="$key = :$key";
        }
        $paramsInQuery = implode(' and ', $paramArray);
//      Returns number of delleted rows
        return self::deleteRecord("DELETE FROM $table where $paramsInQuery ", self::prepareArray($column));
    }
    //  Update Methode
    public static function update($id, array $parameters = []){
        //        The $params must be like:
//        $parameters[] = ['colName_1' => 'colValue_1', 'colName_2' => 'colValue_2', ..., 'colName_n' => 'colValue_n']
        if (isset(User::$table)){
            $table = User::$table;
        }else {
            $table = strtolower(get_called_class()).'s';
        }
        $arrayKeys = $paramsKeys = array_keys($parameters);
        $paramArray = [];
        foreach ($arrayKeys as $key){
            $paramArray[] ="$key = :$key";
        }
        $paramsInQuery = implode(', ', $paramArray);
//        Returns number of affected rows
        return self::updateRecord("UPDATE $table SET $paramsInQuery  where id = $id ", self::prepareArray($parameters));
    }
    //  ------------------------------------------------------------------------
    //  Return JSON
    public static function objToJSON($obj){
        return json_encode($obj);
    }
    public static function getStatistics(){
        return [
            'fetch' => self::$_count_fetch,
            'fetchAll' => self::$_count_fetch_all,
            'exec' => self::$_count_exec,
            'sql_query' => self::$_count_sql_query
        ];
    }

//    DEVELOPMENT SECTION


}