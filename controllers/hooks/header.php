<?php
// KLASA TESTOWA
class HeaderController
{
    public function __construct($module)
    {
        $this->module = $module;
    }

    public function run(): void
    {
        // NIEDOKOŃCZONA METODA, ZAKOMENTOWANA W CELU UNIKNIĘCIA TYMCZASOWYCH BŁĘDÓW
        // $data = $this->getData();

        // foreach ($data as $index => $d) {
        //     $data[$index] = $this->getLastData($d['tableName'], $d['idName'], $d['dataName']);
        // }

        if (!empty($data)) {
            Db::getInstance()->insert('test', $data);

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

        $query = "SELECT * FROM " . _DB_PREFIX_ . "test ORDER BY id DESC LIMIT 1";
        $requestData = Db::getInstance()->executeS($query);

        return $requestData;
    }

    public function sendWebhook(array $requestData)
    {
        echo json_encode($requestData);
        die();
    }

    public function getData(): array
    {
        // Przykładowe dane do nowo utworzonej tablicy testowej ps_test
        return [
            'dane1' => [
                'tableName' => 'ps_address',
                'idName' => 'id_address',
                'dataName' => 'alias'
                ],

            'dane2' => [
                'tableName' => 'ps_carrier',
                'idName' => 'id_carrier',
                'dataName' => 'name'
                ]
            ];
    }
}
