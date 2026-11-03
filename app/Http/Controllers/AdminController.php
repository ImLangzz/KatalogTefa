<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $reports = ProductReport::with(['reporter', 'product.seller'])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', ['pending'])
            ->latest()
            ->paginate(15, ['*'], 'reports_page');
        $products = Product::with(['seller', 'images'])->latest()->paginate(12, ['*'], 'products_page');
        $pendingReports = ProductReport::where('status', 'pending')->count();

        return view('admin.index', compact('reports', 'products', 'pendingReports'));
    }

    public function updateReport(Request $request, ProductReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:reviewed,dismissed'],
        ]);

        $report->update($validated);

        return back()->with('success', 'Status laporan berhasil diperbarui.');
    }

    public function destroyProduct(Product $product): RedirectResponse
    {
        $imagePaths = $product->images->pluck('path')->all();

        DB::transaction(function () use ($product): void {
            $product->reports()
                ->where('status', 'pending')
                ->update(['status' => 'reviewed']);

            $product->delete();
        });

        if ($imagePaths !== [] && ! Storage::disk('public')->delete($imagePaths)) {
            throw new \RuntimeException('Foto katalog gagal dihapus.');
        }

        return back()->with('success', 'Katalog telah dihapus. Laporan yang masih menunggu ditandai telah ditinjau dan riwayatnya tetap tersimpan.');
    }
}
