<?php

namespace App\Services;

use App\Filters\AdminFilter;
use App\Filters\ArtistFilter;
use App\Filters\UserFilter;
use App\Models\Admin;
use App\Models\Artist;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * Main service that handles all filtering across the app
 */
class FilterService
{
    /**
     * Map of model classes to their corresponding filter classes
     *
     * @var array<string, string>
     */
    protected array $filterMap = [
        Admin::class => AdminFilter::class,
        User::class => UserFilter::class,
        Artist::class => ArtistFilter::class,
    ];

    /**
     * Apply filters to a query based on the model type
     *
     * @param  Builder  $query  The query builder
     * @param  array  $filters  The filter data from the request
     */
    public function applyFilters(Builder $query, array $filters): void
    {
        $filterClass = $this->detectModelFromQuery($query);

        if (! $filterClass) {
            return;
        }

        // Use Laravel's container to properly resolve dependencies
        $filter = app($filterClass, ['filters' => $filters]);
        $filter->apply($query);
    }

    /**
     * Detect the model class from a query and return the corresponding filter class
     *
     * @param  Builder  $query  The query builder
     * @return string|null The filter class name or null if not found
     */
    protected function detectModelFromQuery(Builder $query): ?string
    {
        try {
            $modelClass = get_class($query->getModel());

            return $this->filterMap[$modelClass] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }
}
