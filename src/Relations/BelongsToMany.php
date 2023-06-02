<?php

namespace Faliure\SeparateDatabases\Relations;

use Faliure\SeparateDatabases\Relations\Concerns\InteractsWithPivotTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as LaravelBelongsToMany;

class BelongsToMany extends LaravelBelongsToMany
{
    use InteractsWithPivotTable;

    /**
     * Witness to ensure lazy constraints are applied only once.
     */
    protected bool $lazyConstraintsApplied = false;

    /**
     * Execute the query as a "select" statement.
     *
     * @param array  $columns
     */
    public function get($columns = ['*']): Collection
    {
        $this->addLazyConstraints();

        return parent::get($columns);
    }

    /**
     * Handle dynamic method calls to the relationship.
     */
    public function __call($method, $parameters): mixed
    {
        $this->addLazyConstraints();

        return parent::__call($method, $parameters);
    }

    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints(): void
    {
        // Do not add any constraints on construction (will be added later, lazily)
    }

    /**
     * Set the latest constraints on the relation query.
     */
    public function addLazyConstraints(): void
    {
        if (static::$constraints && ! $this->lazyConstraintsApplied) {
            $this->addWhereConstraints();
        }

        $this->lazyConstraintsApplied = true;
    }

    /**
     * Set the where clause for the relation query.
     */
    protected function addWhereConstraints(): self
    {
        $this->query->whereIn(
            $this->getQualifiedRelatedKeyName(),
            $this->getRelatedIds(),
        );

        return $this;
    }

    /**
     * Get the ids of the related model to filter by.
     */
    protected function getRelatedIds(): array
    {
        return $this->newPivotQuery()
            ->where($this->getQualifiedForeignPivotKeyName(), $this->parent->getKey())
            ->pluck($this->getQualifiedRelatedPivotKeyName())
            ->all();
    }
}
