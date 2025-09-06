<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Base class for all filters
 */
abstract class BaseFilter
{
    protected array $filters;

    /**
     * Constructor
     *
     * @param  array  $filters  The filter data from the request
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Apply all filters to the query
     *
     * @param  Builder  $query  The query builder
     */
    abstract public function apply(Builder $query): void;

    /**
     * Check if a filter key exists and has a value
     *
     * @param  string  $key  The filter key
     * @return bool True if the filter exists and has a value
     */
    protected function has(string $key): bool
    {
        return ! empty($this->filters[$key]);
    }

    /**
     * Get a filter value with optional default
     *
     * @param  string  $key  The filter key
     * @param  mixed  $default  What to return if the key doesn't exist
     * @return mixed The filter value or default
     */
    protected function get(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }

    /**
     * Get a filter value as a trimmed string
     *
     * @param  string  $key  The filter key
     * @return string|null The trimmed string or null
     */
    protected function getString(string $key): ?string
    {
        $value = $this->get($key);

        return $value ? trim($value) : null;
    }

    /**
     * Get a filter value as an integer
     *
     * @param  string  $key  The filter key
     * @return int|null The integer or null
     */
    protected function getInt(string $key): ?int
    {
        $value = $this->get($key);

        return $value ? (int) $value : null;
    }

    /**
     * Get a filter value as a boolean
     *
     * @param  string  $key  The filter key
     * @return bool|null The boolean or null
     */
    protected function getBoolean(string $key): ?bool
    {
        $value = $this->get($key);

        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));

            return in_array($value, ['1', 'true']);
        }

        return (bool) $value;
    }

    /**
     * Apply common date range filters (created_at, updated_at, deleted_at)
     *
     * @param  Builder  $query  The query builder
     */
    protected function applyDateFilters(Builder $query): void
    {
        $this->applyCreatedAtFilter($query);
        $this->applyUpdatedAtFilter($query);
        $this->applyDeletedAtFilter($query);
    }

    /**
     * Apply withInactive filter to include/exclude deleted records
     *
     * @param  Builder  $query  The query builder
     */
    protected function applyWithInactiveFilter(Builder $query): void
    {
        $withInactive = $this->getBoolean('with_inactive');

        if ($withInactive === true) {
            $query->withTrashed();
        }
    }

    /**
     * Apply onlyInactive filter to show only deleted records
     *
     * @param  Builder  $query  The query builder
     */
    protected function applyOnlyInactiveFilter(Builder $query): void
    {
        $onlyInactive = $this->getBoolean('only_inactive');

        if ($onlyInactive) {
            $query->onlyTrashed();
        }
    }

    /**
     * Apply created_at date range filter
     *
     * @param  Builder  $query  The query builder
     */
    protected function applyCreatedAtFilter(Builder $query): void
    {
        $from = $this->get('created_from');
        $to = $this->get('created_to');

        if ($from) {
            $query->where('created_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to) {
            $query->where('created_at', '<=', Carbon::parse($to)->endOfDay());
        }
    }

    /**
     * Apply updated_at date range filter
     *
     * @param  Builder  $query  The query builder
     */
    protected function applyUpdatedAtFilter(Builder $query): void
    {
        $from = $this->get('updated_from');
        $to = $this->get('updated_to');

        if ($from) {
            $query->where('updated_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to) {
            $query->where('updated_at', '<=', Carbon::parse($to)->endOfDay());
        }
    }

    /**
     * Apply deleted_at date range filter
     *
     * @param  Builder  $query  The query builder
     */
    protected function applyDeletedAtFilter(Builder $query): void
    {
        $from = $this->get('deleted_from');
        $to = $this->get('deleted_to');

        if ($from) {
            $query->where('deleted_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to) {
            $query->where('deleted_at', '<=', Carbon::parse($to)->endOfDay());
        }
    }

    /**
     * Apply common sorting for standard model fields
     *
     * @param  Builder  $query  The query builder
     */
    protected function applySortBy(Builder $query): void
    {
        $sortBy = $this->getString('sort_by') ?: 'created_at';
        $sortDirection = $this->getString('sort_direction') ?: 'desc';

        $allowedFields = $this->getAllSortableFields();

        if (! in_array($sortBy, $allowedFields)) {
            return;
        }

        $query->reorder()->orderBy($sortBy, $sortDirection);
    }

    /**
     * Get common sortable fields that most models will have
     *
     * @return array List of common sortable field names
     */
    protected function getCommonSortableFields(): array
    {
        return [
            'id',
            'created_by',
            'updated_by',
            'deleted_by',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    /**
     * Get all sortable fields (common + model-specific)
     * Override this method in child classes to add more fields
     *
     * @return array Combined list of all sortable fields
     */
    protected function getAllSortableFields(): array
    {
        return array_merge(
            $this->getCommonSortableFields(),
            $this->getModelSpecificSortableFields()
        );
    }

    /**
     * Get model-specific sortable fields
     * Override this method in child classes to add more fields
     *
     * @return array List of model-specific sortable fields
     */
    protected function getModelSpecificSortableFields(): array
    {
        return [];
    }
}
