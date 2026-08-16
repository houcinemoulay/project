<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait SearchesColumns
{
    /**
     * Constrain $query to rows matching $search on any of the given columns.
     *
     * @param  list<string>  $likeColumns   matched with a LIKE %term%
     * @param  list<string>  $exactColumns  matched with equality (ids, card UIDs, ...)
     */
    protected function applySearch(Builder $query, ?string $search, array $likeColumns, array $exactColumns = []): void
    {
        if (!$search) {
            return;
        }

        $query->where(function (Builder $q) use ($search, $likeColumns, $exactColumns) {
            foreach ($likeColumns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }

            foreach ($exactColumns as $column) {
                $q->orWhere($column, $search);
            }
        });
    }
}
