<?php

require_once '../src/components/DB.php';
require_once '../src/components/Utils.php';

/**
* Table Handler
*/
class TableHandler
{
    protected const LINKS_ENUM_STRING = "__linksEnum";

    public $rules = [
        // "All", "Authorized", "Nobody"
        "select" => "Nobody",
        "insert" => "Nobody",
        "update" => "Nobody",
        "delete" => "Nobody"
    ];
    protected $tableName;
    protected $tableMainID;
    protected $tableLinks;
    protected $tableFormat;
    protected $tableStrongLinks = false;

    private $dbConn;
    
    function __construct()
    {
        $db = new DB();
        $this->dbConn = $db->getDBConnection();
    }

    public function selectAll()
    {           
        $sql = "SELECT id FROM $this->tableName";
        $result = $this->dbConn->query($sql);
        $id = $result->fetchAll(PDO::FETCH_NUM);

        $array = [];
        for ($i=0; $i < count($id); $i++) { 
            $tmpid = $id[$i][0];
            $array[] = $this->select($tmpid);
        }       
        
        return $array;
    }

    public function select($id)
    {
        $readyArray;
        if ($this->tableFormat != null) {
            $readyArray = $this->tableFormat;
            foreach ($this->tableFormat as $kFormat => $vFormat) {
                $tmpFormat = $vFormat;
                if (is_array($tmpFormat))
                    $tmpFormat = $tmpFormat[0];

                preg_match('/(\w.*?)\.(\w.*)/', $tmpFormat, $matches);
                if ($matches[1] !== null) {
                    $readyArray[$kFormat] = $this->selectByLinkId($id, $vFormat);
                } elseif (is_array($vFormat)) {
                    $readyArray[$kFormat] = $this->selectByLinkIdMultiple($id, $vFormat);
                } else {
                    $readyArray[$kFormat] = $this->getSQLValue("SELECT $vFormat FROM $this->tableName WHERE $this->tableMainID = $id", PDO::FETCH_NUM);
                    $readyArray[$kFormat] = $this->getFixedSQLValue($readyArray[$kFormat]);
                }
            }
        }
        return $readyArray;
    }

    public function insert($array)
    {
        $indexesString = "";
        $valuesString = "";
        $fixedArray = $this->getFixedArray($this->tableFormat, $array);
        $bigLinkKey;
        $bigLinkValue;

        foreach ($fixedArray as $kFA => $vFA) {
            preg_match('/(\w.*?)\.(\w.*)/', $kFA, $matches);
            $isLinkName = false;
            foreach ($this->tableLinks as $k => $v)
                if ($k === $matches[1])
                    $isLinkName = true;
            if (!is_array($vFA) && !$isLinkName) {
                $indexesString .= "$kFA, ";
                $valuesString .= "'$vFA', ";
            } else {
                $bigLinkKey = $kFA;
                $bigLinkValue = $vFA;
            }
        }

        $indexesString = substr($indexesString, 0, -2);
        $valuesString = substr($valuesString, 0, -2);

        $SQL = "INSERT INTO $this->tableName ($indexesString) VALUES ($valuesString)";
        $this->dbConn->query($SQL);

        $id = $this->getSQLValue("SELECT MAX($this->tableMainID) FROM $this->tableName", PDO::FETCH_NUM)[0][0] + 1;

        $this->updateLinkValue($bigLinkKey, $bigLinkValue, $this->tableName, $id, false);
    }

    public function update($id, $array)
    {
        $settersString = "";
        $fixedArray = $this->getFixedArray($this->tableFormat, $array);
        $bigLinkValue;
        $bigLinkKey;

        foreach ($fixedArray as $kFA => $vFA) {
            preg_match('/(\w.*?)\.(\w.*)/', $kFA, $matches);
            $isLinkName = false;
            foreach ($this->tableLinks as $k => $v)
                if ($k === $matches[1])
                    $isLinkName = true;
            if (!is_array($vFA) && !$isLinkName) {
                $settersString .= "$kFA = '$vFA', ";
            } else {
                $bigLinkKey = $kFA;
                $bigLinkValue = $vFA;
            }
        }
        $settersString = substr($settersString, 0, -2);

        $SQL = "UPDATE $this->tableName SET $settersString WHERE $this->tableMainID = $id";
        // echo "\n$SQL\n";
        $this->dbConn->query($SQL);

        $bigLinkOldValue;
        foreach ($this->getFixedArray($this->tableFormat, $this->select($id)) as $k => $v) {
            if ($k === $bigLinkKey)
                $bigLinkOldValue = $v;
        }
        $this->updateLinkValue($bigLinkKey, $bigLinkValue, $this->tableName, $id, true, $bigLinkOldValue);
    }

    public function delete($id)
    {
        // To Do
        // To Do
        // To Do, To Do, To Do, To Do
        // To Dooooo, DoDoDoDoDo
    }

    private function updateLinkValueSimple($bigLinkKey, $bigLinkValue, $originKey = "", $originValue = "", $isUpdate = true, $bigLinkOldValue = "", $deleted = false)
    {
        preg_match('/(\w.*?)\.(\w.*)/', $bigLinkKey, $matches);
        $links = $this->tableLinks[$matches[1]];
        $linkTable = $this->tableLinks[$matches[1]][TableHandler::LINKS_ENUM_STRING][0];
        $bigLinkKey = str_replace($matches[1], $linkTable, $bigLinkKey);
        $multiLinkName;
        $multiLinkMain;
        $multiLinkArray = [];
        foreach ($links as $k => $v) {
            if (strpos($k, $linkTable) > -1) {
                $multiLinkArray[$k] = $originValue;
            }
        }
        foreach ($links as $k => $v) {
            if ($k === TableHandler::LINKS_ENUM_STRING) continue;
            if (strpos($v, $linkTable) > -1) {
                $SQL = "SELECT $v FROM $linkTable WHERE $bigLinkKey = '$bigLinkValue'";
                // echo "\n$SQL\n";
                $multiLinkArray[$k] = $this->getSQLValue($SQL, PDO::FETCH_NUM)[0][0];
                $multiLinkMain = $k;

                if ($bigLinkOldValue !== ""){
                    $SQL = "SELECT $v FROM $linkTable WHERE $bigLinkKey = '$bigLinkOldValue'";
                    // echo "\n$SQL\n";
                    $bigLinkOldValue = $this->getSQLValue($SQL, PDO::FETCH_NUM)[0][0];
                }
            }
            preg_match('/(\w.*?)\.(\w.*)/', $k, $m);
            $multiLinkName = $m[1];
        }

        if ($isUpdate && !$deleted){
            $SQL = "DELETE FROM $multiLinkName WHERE ";
            foreach ($multiLinkArray as $k => $v) { 
                if ($k !== $multiLinkMain)
                    $SQL .= "$k = '$v'";
            }
            // echo "\n$SQL\n";
            $this->dbConn->query($SQL);
        }
        if ($this->tableStrongLinks){
            // echo "$multiLinkMain";
            $SQL = "DELETE FROM $multiLinkName WHERE ";
            foreach ($multiLinkArray as $k => $v) { 
                $notequal = $k !== $multiLinkMain ? "!" : "";
                $SQL .= "$k $notequal= '$v' AND ";
            }
            $SQL = substr($SQL, 0, -5);
            $this->dbConn->query($SQL);
            // echo "\n$SQL\n";
        }
        
        $SQL = "INSERT INTO $multiLinkName ";
        $indexes = "";
        $values = "";
        foreach ($multiLinkArray as $k => $v) { 
            $indexes .= "$k, ";
            $values .= "'$v', ";
        }
        $indexes = substr($indexes, 0, -2);
        $values = substr($values, 0, -2);
        $SQL .= "($indexes) VALUES ($values)";
        // echo "\n$SQL\n";
        $this->dbConn->query($SQL);
        
    }

    private function updateLinkValue($bigLinkKey, $bigLinkValue, $originKey = "", $originValue = "", $isUpdate = true, $bigLinkOldValue = "")
    {
        if (is_array($bigLinkValue)) {
            $deleted = false; 
            for ($i=0; $i < count($bigLinkValue); $i++) {
                $isWas = false; 
                foreach ($bigLinkValue[$i] as $k => $v) {
                    if (!$isWas) {
                        $this->updateLinkValueSimple($k, $v, $originKey, $originValue, $isUpdate, $bigLinkOldValue !== "" ? $bigLinkOldValue[$i][$k] : "", $deleted);
                        $deleted = true; 
                        $isWas = true; 
                    } 
                }
            }
        } else {
            $this->updateLinkValueSimple($bigLinkKey, $bigLinkValue, $originKey, $originValue, $isUpdate, $bigLinkOldValue);
        }
    }

    private function getFixedArray($format, $array, $linkChanging = true)
    {
        $newArray = [];
        foreach ($format as $kFormat => $vFormat) {
            foreach ($array as $kArr => $vArr) {
                if ($kArr === $kFormat){
                    if (!is_array($vArr)){
                        $key = $vFormat;
                        $value = $vArr;
                        if ($linkChanging){
                            preg_match('/(\w.*?)\.(\w.*)/', $vFormat, $matches);
                            if ($matches[1] !== null)
                                $links = $this->tableLinks[$matches[1]];
                                $origin = $this->tableLinks[$matches[1]][TableHandler::LINKS_ENUM_STRING];
                                if (count($origin) === 1 && (is_array($links) || is_object($links)))
                                    foreach ($links as $k => $v) {
                                        if ($k === TableHandler::LINKS_ENUM_STRING) continue;
                                        $t = $this->tableLinks[$matches[1]][TableHandler::LINKS_ENUM_STRING][0];
                                        $c = "$t.".$matches[2];
                                        $value = $this->getSQLValue("SELECT $v FROM $t WHERE $c = '$vArr'", PDO::FETCH_NUM)[0][0];
                                        $key = $k;
                                    }
                        }
                        $newArray[$key] = $value;
                    } else {
                        $newArray[$kArr] = [];
                        for ($i=0; $i < count($vArr); $i++) {
                            $newArray[$kArr][$i] = $this->getFixedArray($vFormat, $vArr[$i], false);
                        }
                    }
                }
            }
        }
        return $newArray;
    }

    private function selectByLinkId($id, $linkValue)
    {
        $value;
        preg_match('/(\w.*?)\.(\w.*)/', $linkValue, $matches);
        $linkName = $matches[1];
        $linkField = $matches[2];
        $tableLink = [];

        foreach ($this->tableLinks as $kLink => $vLink)
            if ($linkName === $kLink)
                $tableLink = $vLink;

        $count = count($tableLink[TableHandler::LINKS_ENUM_STRING]);
        if ($count > 0) {
            $linkTableName = $tableLink[TableHandler::LINKS_ENUM_STRING][0];

            $linksSelect = str_replace($linkName, $linkTable, $vFormat[0]);
            for ($i=1; $i < count($vFormat); $i++) {
                $linksSelect .= ', '.str_replace($linkName, $linkTable, $vFormat[$i]);
            }

            $linksFrom = "";
            for ($i=0; $i < count($tableLink[TableHandler::LINKS_ENUM_STRING]); $i++) { 
                $linksFrom .= ', '.$tableLink[TableHandler::LINKS_ENUM_STRING][$i];
            }

            $linksWhere = "";
            foreach ($tableLink as $k => $v) {
                if ($k === TableHandler::LINKS_ENUM_STRING) continue;
                $linksWhere .= " AND $k = $v";
            }

            $select = is_array($vFormat) ? $linksSelect : "$linkTableName.$linkField";
            $from = "$this->tableName".$linksFrom;
            $where = "$this->tableName.$this->tableMainID = $id".$linksWhere;

            $value = $this->getSQLValue("SELECT $select FROM $from WHERE $where", is_array($vFormat) ? PDO::FETCH_ASSOC : PDO::FETCH_NUM);
            $value = $this->getFixedSQLValue($value);
        }
        return $value;
    }

    private function selectByLinkIdMultiple($id, $table)
    {
        $array;
        foreach ($table as $key => $value) {
            $array[$key] = $this->selectByLinkId($id, $value);
        }
        foreach ($array as $key => $value) {
            if (!is_array($value)){
                $a = [];
                $a[0] = $value;
                $array[$key] = $a;
            }
        }
        return array_transpose($array);
    }

    private function getSQLValue($sqlString, $pdoFetch = PDO::FETCH_ASSOC)
    {
        $sql = $sqlString;
        $result = $this->dbConn->query($sql);
        $array = $result->fetchAll($pdoFetch);
        return $array;
    }

    private function getFixedSQLValue($value)
    {
        if(count($value) === 1 && count($value[0]) === 1)
            return $value[0][0];

        if(count($value) === 1)
            return $value[0];

        if(count($value) > 1 && count($value[0]) === 1){
            for ($i=0; $i < count($value); $i++) { 
                if(count($value[$i]) == 1)
                    $value[$i] = $value[$i][0];
            }
            return $value;
        }

        return $value;
    }
}