<?php

namespace Scalar\Database;

interface DatabaseInterface
{

    public function getName();

    public function getConnectionString();

    public function getUser();

    public function getOptions();

    /**
     * Execute raw query on database
     *
     * @param string $query
     * @return mixed
     */
    public function query
    (
        $query
    );

    /**
     * Get raw pdo instance
     * @return \PDO
     */
    public function getPdoInstance();

    /**
     * Establish connection to database
     *
     * @return bool
     */
    public function connect();

    /**
     * Close connection to database
     *
     * @return void
     */
    public function disconnect();

    /**
     * Returns current database connection status
     *
     * @return bool
     */
    public function isConnected();

}