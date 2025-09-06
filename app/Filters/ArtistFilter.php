<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Handles all filtering logic for Artist models
 */
class ArtistFilter extends BaseFilter
{
    /**
     * Apply all filters to the query
     *
     * @param  Builder  $query  The query builder
     */
    public function apply(Builder $query): void
    {
        $this->applySearchFilter($query);
        $this->applyOwnerFilter($query);
        $this->applyDateFilters($query);
        $this->applySortBy($query);
        $this->applyWithInactiveFilter($query);
        $this->applyOnlyInactiveFilter($query);
    }

    /**
     * Apply search filter by name or spotify_id
     *
     * @param  Builder  $query  The query builder
     */
    private function applySearchFilter(Builder $query): void
    {
        $search = $this->getString('search');

        if (! $search || strlen($search) < 2) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('spotify_id', 'like', "%{$search}%");
        });
    }

    /**
     * Apply owner filter by owner ID
     *
     * @param  Builder  $query  The query builder
     */
    private function applyOwnerFilter(Builder $query): void
    {
        $ownerId = $this->getInt('owner_id');

        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }
    }


    /**
     * Get artist-specific sortable fields
     *
     * @return array List of artist-specific sortable field names
     */
    protected function getModelSpecificSortableFields(): array
    {
        return [
            'name',
        ];
    }
}
