<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\GbpLocation;
use App\Models\Integration;
use App\Services\GbpService;
use Illuminate\Http\Request;

class InsightsController extends Controller
{
    public function index(Request $request, GbpService $gbp)
    {
        $user = $request->user();
        $aid = $user->agency_id;
        $clientId = $user->client_id;

        $locations = GbpLocation::whereHas('client', function ($q) use ($aid, $clientId) {
            $q->where('agency_id', $aid);
            if ($clientId) $q->where('id', $clientId);
        })->with('client')->get();

        $connectedClientIds = Integration::where('agency_id', $aid)
            ->where('provider', 'GOOGLE_GBP')->whereNotNull('access_token')
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->pluck('client_id')->all();

        $hasGoogle = ! empty($connectedClientIds);

        $startDate = $request->input('start', now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->input('end', now()->format('Y-m-d'));
        $locationId = $request->input('location');

        $metrics = [];
        $selectedLocation = null;
        $error = null;

        if ($hasGoogle && $locations->isNotEmpty()) {
            $selectedLocation = $locationId
                ? $locations->firstWhere('id', $locationId)
                : $locations->first();

            if ($selectedLocation && in_array($selectedLocation->client_id, $connectedClientIds)) {
                $metrics = $gbp->fetchPerformanceMetrics(
                    $selectedLocation->client,
                    $selectedLocation->google_name,
                    $startDate,
                    $endDate
                );
                $error = $gbp->lastError();
            }
        }

        $totals = [];
        $dailyTotals = [];
        foreach ($metrics as $metric => $dataPoints) {
            $totals[$metric] = array_sum($dataPoints);
            foreach ($dataPoints as $date => $val) {
                $dailyTotals[$date] = ($dailyTotals[$date] ?? 0) + $val;
            }
        }
        ksort($dailyTotals);

        $totalInteractions = array_sum($totals);

        return view('dashboard.insights', compact(
            'locations', 'hasGoogle', 'metrics', 'totals', 'dailyTotals',
            'totalInteractions', 'startDate', 'endDate', 'selectedLocation', 'error'
        ));
    }

    public function download(Request $request, GbpService $gbp)
    {
        $user = $request->user();
        $aid = $user->agency_id;
        $clientId = $user->client_id;

        $startDate = $request->input('start', now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->input('end', now()->format('Y-m-d'));
        $locationId = $request->input('location');

        $location = GbpLocation::whereHas('client', function ($q) use ($aid, $clientId) {
            $q->where('agency_id', $aid);
            if ($clientId) $q->where('id', $clientId);
        })->when($locationId, fn ($q) => $q->where('id', $locationId))->first();

        if (! $location) {
            return back()->with('error', 'Location not found.');
        }

        $connectedClientIds = Integration::where('agency_id', $aid)
            ->where('provider', 'GOOGLE_GBP')->whereNotNull('access_token')
            ->pluck('client_id')->all();

        if (! in_array($location->client_id, $connectedClientIds)) {
            return back()->with('error', 'Google not connected for this location.');
        }

        $metrics = $gbp->fetchPerformanceMetrics(
            $location->client,
            $location->google_name,
            $startDate,
            $endDate
        );

        $allDates = [];
        foreach ($metrics as $dataPoints) {
            foreach (array_keys($dataPoints) as $d) {
                $allDates[$d] = true;
            }
        }
        ksort($allDates);
        $allDates = array_keys($allDates);

        $metricLabels = [
            'BUSINESS_IMPRESSIONS_DESKTOP_MAPS' => 'Desktop Maps Views',
            'BUSINESS_IMPRESSIONS_DESKTOP_SEARCH' => 'Desktop Search Views',
            'BUSINESS_IMPRESSIONS_MOBILE_MAPS' => 'Mobile Maps Views',
            'BUSINESS_IMPRESSIONS_MOBILE_SEARCH' => 'Mobile Search Views',
            'CALL_CLICKS' => 'Call Clicks',
            'WEBSITE_CLICKS' => 'Website Clicks',
            'BUSINESS_DIRECTION_REQUESTS' => 'Direction Requests',
            'BUSINESS_BOOKINGS' => 'Bookings',
        ];

        $csv = "Date";
        $metricKeys = array_keys($metrics);
        foreach ($metricKeys as $mk) {
            $csv .= ',"'.($metricLabels[$mk] ?? $mk).'"';
        }
        $csv .= ",Total\n";

        foreach ($allDates as $date) {
            $csv .= $date;
            $rowTotal = 0;
            foreach ($metricKeys as $mk) {
                $val = $metrics[$mk][$date] ?? 0;
                $csv .= ','.$val;
                $rowTotal += $val;
            }
            $csv .= ','.$rowTotal."\n";
        }

        $filename = 'insights-'.($location->title ? \Illuminate\Support\Str::slug($location->title) : 'location').'-'.$startDate.'-to-'.$endDate.'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
