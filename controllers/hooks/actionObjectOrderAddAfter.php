<?php

class ActionObjectOrderAddAfterController
{

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function run()
    {
        $data = $this->getLastData('ps_address', 'id_address', 'alias');
        dump($data);
        die();
    }

    public function getLastData($tableName, $idName, $dataName)
    {
        $data =  Db::getInstace()->getValue(
            "SELECT " . $dataName . " FROM " . $tableName . " ORDER BY " . $idName . " DESC LIMIT 1"
        );

        return $data;
    }
}
