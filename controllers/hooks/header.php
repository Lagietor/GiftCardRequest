<?php
// KLASA TESTOWA
class HeaderController
{
    public function __construct($module)
    {
        $this->module = $module;
    }

    public function run()
    {
        // NIEDOKOŃCZONA METODA, ZAKOMENTOWANA W CELU UNIKNIĘCIA TYMCZASOWYCH BŁĘDÓW
        // $table = $this->getTable();

        // foreach ($table as $index => $t) {
        //     $data[$index] = $this->getLastData($t['tableName'], $t['idName'], $t['dataName']);
        // }

        // Db::getInstance()->insert('test', $data);
    }

    public function getLastData($tableName, $idName, $dataName)
    {
        $data =  Db::getInstance()->getValue(
            "SELECT " . $dataName . " FROM " . $tableName . " ORDER BY " . $idName . " DESC"
        );

        return $data;
    }

    public function getTable()
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
