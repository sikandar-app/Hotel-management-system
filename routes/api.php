<?php

use App\Http\Controllers\APIController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingDashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OccupancyController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [APIController::class, 'login']);
Route::post('/forget-password', [APIController::class, 'forget_pass']);
Route::post('/reset-password', [APIController::class, 'reset_pass']);
Route::post('/logout', [APIController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'user']);
        Route::get('/list', [UserController::class, 'index'])->middleware('permission:users-all|users-view');
        Route::post('/', [UserController::class, 'store'])->middleware('permission:users-all|users-create');
        Route::get('/{id}', [UserController::class, 'show'])->middleware('permission:users-all|users-view');
        Route::put('/{id}', [UserController::class, 'update'])->middleware('permission:users-all|users-edit');
        Route::delete('/{id}', [UserController::class, 'delete'])->middleware('permission:users-all|users-delete');
    });
    
    Route::get('/booking-dashboard', [BookingDashboardController::class, 'index']);
    
    // Role Management
    Route::prefix('roles')->group(function () {
        Route::apiResource('/', RoleController::class)->only(['index', 'store']);
        Route::get('/{role}', [RoleController::class, 'show'])->middleware('permission:roles-all|roles-view');
        Route::put('/{role}', [RoleController::class, 'update'])->middleware('permission:roles-all|roles-edit');
        Route::delete('/{role}', [RoleController::class, 'delete'])->middleware('permission:roles-all|roles-delete');
        Route::post('/{role}/permissions', [RoleController::class, 'assignPermissions'])->middleware('permission:roles-all|roles-assign-permissions');
    });

    // Permission Management
    Route::prefix('permissions')->group(function () {
        Route::apiResource('/', PermissionController::class)->only(['index'])->middleware('permission:permissions-all|permissions-view');
    });

    Route::prefix('booking')->group(function () {
        Route::get('/tax', [BookingController::class, 'tax']);
        Route::get('/', [BookingController::class, 'index'])->middleware('permission:booking-all|booking-view');
        Route::post('/', [BookingController::class, 'store'])->middleware('permission:booking-all|booking-create');
        Route::post('/confirmed/{bookingId}', [BookingController::class, 'confirmed'])->middleware('permission:booking-all|booking-create');
        Route::get('/{booking}', [BookingController::class, 'show'])->middleware('permission:booking-all|booking-view');
        Route::put('/{booking}', [BookingController::class, 'update'])->middleware('permission:booking-all|booking-edit');
        Route::delete('/{booking}', [BookingController::class, 'destroy'])->middleware('permission:booking-all|booking-delete');
        Route::get('export-pdf/{booking}', [BookingController::class, 'export'])->middleware('permission:booking-all|booking-export');
        Route::get('/room/{id}/availability/{bookingId?}', [BookingController::class, 'checkRoomAvailability']);
        Route::get('/{id}/invoice/download', [BookingController::class, 'downloadInvoice'])->middleware('permission:invoices-all|invoices-pdf-export');
    });

    Route::prefix('room')->group(function () {
        Route::get('/list', [RoomController::class, 'getRooms']);
        Route::get('/', [RoomController::class, 'index'])->middleware('permission:room-all|room-view');
        Route::post('/', [RoomController::class, 'store'])->middleware('permission:room-all|room-create');
        Route::get('/{room}', [RoomController::class, 'show'])->middleware('permission:room-all|room-view');
        Route::put('/{room}', [RoomController::class, 'update'])->middleware('permission:room-all|room-edit');
        Route::delete('/{room}', [RoomController::class, 'destroy'])->middleware('permission:room-all|room-delete');
    });

    Route::prefix('tax')->group(function () {
        Route::get('/', [TaxController::class, 'index'])->middleware('permission:tax-all|tax-view');
        Route::post('/', [TaxController::class, 'store'])->middleware('permission:tax-all|tax-create');
        Route::get('/{tax}', [TaxController::class, 'show'])->middleware('permission:tax-all|tax-view');
        Route::put('/{tax}', [TaxController::class, 'update'])->middleware('permission:tax-all|tax-edit');
        Route::delete('/{tax}', [TaxController::class, 'destroy'])->middleware('permission:tax-all|tax-delete');
    });

    Route::prefix('invoices')->group(function () {
        Route::get('/all/{bookingId}', [InvoiceController::class, 'index'])->middleware('permission:invoices-all|invoices-view');
        Route::post('/', [InvoiceController::class, 'store'])->middleware('permission:invoices-all|invoices-create');
        Route::get('/{invoice}/approved', [InvoiceController::class, 'approved'])->middleware('permission:invoices-all|invoices-approved');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->middleware('permission:invoices-all|invoices-view');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->middleware('permission:invoices-all|invoices-edit');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->middleware('permission:invoices-all|invoices-delete');
        Route::get('/{id}/download', [InvoiceController::class, 'downloadInvoice'])->middleware('permission:invoices-all|invoices-pdf-export');
    });

    Route::prefix('settings')->middleware('is_admin')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::get('/{setting}', [SettingController::class, 'show']);
        Route::put('/{setting}', [SettingController::class, 'update']);
    });

    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->middleware('permission:expenses-all|expenses-view');
        Route::post('/', [ExpenseController::class, 'store'])->middleware('permission:expenses-all|expenses-create');
        Route::get('/{expense}', [ExpenseController::class, 'show'])->middleware('permission:expenses-all|expenses-view');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->middleware('permission:expenses-all|expenses-edit');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->middleware('permission:expenses-all|expenses-delete');
    });

    Route::prefix('expense-categories')->group(function () {
        Route::get('/', [ExpenseCategoryController::class, 'index'])->middleware('permission:expense-category-all|expense-category-view');
        Route::post('/', [ExpenseCategoryController::class, 'store'])->middleware('permission:expense-category-all|expense-category-create');
        Route::get('/{expense}', [ExpenseCategoryController::class, 'show'])->middleware('permission:expense-category-all|expense-category-view');
        Route::put('/{expense}', [ExpenseCategoryController::class, 'update'])->middleware('permission:expense-category-all|expense-category-edit');
        Route::delete('/{expense}', [ExpenseCategoryController::class, 'destroy'])->middleware('permission:expense-category-all|expense-category-delete');
    });

    Route::get('/occupancy', [OccupancyController::class, 'getOccupancyData'])->middleware('permission:occupancy-all|occupancy-view');
});