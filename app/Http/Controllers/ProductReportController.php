<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductReportController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_if($product->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'reason' => ['required', Rule::in(array_keys(ProductReport::REASONS))],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        $request->user()->reports()->create([
            ...$validated,
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Laporan terkirim dan akan ditinjau oleh admin.');
    }
}
