<?php

namespace App\Support;

use App\Models\Department;
use App\Models\RoutingRule;
use Illuminate\Support\Collection;

/**
 * Shared data for the document submission form (the New Submission modal and
 * the edit page): the department list, suggested routing paths per document
 * type, and the category list. Fed to the layout via a view composer.
 */
class DocumentFormOptions
{
    /** Categories offered in the submission form's "Category" dropdown. */
    public static function categoryOptions(): array
    {
        return [
            'Business Permit',
            'Barangay Clearance',
            'Building Permit',
            "Mayor's Permit",
            'Real Property Tax',
            'Birth Certificate Request',
            'Community Tax Certificate',
            'Other',
        ];
    }

    /** All departments ordered by name. */
    public static function departments(): Collection
    {
        return Department::orderBy('name')->get();
    }

    /**
     * Suggested routing path (ordered department id list) keyed by document
     * type, derived from the configured RoutingRule chains.
     */
    public static function defaultRoutesByType(): Collection
    {
        return RoutingRule::with(['fromDepartment', 'toDepartment'])
            ->orderBy('document_type')
            ->orderBy('step_order')
            ->get()
            ->groupBy('document_type')
            ->map(function ($rules) {
                $chain = collect([$rules->first()->fromDepartment?->id]);
                foreach ($rules as $rule) {
                    if ($rule->toDepartment) {
                        $chain->push($rule->toDepartment->id);
                    }
                }

                return $chain->filter()->unique()->values()->all();
            });
    }
}
