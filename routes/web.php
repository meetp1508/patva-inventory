<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Catalog
    Route::middleware('permission:manage products')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('attributes', AttributeController::class)->except(['show']);
        Route::resource('products', ProductController::class);

        // Variants (nested under product)
        Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');

        // Barcode generation, label sheet, download, regenerate
        Route::get('products/{product}/barcode', [BarcodeController::class, 'product'])->name('barcode.product');
        Route::get('products/{product}/barcode/download', [BarcodeController::class, 'download'])->name('barcode.download');
        Route::post('products/{product}/barcode/regenerate', [BarcodeController::class, 'regenerate'])->name('barcode.regenerate');
        Route::get('products/{product}/variants/{variant}/barcode', [BarcodeController::class, 'variant'])->name('barcode.variant');
    });

    // Customers
    Route::middleware('permission:manage customers')->group(function () {
        Route::resource('customers', CustomerController::class);
    });

    // Inventory
    Route::middleware('permission:manage inventory')->group(function () {
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/adjust', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
    });

    // POS / Billing / Invoices
    Route::middleware('permission:billing access')->group(function () {
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('pos/search', [PosController::class, 'search'])->name('pos.search');
        Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('pos/invoice/{invoice}', [PosController::class, 'invoice'])->name('pos.invoice');
        Route::get('pos/invoice/{invoice}/pdf', [PosController::class, 'downloadPdf'])->name('pos.invoice.pdf');

        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

        Route::post('whatsapp/{invoice}/send', [WhatsAppController::class, 'send'])->name('whatsapp.send');
    });

    // Analytics
    Route::middleware('permission:analytics access')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/sales', [ExportController::class, 'sales'])->name('reports.export.sales');
        Route::get('reports/export/products', [ExportController::class, 'products'])->name('reports.export.products');
        Route::get('reports/export/inventory', [ExportController::class, 'inventory'])->name('reports.export.inventory');
    });

    // System (settings + activity log)
    Route::middleware('permission:settings access')->group(function () {
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
    });
});

require __DIR__ . '/auth.php';
