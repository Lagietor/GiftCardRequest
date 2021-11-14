<?php

class ActionObjectOrderAddAfterController
{

    public function __construct($module, $WebhookURL)
    {
        $this->module = $module;
        $this->URL = $WebhookURL;
    }

    public function run($params)
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
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->URL,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($requestData)
        ]);

        $response = curl_exec($curl);

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return ($http_status == 200) ? $response : false;
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
