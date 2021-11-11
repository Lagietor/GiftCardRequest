<?php

class ActionObjectOrderAddAfterController
{

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function run()
    {
        $data = $this->getData();

        foreach ($data as $index => $d) {
            $data[$index] = $this->getLastData($d['tableName'], $d['idName'], $d['dataName']);
        }

        if (!empty($data)) {
            Db::getInstance()->insert('ordercreatedata', $data);

            $requestData = $this->getRequestData();

            $this->sendWebhook($requestData);
        }
    }

    public function getLastData(string $tableName, string $idName, string $dataName): string
    {
        $data =  Db::getInstance()->getValue(
            "SELECT " . $dataName . " FROM " . $tableName . " ORDER BY " . $idName . " DESC"
        );

        return $data;
    }

    public function getRequestData(): array
    {
        $query = "SELECT * FROM " . _DB_PREFIX_ . "ordercreatedata ORDER BY order_id DESC LIMIT 1";
        $requestData = Db::getInstance()->executeS($query);

        return $requestData;
    }

    public function sendWebhook(array $requestData)
    {
        // echo json_encode($requestData);
        // die();
    }

    public function getData(): array
    {
        return [
            'email' => [
                'tableName' => 'ps_address',
                'idName' => 'id_address',
                'dataName' => 'alias'
                ],

            'notes' => [
                'tableName' => 'ps_carrier',
                'idName' => 'id_carrier',
                'dataName' => 'name'
                ]
            ];
    }
}
