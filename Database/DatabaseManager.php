<?php

namespace Scalar\Database;


use Scalar\Config\IniConfig;
use Scalar\Core\Scalar;

class DatabaseManager
{

    const CONFIG_DATABASE_LIST = 'Database.List';

    /**
     * @var IniConfig $iniConfig
     */
    private $iniConfig;


    public function __construct()
    {
        $scalarConfig = Scalar::getService
        (
            Scalar::SERVICE_SCALAR_CONFIG
        );

        $scalarConfig->setDefaultAndSave(self::CONFIG_DATABASE_LIST, '{{App.Home}}/database.list');

        if (!file_exists($scalarConfig->get(self::CONFIG_DATABASE_LIST))) {
            $iniConfig = new IniConfig($scalarConfig->get(self::CONFIG_DATABASE_LIST), [], true, INI_SCANNER_RAW);
            $iniConfig->set('MyDatabase.ConnectionString', 'mysql:host=localhost:33q06;dbname=myDatabase;charset=utf8');
            $iniConfig->set('MyDatabase.User', 'root');
            $iniConfig->set('MyDatabase.Pass', 'password');
            $iniConfig->save();
        }

        $this->iniConfig = new IniConfig($scalarConfig->get(self::CONFIG_DATABASE_LIST), [], true, INI_SCANNER_RAW);
        $this->iniConfig->load();
    }

    /**
     * @deprecated
     * @return DatabaseManager
     */
    public static function getInstance()
    {
        return Scalar::getService
        (
            Scalar::SERVICE_DATABASE_MANAGER
        );
    }

    public function getDatabase
    (
        $database
    )
    {
        if (!$this->hasDatabase($database)) {
            return null;
        }

        return new PDODatabase
        (
            $database,
            $this->iniConfig->get($database . '.ConnectionString'),
            $this->iniConfig->get($database . '.User'),
            $this->iniConfig->get($database . '.Pass')
        );
    }

    public function hasDatabase
    (
        $database
    )
    {
        return $this->iniConfig->has($database);
    }

}