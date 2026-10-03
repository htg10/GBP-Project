<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GbpLocation;
use App\Models\Integration;
use App\Services\GbpService;
use Illuminate\Http\Request;

class InsightsController extends Controller
{
    public function index(Request $request, GbpService $gbp)
    {
        $aid = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $startDate = $request->query('start', now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->query('end', now()->format('Y-m-d'));
        $selectedLocation = $request->query('location');

        $locations = GbpLocation::whereHas('client', function ($q) use ($aid, $clientId) {
                $q->where('agency_id', $aid);
                if ($clientId) $q->where('id', $clientId);
            })
            ->with('client')
            ->get();

        $connectedClientIds = Integration::where('agency_id', $aid)
            ->where('provider', 'GOOGLE_GBP')
            ->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->pluck('client_id')
            ->all();

        $hasGoogle = ! empty($connectedClientIds);
        $metrics = [];
        $error = null;

        if ($hasGoogle && $locations->isNotEmpty()) {
            $locs = $selectedLocation
                ? $locations->where('id', $selectedLocation)
                : $locations->whereIn('client_id', $connectedClientIds);

            foreach ($locs as $loc) {
                $data = $gbp->fetchPerformanceMetrics($loc->client, $loc->google_name, $startDate, $endDate);
                if ($data) {
                    $metrics[$loc->id] = [
                        'location_title' => $loc->title,
                        'data' => $data,
                    ];
                }
            }

            if (empty($metrics)) {
                $error = $gbp->lastError();
            }
        }

        $totals = [];
        $dailyTotals = [];
        foreach ($metrics as $m) {
            foreach ($m['data'] as $row) {
                $date = $row['date'] ?? null;
                foreach ($row as $k => $v) {
                    if ($k === 'date') continue;
                    $totals[$k] = ($totals[$k] ?? 0) + $v;
                    if ($date) {
                        $dailyTotals[$date][$k] = ($dailyTotals[$date][$k] ?? 0) + $v;
                    }
                }
            }
        }

        return response()->json([
            'locations' => $locations->map(fn ($l) => ['id' => $l->id, 'title' => $l->title]),
            'has_google' => $hasGoogle,
            'metrics' => $metrics,
            'totals' => $totals,
            'daily_totals' => $dailyTotals,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'selected_location' => $selectedLocation,
            'error' => $error,
        ]);
    }

    public function download(Request $request, GbpService $gbp)
    {
        $aid = $request->user()->agency_id;
        $clientId = $request->user()->client_id;

        $startDate = $request->query('start', now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->query('end', now()->format('Y-m-d'));
        $selectedLocation = $request->query('location');

        $locations = GbpLocation::whereHas('client', function ($q) use ($aid, $clientId) {
                $q->where('agency_id', $aid);
                if ($clientId) $q->where('id', $clientId);
            })->with('client')->get();

        $connectedClientIds = Integration::where('agency_id', $aid)
            ->where('provider', 'GOOGLE_GBP')
            ->whereNotNull('access_token')
            ->pluck('client_id')->all();

        $locs = $selectedLocation
            ? $locations->where('id', $selectedLocation)
            : $locations->whereIn('client_id', $connectedClientIds);

        $allRows = [];
        foreach ($locs as $loc) {
            $data = $gbp->fetchPerformanceMetrics($loc->client, $loc->google_name, $startDate, $endDate);
            if ($data) {
                foreach ($data as $row) {
                    $allRows[] = $row;
                }
            }
        }

        $labels = [
            'BUSINESS_IMPRESSIONS_DESKTOP_MAPS' => 'Desktop Maps Views',
            'BUSINESS_IMPRESSIONS_DESKTOP_SEARCH' => 'Desktop Search Views',
            'BUSINESS_IMPRESSIONS_MOBILE_MAPS' => 'Mobile Maps Views',
            'BUSINESS_IMPRESSIONS_MOBILE_SEARCH' => 'Mobile Search Views',
            'CALL_CLICKS' => 'Call Clicks',
            'WEBSITE_CLICKS' => 'Website Clicks',
            'BUSINESS_DIRECTION_REQUESTS' => 'Direction Requests',
            'BUSINESS_BOOKINGS' => 'Bookings',
        ];

        $csv = "Date," . implode(',', array_values($labels)) . ",Total\n";
        foreach ($allRows as $row) {
            $line = [$row['date'] ?? ''];
            $total = 0;
            foreach (array_keys($labels) as $key) {
                $val = $row[$key] ?? 0;
                $line[] = $val;
                $total += $val;
            }
            $line[] = $total;
            $csv .= implode(',', $line) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="insights.csv"',
        ]);
    }
}
