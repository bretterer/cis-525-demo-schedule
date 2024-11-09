<?php

namespace Bretterer\DemonstrationScheduling;

use mysqli;

abstract class Migration
{
    /**
     * The name of the database connection to use.
     *
     * @var mysqli
     */
    protected mysqli $connection;

    /**
     * The up queries that need to be run.
     *
     * @var array
     */
    protected array $upQueries = [];

    /**
     * The down queries that need to be run.
     *
     * @var array
     */
    protected array $downQueries = [];

    /**
     * Set the migration connection name.
     *
     * @param mysqli $connection
     */
    public function setConnection(mysqli $connection)
    {
        $this->connection = $connection;
    }
    /**
     * Get the migration connection name.
     *
     * @return mysqli
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    /**
     * Run the migration.
     *
     * @return void
     */
    public function build(): void
    {
        foreach ($this->upQueries as $query) {
            $this->getConnection()->execute_query($query);
        }
    }

    /**
     * Rollback the migration.
     *
     * @return void
     */
    public function rollback(): void
    {
        foreach ($this->downQueries as $query) {
            $this->getConnection()->execute_query($query);
        }
    }
}
