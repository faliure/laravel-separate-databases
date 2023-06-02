<?php

namespace Faliure\SeparateDatabases\Relations\Concerns;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

trait InteractsWithPivotTable
{
    /**
     * The name of the connection for the pivot table.
     */
    protected string $pivotConnection;

    /**
     * Provide the name of the pivot connection, if it doesn't use the same
     * connection as the related model.
     */
    public function pivotConnection(string $connectionName): self
    {
        $this->pivotConnection = $connectionName;

        return $this;
    }

    /**
     * Get the pivot columns for the relation.
     *
     * "pivot_" is prefixed ot each column for easy removal later.
     *
     * @return array
     */
    protected function aliasedPivotColumns()
    {
        return []; // TODO
    }

    /**
     * Get the connection name to use for the pivot table.
     */
    protected function getPivotConnection(): Connection
    {
        $connectionName = $this->pivotConnection ?? (
            $this->using
                ? $this->newPivot()->getConnectionName()
                : $this->parent->getConnectionName()
        );

        return Model::resolveConnection($connectionName);
    }

    /**
     * Get a new plain query builder for the pivot table.
     */
    public function newPivotStatement(): Builder
    {
        return $this->getPivotConnection()
            ->query()
            ->from($this->table);
    }
}
