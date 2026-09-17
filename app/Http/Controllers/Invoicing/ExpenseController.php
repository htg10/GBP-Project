<?php

namespace App\Http\Controllers\Invoicing;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $month = $request->query('month'); // 'YYYY-MM' or null for all-time

        $query = Expense::where('agency_id', $agencyId);
        if ($month) {
            $query->whereYear('date', substr($month, 0, 4))->whereMonth('date', substr($month, 5, 2));
        }
        $expenses = $query->orderByDesc('date')->get();

        $thisMonth = Expense::where('agency_id', $agencyId)
            ->whereYear('date', now()->year)->whereMonth('date', now()->month)->get();

        $stats = [
            'spent' => $thisMonth->sum('amount'),
            'count' => $expenses->count(),
        ];

        return view('dashboard.invoicing.expenses', compact('expenses', 'stats', 'month'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'method' => 'required|in:CASH,BANK,UPI,CARD,OTHER',
            'amount' => 'required|numeric|min:0',
        ]);
        $data['agency_id'] = $request->user()->agency_id;
        Expense::create($data);

        return back()->with('success', 'Expense logged.');
    }

    public function destroy(Request $request, Expense $expense)
    {
        abort_unless($expense->agency_id === $request->user()->agency_id, 403);
        $expense->delete();
        return back()->with('success', 'Expense deleted.');
    }
}
