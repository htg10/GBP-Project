<?php

namespace App\Http\Controllers\Invoicing;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $services = Service::where('agency_id', $agencyId)->with('category')->orderBy('name')->get();
        $categories = ServiceCategory::where('agency_id', $agencyId)->orderBy('name')->get();

        return view('dashboard.invoicing.services', compact('services', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'price' => 'required|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
        ]);
        $data['agency_id'] = $request->user()->agency_id;
        Service::create($data);

        return back()->with('success', 'Service added.');
    }

    public function update(Request $request, Service $service)
    {
        abort_unless($service->agency_id === $request->user()->agency_id, 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'price' => 'required|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);
        $service->update($data);

        return back()->with('success', 'Service updated.');
    }

    public function destroy(Request $request, Service $service)
    {
        abort_unless($service->agency_id === $request->user()->agency_id, 403);
        $service->delete();
        return back()->with('success', 'Service deleted.');
    }
}
