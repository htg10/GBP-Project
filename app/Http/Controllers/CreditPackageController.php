<?php

namespace App\Http\Controllers;

use App\Models\CreditPackage;
use Illuminate\Http\Request;

/** Super Admin: create/edit credit packages and pause/activate them. */
class CreditPackageController extends Controller
{
    public function index()
    {
        $packages = CreditPackage::orderBy('sort')->orderBy('credits')->get();
        return view('admin.credit-packages', compact('packages'));
    }

    public function store(Request $request)
    {
        CreditPackage::create($this->data($request));
        return back()->with('success', 'Credit package created.');
    }

    public function update(Request $request, CreditPackage $package)
    {
        $package->update($this->data($request));
        return back()->with('success', 'Credit package updated.');
    }

    public function toggle(Request $request, CreditPackage $package)
    {
        $package->update(['is_active' => ! $package->is_active]);
        return back()->with('success', $package->is_active ? 'Package activated.' : 'Package paused.');
    }

    public function destroy(CreditPackage $package)
    {
        $package->delete();
        return back()->with('success', 'Credit package deleted.');
    }

    private function data(Request $request): array
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'credits'  => 'required|integer|min:1',
            'price'    => 'required|integer|min:0',
            'gst_rate' => 'required|integer|min:0|max:50',
            'sort'     => 'nullable|integer|min:0',
        ]);

        return [
            'name'      => $request->string('name'),
            'credits'   => (int) $request->input('credits'),
            'price'     => (int) $request->input('price'),
            'gst_rate'  => (int) $request->input('gst_rate'),
            'is_active' => $request->boolean('is_active'),
            'sort'      => (int) $request->input('sort', 0),
        ];
    }
}
