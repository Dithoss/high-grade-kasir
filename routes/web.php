<?php

use App\Enums\UserRole;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\LibraryCardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PreorderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TripayCallbackController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| AUTH — GUEST ONLY
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['guest', 'registration.open'])->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'updateForgotPassword'])->name('password.update');

Route::resource('products', ProductController::class)->except('show');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USERS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'maintenance'])->group(function () {

    Route::get('/library-card/json', [LibraryCardController::class, 'myCardJson'])
        ->name('library-card.json');

    Route::get('/books/barcode-lookup', [BookController::class, 'barcodeLookup'])
        ->name('books.barcode-lookup');

    Route::post('fines/{fine}/pay', [FineController::class, 'pay'])
        ->name('fines.pay');

    Route::get('/books/catalog', [BookController::class, 'catalog'])->name('books.catalog');

    Route::get('/payment/success', [CheckoutController::class, 'success'])
        ->name('stripe.success');

    Route::get('/payment/cancel', [CheckoutController::class, 'cancel'])
        ->name('stripe.cancel');

    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unread-count');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])
        ->name('notifications.recent');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/users/dashboard', [AuthController::class, 'userDashboard'])->name('users.dashboard');

    Route::get('books', [BookController::class, 'index'])->name('books.index');

    Route::post('/payment/checkout', [PaymentController::class, 'checkout']);

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{bookId}/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    Route::get('transactions/history', [TransactionController::class, 'history'])
        ->name('transactions.history');
    Route::get('transactions/history/export', [TransactionController::class, 'exportHistory'])
        ->name('transactions.history.export');
    Route::get('transactions/create', [TransactionController::class, 'create'])
        ->name('transactions.create');
    Route::get('transactions-trash', [TransactionController::class, 'trash'])
        ->name('transactions.trash');
    Route::get('transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');
    Route::post('transactions', [TransactionController::class, 'store'])
        ->name('transactions.store');
    Route::get('transactions/{id}/receipt', [TransactionController::class, 'receipt'])
        ->name('transactions.receipt');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])
        ->name('transactions.show');
    Route::get('transactions/{transaction}/edit', [TransactionController::class, 'edit'])
        ->name('transactions.edit');
    Route::put('transactions/{transaction}', [TransactionController::class, 'update'])
        ->name('transactions.update');
    Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])
        ->name('transactions.destroy');

    /*
    |--------------------------------------------------------------------------
    | Library Card — Member
    |--------------------------------------------------------------------------
    */
    Route::get('/library-card', [LibraryCardController::class, 'show'])
        ->name('library-card.show');
    Route::post('/library-card/photo', [LibraryCardController::class, 'updatePhoto'])
        ->name('library-card.update-photo');

    /*
    |--------------------------------------------------------------------------
    | ROLE: ADMIN ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:' . UserRole::ADMIN->value])->group(function () {

        Route::get('admin/settings', [SystemSettingController::class, 'index'])
            ->name('settings.index');
        Route::put('admin/settings', [SystemSettingController::class, 'update'])
            ->name('settings.update');

        Route::get('/admin/preorders', [PreorderController::class, 'adminIndex'])
            ->name('admin.preorders.index');
        Route::patch('/admin/preorders/{id}/mark-ready', [PreorderController::class, 'adminMarkReady'])
            ->name('admin.preorders.markReady');
        Route::post('/admin/preorders/{id}/notify', [PreorderController::class, 'adminNotify'])
            ->name('admin.preorders.notify');
        Route::delete('/admin/preorders/{id}', [PreorderController::class, 'adminCancel'])
            ->name('admin.preorders.cancel');
        Route::get('/admin/preorders/{id}', [PreorderController::class, 'adminShow'])
            ->name('admin.preorders.show');

        /*
        |----------------------------------------------------------------------
        | Books — BULK & STATIC routes HARUS di atas {book} wildcard
        |----------------------------------------------------------------------
        */
        Route::delete('books/mass-delete', [BookController::class, 'massDelete'])
            ->name('books.mass-delete');
        Route::post('books/mass-restore', [BookController::class, 'massRestore'])
            ->name('books.mass-restore');
        Route::delete('books/mass-force-delete', [BookController::class, 'massForceDelete'])
            ->name('books.mass-force-delete');
        Route::delete('books/trash/empty', [BookController::class, 'emptyTrash'])
            ->name('books.empty-trash');
        Route::get('books-trash', [BookController::class, 'trash'])
            ->name('books.trash');
        Route::get('books/create', [BookController::class, 'create'])
            ->name('books.create');
        Route::post('books', [BookController::class, 'store'])
            ->name('books.store');

        /*
        | {book} wildcard routes — selalu di bawah static routes
        */
        Route::get('books/{book}/edit', [BookController::class, 'edit'])
            ->name('books.edit');
        Route::put('books/{book}', [BookController::class, 'update'])
            ->name('books.update');
        Route::delete('books/{book}', [BookController::class, 'destroy'])
            ->name('books.destroy');
        Route::put('books/{id}/restore', [BookController::class, 'restore'])
            ->name('books.restore');
        Route::delete('books/{book}/force-delete', [BookController::class, 'forceDelete'])
            ->name('books.force-delete');

        Route::resource('categories', CategoryController::class);

        Route::delete('/categories/mass-delete', [CategoryController::class, 'massDelete'])
            ->name('categories.mass-delete');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->name('audit.index');

        Route::post('transactions/admin', [TransactionController::class, 'storeAdmin'])
            ->name('transactions.store.admin');
        Route::put('transactions/{id}/restore', [TransactionController::class, 'restore'])
            ->name('transactions.restore');
        Route::post('transactions/{transaction}/confirm-return', [TransactionController::class, 'confirmReturn'])
            ->name('confirm-return');
        Route::get('transactions/{transaction}/inspect', [TransactionController::class, 'inspect'])
            ->name('transactions.inspect');
        Route::post('transactions/{transaction}/inspect', [TransactionController::class, 'inspectStore'])
            ->name('transactions.inspect.store');
        Route::delete('transactions/{id}/force-delete', [TransactionController::class, 'forceDelete'])
            ->name('transactions.force-delete');
        Route::post('transactions/{id}/approve-extend', [TransactionController::class, 'approveExtend'])
            ->name('transactions.approve-extend');
        Route::post('transactions/{id}/approve', [TransactionController::class, 'approveTransaction'])
            ->name('transactions.approve')
            ->middleware('role:admin');
        Route::post('transactions/{id}/reject', [TransactionController::class, 'rejectTransaction'])
            ->name('transactions.reject')
            ->middleware('role:admin');

        Route::get('users', [AuthController::class, 'index'])->name('users.index');
        Route::get('users/create', [AuthController::class, 'create'])->name('users.create');
        Route::post('users', [AuthController::class, 'store'])->name('users.store');
        Route::delete('users/{user}', [AuthController::class, 'destroy'])->name('users.destroy');

        Route::get('admin/fines', [FineController::class, 'adminIndex'])->name('admin.fines.index');
        Route::post('fines/{fine}/paid', [FineController::class, 'markPaid'])->name('fines.paid');
        Route::post('fines/{fine}/confirm', [FineController::class, 'confirmPayment'])->name('fines.confirm');
        Route::post('fines/{fine}/reject', [FineController::class, 'rejectPayment'])->name('fines.reject');

        /*
        |----------------------------------------------------------------------
        | Library Card — Admin
        | PENTING: by-user/{userId} HARUS di atas {id}
        |----------------------------------------------------------------------
        */
        Route::prefix('admin/library-cards')->name('admin.library-cards.')->group(function () {
            Route::get('/',                 [LibraryCardController::class, 'index'])        ->name('index');
            Route::get('/by-user/{userId}', [LibraryCardController::class, 'byUser'])       ->name('by-user');
            Route::get('/{id}',             [LibraryCardController::class, 'detail'])       ->name('detail');
            Route::patch('/{id}/status',    [LibraryCardController::class, 'updateStatus']) ->name('update-status');
            Route::post('/{id}/regenerate', [LibraryCardController::class, 'regenerate'])   ->name('regenerate');
        });
    });

    Route::get('books/{book:slug}', [BookController::class, 'show'])->name('books.show');

    Route::get('users/{user}', [AuthController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [AuthController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [AuthController::class, 'update'])->name('users.update');

    /*
    |--------------------------------------------------------------------------
    | ROLE: USER ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:' . UserRole::USER->value, 'preorder.throttle'])->group(function () {
        Route::post('{id}/request-return', [TransactionController::class, 'requestReturn'])
            ->name('request-return');
        Route::get('/my-fines', [FineController::class, 'index'])->name('fines.index');
        Route::get('/books/{book}/related', [BookController::class, 'related'])->name('books.related');
        Route::post('transactions/{id}/request-extend', [TransactionController::class, 'requestExtend'])
            ->name('transactions.request-extend');
        Route::get('/preorders', [PreorderController::class, 'index'])->name('preorders.index');
        Route::post('/preorders', [PreorderController::class, 'store'])->name('preorders.store');
        Route::put('/preorders/{id}', [PreorderController::class, 'update'])->name('preorders.update');
        Route::delete('/preorders/{id}', [PreorderController::class, 'cancel'])->name('preorders.cancel');
        Route::get('/preorders/{id}/confirm', [PreorderController::class, 'confirm'])->name('preorders.confirm');
    });

    //php artisan route:clear
    //php artisan cache:clear
    //php artisan config:clear
    //php artisan view:clear
});