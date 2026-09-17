<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort')->orderBy('price')->get();
        $modules = Plan::MODULES;
        return view('admin.plans', compact('plans', 'modules'));
    }

    public function store(Request $request)
    {
        Plan::create($this->data($request));
        return back()->with('success', 'Plan created.');
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($this->data($request, $plan));
        return back()->with('success', 'Plan updated.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return back()->with('success', 'Plan deleted.');
    }

    private function data(Request $request, ?Plan $plan = null): array
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'code'     => ['required', 'string', 'max:40', Rule::unique('plans', 'code')->ignore($plan?->id)],
            'price'    => 'required|integer|min:0',      // GST-inclusive
            'gst_rate' => 'required|integer|min:0|max:50',
            'credits'  => 'required|integer|min:0',
            'sort'     => 'nullable|integer|min:0',
        ]);

        return [
            'name'        => $request->string('name'),
            'code'        => strtoupper(preg_replace('/[^A-Za-z0-9_]/', '', $request->input('code'))),
            'price'       => (int) $request->input('price'),
            'gst_rate'    => (int) $request->input('gst_rate'),
            'credits'     => (int) $request->input('credits'),
            'features'    => array_values(array_filter(array_map('trim', explode("\n", (string) $request->input('features', ''))))),
            'permissions' => array_values($request->input('permissions', [])),
            'is_active'   => $request->boolean('is_active'),
            'sort'        => (int) $request->input('sort', 0),
        ];
    }
}
