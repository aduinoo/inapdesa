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
use App\Http\Controllers\BookingChatController;
use App\Http\Controllers\BookingCancellationController;
use App\Http\Controllers\NeighbourhoodReportController;
use App\Http\Controllers\RoomScanController;
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

Route::get('/help-center', [PagesController::class, 'helpCenter'])
    ->name('help-center');

Route::get('/cancellation-options', [BookingCancellationController::class, 'publicIndex'])
    ->name('cancellation-options');

Route::get('/safety-issues', [PagesController::class, 'safetyIssues'])
    ->name('safety-issues');

Route::get('/report-neighbourhood-concerns', [NeighbourhoodReportController::class, 'publicIndex'])
    ->name('neighbourhood-concerns');

Route::get('/list-your-property', [PagesController::class, 'listProperty'])
    ->name('list-property');

Route::get('/hosting-guide', [PagesController::class, 'hostingGuide'])
    ->name('hosting-guide');

Route::get('/community-forum', [PagesController::class, 'communityForum'])
    ->name('community-forum');

Route::get('/privacy', [PagesController::class, 'privacy'])
    ->name('privacy');

Route::get('/terms', [PagesController::class, 'terms'])
    ->name('terms');

Route::get('/homes/{homestay}', [UserCustomerController::class, 'showHomestay'])
    ->name('user.homestays.show');

Route::get('/homes/{homestay}/panorama/{scan}', [UserCustomerController::class, 'panorama'])
    ->whereNumber('scan')
    ->name('user.homestays.panorama');

Route::post('/payment/toyyibpay/callback', [UserCustomerController::class, 'toyyibpayCallback'])
    ->name('payment.toyyibpay.callback');

Route::post('/set-location', [AttractionController::class, 'setLocation']);




#Dashboard Redirection Based on Role
Route::middleware('auth')->get('/dashboard', function () {

    $user = Auth::user();

    if ((int) $user->role === 1) {
        return redirect()->route('admin.dashboard');
    }

    if ((int) $user->role === 3) {
        return redirect()->route('owner.dashboard');
    }

    if ((int) $user->role === 2) {
        return $user->hasVerifiedEmail()
            ? redirect()->route('user.dashboard')
            : redirect()->route('verification.notice');
    }

    return redirect()->route('home-page');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/owner-applications', [OwnerApplicationController::class, 'store'])
        ->name('owner-applications.store');
    Route::post('/notifications/mark-read', [App\Http\Controllers\NotificationController::class, 'markAllRead'])
        ->name('notifications.mark-read');
});

#Dashboard for Admin
Route::middleware(['auth', 'role:1'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::get('/admin/owner-applications', [OwnerApplicationController::class, 'index'])
        ->name('admin.owner-applications.index');

    Route::get('/admin/owner-applications/{application}', [OwnerApplicationController::class, 'show'])
        ->name('admin.owner-applications.show');

    Route::post('/admin/owner-applications/{application}/approve', [OwnerApplicationController::class, 'approve'])
        ->name('admin.owner-applications.approve');

    Route::post('/admin/owner-applications/{application}/reject', [OwnerApplicationController::class, 'reject'])
        ->name('admin.owner-applications.reject');

    Route::get('/admin/reports', [NeighbourhoodReportController::class, 'adminIndex'])
        ->name('admin.reports.index');

    Route::get('/admin/reports/{report}', [NeighbourhoodReportController::class, 'adminShow'])
        ->name('admin.reports.show');

    Route::post('/admin/reports/{report}/messages', [NeighbourhoodReportController::class, 'storeAdminMessage'])
        ->name('admin.reports.messages.store');
});

#Dashboard for Owner (Homestay Owner)
Route::middleware(['auth', 'role:3'])->group(function () {

    Route::get('/owner/dashboard', [OwnerHomestayController::class, 'dashboard'])
        ->name('owner.dashboard');

    Route::get('/owner/my-homestay', [OwnerHomestayController::class, 'myHomestay'])
        ->name('owner.myHomestay');

    Route::get('/owner/room-scans', [RoomScanController::class, 'index'])
        ->name('owner.room-scans.index');

    Route::post('/owner/room-scans', [RoomScanController::class, 'store'])
        ->name('owner.room-scans.store');

    Route::get('/owner/room-scans/{roomScan}', [RoomScanController::class, 'show'])
        ->name('owner.room-scans.show');

    Route::get('/owner/room-scans/{roomScan}/photos/{photoIndex}', [RoomScanController::class, 'photo'])
        ->whereNumber('photoIndex')
        ->name('owner.room-scans.photo');

    Route::get('/owner/room-scans/{roomScan}/overview-video', [RoomScanController::class, 'video'])
        ->name('owner.room-scans.video');

    Route::get('/owner/messages', [BookingChatController::class, 'ownerIndex'])
        ->name('owner.messages.index');

    Route::get('/owner/messages/{booking}', [BookingChatController::class, 'showForOwner'])
        ->name('owner.messages.show');

    Route::post('/owner/messages/{booking}', [BookingChatController::class, 'storeForOwner'])
        ->name('owner.messages.store');

    Route::get('/owner/cancellation-requests', [BookingCancellationController::class, 'ownerIndex'])
        ->name('owner.cancellation-requests.index');

    Route::get('/owner/cancellation-requests/{request}', [BookingCancellationController::class, 'ownerShow'])
        ->name('owner.cancellation-requests.show');

    Route::post('/owner/cancellation-requests/{request}/approve', [BookingCancellationController::class, 'approve'])
        ->name('owner.cancellation-requests.approve');

    Route::post('/owner/cancellation-requests/{request}/reject', [BookingCancellationController::class, 'reject'])
        ->name('owner.cancellation-requests.reject');

    Route::get('/owner/reports', [NeighbourhoodReportController::class, 'ownerIndex'])
        ->name('owner.reports.index');

    Route::get('/owner/reports/{report}', [NeighbourhoodReportController::class, 'ownerShow'])
        ->name('owner.reports.show');

    Route::post('/owner/reports/{report}/messages', [NeighbourhoodReportController::class, 'storeOwnerMessage'])
        ->name('owner.reports.messages.store');

    Route::post('/owner/reports/{report}/settle', [NeighbourhoodReportController::class, 'settle'])
        ->name('owner.reports.settle');

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

#Dashboard for User (Customer + Owner booking as guest)
Route::middleware(['auth', 'verified', 'role:2,3'])->group(function () {

    Route::get('/user/dashboard', [UserCustomerController::class, 'dashboard'])
        ->name('user.dashboard');

    Route::get('/user/bookings', [UserCustomerController::class, 'bookings'])
        ->name('user.bookings.index');

    Route::get('/user/wishlist', [UserCustomerController::class, 'wishlist'])
        ->name('user.wishlist.index');

    Route::post('/user/wishlist/{homestay}/toggle', [UserCustomerController::class, 'toggleWishlist'])
        ->name('user.wishlist.toggle');

    Route::post('/user/homestays/{homestay}/book', [UserCustomerController::class, 'prepareBooking'])
        ->name('user.homestays.book');

    Route::get('/user/bookings/payment', [UserCustomerController::class, 'payment'])
        ->name('user.bookings.payment');

    Route::post('/user/bookings/process-payment', [UserCustomerController::class, 'processPayment'])
        ->name('user.bookings.process-payment');

    Route::get('/user/bookings/{booking}/confirmed', [UserCustomerController::class, 'confirmed'])
        ->name('user.bookings.confirmed');

    Route::get('/payment/toyyibpay/return', [UserCustomerController::class, 'toyyibpayReturn'])
        ->name('payment.toyyibpay.return');

    Route::get('/user/messages', [BookingChatController::class, 'customerIndex'])
        ->name('user.messages.index');

    Route::get('/user/messages/{booking}', [BookingChatController::class, 'showForCustomer'])
        ->name('user.messages.show');

    Route::post('/user/messages/{booking}', [BookingChatController::class, 'storeForCustomer'])
        ->name('user.messages.store');

    Route::get('/user/cancellation-options', [BookingCancellationController::class, 'customerIndex'])
        ->name('user.cancellation-options.index');

    Route::post('/user/bookings/{booking}/cancellation-requests', [BookingCancellationController::class, 'store'])
        ->name('user.cancellation-requests.store');

    Route::get('/user/report-neighbourhood-concerns', [NeighbourhoodReportController::class, 'customerIndex'])
        ->name('user.reports.index');

    Route::post('/user/bookings/{booking}/reports', [NeighbourhoodReportController::class, 'store'])
        ->name('user.reports.store');
});
