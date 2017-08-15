<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 13.06.17
 * Time: 15:28
 */

namespace Scaly\Database;


use Scaly\Config\IniConfig;
use Scaly\Core\Config\ScalyConfig;

class DatabaseManager
{

    const CONFIG_DATABASE_LIST = 'Database.List';

    /**
     * @var DatabaseManager
     */
    private static $instance;

    /**
     * @var IniConfig $iniConfig
     */
    private $iniConfig;


    public function __construct()
    {
        self::$instance = $this;
        ScalyConfig::getInstance()->setDefaultAndSave(self::CONFIG_DATABASE_LIST, '{{App.Home}}/database.list');

        if (!file_exists(ScalyConfig::getInstance()->get(self::CONFIG_DATABASE_LIST))) {
            $iniConfig = new IniConfig(ScalyConfig::getInstance()->get(self::CONFIG_DATABASE_LIST), [], true, INI_SCANNER_RAW);
            $iniConfig->set('MyDatabase.ConnectionString', 'mysql:host=localhost:33q06;dbname=myDatabase;charset=utf8');
            $iniConfig->set('MyDatabase.User', 'root');
            $iniConfig->set('MyDatabase.Pass', 'password');
            $iniConfig->save();
        }

        $this->iniConfig = new IniConfig(ScalyConfig::getInstance()->get(self::CONFIG_DATABASE_LIST), [], true, INI_SCANNER_RAW);
        $this->iniConfig->load();
    }

    /**
     * @return DatabaseManager
     */
    public static function getInstance(): DatabaseManager
    {
        if (!self::$instance) {
            new DatabaseManager();
        }
        return self::$instance;
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