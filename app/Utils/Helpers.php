<?php

namespace App\Utils;

use App\Models\Incident;
use Carbon\Carbon;

class Helpers
{
   
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
