<?php

namespace App\Http\Controllers\Invoicing;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ServiceCategory::where('agency_id', $request->user()->agency_id)
            ->withCount('services')->orderBy('name')->get();

        return view('dashboard.invoicing.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $data['agency_id'] = $request->user()->agency_id;
        ServiceCategory::create($data);

        return back()->with('success', 'Category added.');
    }

    public function destroy(Request $request, ServiceCategory $category)
    {
        abort_unless($category->agency_id === $request->user()->agency_id, 403);
        $category->delete(); // services keep their name via nullOnDelete — nothing else breaks
        return back()->with('success', 'Category deleted.');
    }
}
