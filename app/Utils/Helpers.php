<?php

namespace App\Utils;

use App\Models\Incident;
use Carbon\Carbon;

class Helpers
{
   /**
     * Build a base query for Incidents with optional filters.
     *
     * This method constructs a query that can filter incidents based on their
     * school, the school's division, or the school's region.
     *
     * @param array $filters An associative array of potential filters.
     * - 'school_id' (int|string): Filter by a specific school ID.
     * - 'division_id' (int|string): Filter incidents belonging to a specific division.
     * - 'region_id' (int|string): Filter incidents belonging to a specific region.
     */
   public static function baseIncidentQuery(array $filters)
   {
      return Incident::query()
         ->when($filters['school_id'] ?? null, fn($q, $schoolId) => $q->where('school_id', $schoolId))
         ->when($filters['division_id'] ?? null, function ($q, $divisionId) {
            $q->whereHas('school', fn($s) => $s->where('division_id', $divisionId));
         })
         ->when($filters['region_id'] ?? null, function ($q, $regionId) {
            $q->whereHas(
               'school',
               fn($s) =>
               $s->whereHas('division', fn($d) => $d->where('region_id', $regionId))
            );
         });
   }

   /**
    * Central place to map UI buckets -> DB values.
    * If you use an Enum (e.g., App\Enums\IncidentStatus), replace these values with ->value.
    */
   public static function statusMap(): array
   {
      return [
         'open' => 'open',
         'resolved' => 'resolved',
         'in_progress' => 'in_progress',
         'closed' => 'closed',
         'disapproved' => 'disapproved',
      ];
   }

   /**
    * Resolve current and previous rolling windows.
    * If date_from/date_to provided, use that explicitly; otherwise use window_days (default 7).
    */
   public static function resolveWindows(array $filters): array
   {
      $windowDays = max(1, (int) ($filters['window_days'] ?? 7));

      if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
         $currFrom = Carbon::parse($filters['date_from'])->startOfDay();
         $currTo = Carbon::parse($filters['date_to'])->endOfDay();
         $diffDays = $currFrom->diffInDays($currTo) + 1;
         $prevFrom = (clone $currFrom)->subDays($diffDays);
         $prevTo = (clone $currFrom)->subSecond();
         return [$currFrom, $currTo, $prevFrom, $prevTo, $diffDays];
      }

      $currTo = now();
      $currFrom = (clone $currTo)->subDays($windowDays - 1)->startOfDay();
      $prevTo = (clone $currFrom)->subSecond();
      $prevFrom = (clone $currFrom)->subDays($windowDays)->startOfDay();

      return [$currFrom, $currTo, $prevFrom, $prevTo, $windowDays];
   }
}
