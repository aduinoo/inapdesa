<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttractionController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OwnerApplicationController;
use App\Http\Controllers\OwnerHomestayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserCustomerController;
#auth routes
require __DIR__ . '/auth.php';

#public Pages
Route::get('/', [PagesController::class, 'homepage'])
    ->name('home-page');

Route::get('/attNtours', [AttractionController::class, 'index'])
    ->name('attractions-and-tours');

Route::get('/attNtours/tour/{tourKey}', [AttractionController::class, 'show'])
    ->name('attractions-and-tours.show');

Route::get('/maps', [PagesController::class, 'maps'])
    ->name('maps');

Route::get('/contact', [PagesController::class, 'contact'])
    ->name('contact');

Route::get('/list-your-property', [PagesController::class, 'listProperty'])
    ->name('list-property');

Route::post('/set-location', [AttractionController::class, 'setLocation']);




#Dashboard Redirection Based on Role
Route::middleware('auth')->get('/dashboard', function () {

    $user = Auth::user();

    if ($user->role === 1) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 3) {
        return redirect()->route('owner.dashboard');
    }

    // Default: customer
    return redirect()->route('home-page');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/owner-applications', [OwnerApplicationController::class, 'store'])
        ->name('owner-applications.store');
});

#Dashboard for Admin
Route::middleware(['auth', 'role:1'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::get('/admin/owner-applications/{application}', [OwnerApplicationController::class, 'show'])
        ->name('admin.owner-applications.show');

    Route::post('/admin/owner-applications/{application}/approve', [OwnerApplicationController::class, 'approve'])
        ->name('admin.owner-applications.approve');

    Route::post('/admin/owner-applications/{application}/reject', [OwnerApplicationController::class, 'reject'])
        ->name('admin.owner-applications.reject');
});

#Dashboard for Owner (Homestay Owner)
Route::middleware(['auth', 'role:3'])->group(function () {

    Route::get('/owner/dashboard', [OwnerHomestayController::class, 'dashboard'])
        ->name('owner.dashboard');

    Route::get('/owner/my-homestay', [OwnerHomestayController::class, 'myHomestay'])
        ->name('owner.myHomestay');

    Route::get('/owner/homestays/location-search', [OwnerHomestayController::class, 'locationSearch'])
        ->name('owner.homestays.location.search');

    Route::get('/owner/homestays/location-reverse', [OwnerHomestayController::class, 'locationReverse'])
        ->name('owner.homestays.location.reverse');

    # create homestay
    Route::post('/homestays/store', [OwnerHomestayController::class, 'store'])
        ->name('owner.homestays.store');

    # update homestay
    Route::patch('/homestays/update', [OwnerHomestayController::class, 'update'])
        ->name('owner.homestays.update');

    # delete homestay image
    Route::delete('/homestays/image/{id}',[OwnerHomestayController::class, 'deleteImage'])
        ->name('owner.homestays.image.delete');

    # delete homestay
    Route::delete('/owner/homestays/{id}',[OwnerHomestayController::class, 'destroy'])
    ->name('owner.homestays.destroy');
});

#Dashboard for User (Customer)
Route::middleware(['auth', 'verified', 'role:2'])->group(function () {

    Route::get('/user/dashboard', [UserCustomerController::class, 'dashboard'])
        ->name('user.dashboard');
});
