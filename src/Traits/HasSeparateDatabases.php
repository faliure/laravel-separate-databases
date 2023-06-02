<?php

namespace Faliure\SeparateDatabases\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasSeparateDatabases
{
    /**
     * Instantiate a new BelongsToMany relationship, with support for separate databases.
     */
    protected function newBelongsToMany(Builder $query, Model $parent, ...$rest): BelongsToMany
    {
        $relation = $this->relationInvolvesSeparateDatabases($query->getModel())
            ? \Faliure\SeparateDatabases\Relations\BelongsToMany::class
            : BelongsToMany::class;

        return new $relation($query, $parent, ...$rest);
    }

    /**
     * Whether this relationship involves models in separated tables.
     */
    protected function relationInvolvesSeparateDatabases(...$otherModels): bool
    {
        $connections = array_map(
            fn ($model) => $model->getConnection()->getName(),
            [ $this, ...$otherModels ]
        );

        return count(array_unique($connections)) > 1;
    }
}
