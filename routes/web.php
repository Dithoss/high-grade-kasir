<?php

use App\Enums\UserRole;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TripayCallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| AUTH (SESSION BASED)
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'updateForgotPassword'])->name('password.update');
Route::resource('products', ProductController::class)->except('show');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USERS (Both Admin & User)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
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
    
    // Delete notification
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/users/dashboard', [AuthController::class, 'userDashboard'])->name('users.dashboard');

    Route::get('books', [BookController::class, 'index'])->name('books.index');
    Route::get('books/{book:slug}', [BookController::class, 'show'])->name('books.show');


    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('tripay/callback', [TripayCallbackController::class, 'handle']);
    Route::post('/payment/checkout', [PaymentController::class, 'checkout']);

    
    /*
    |--------------------------------------------------------------------------
    | ROLE: ADMIN ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:' . UserRole::ADMIN->value])->group(function () {
        Route::delete('/categories/mass-delete', [CategoryController::class, 'massDelete'])
            ->name('categories.mass-delete');
        Route::delete('/books/mass-delete', [BookController::class, 'massDelete'])
            ->name('books.mass-delete');
        Route::get('books/create', [BookController::class, 'create'])->name('books.create');
        Route::post('books', [BookController::class, 'store'])->name('books.store');
        Route::get('books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
        Route::put('books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
        
        Route::get('books-trash', [BookController::class, 'trash'])->name('books.trash');
        Route::put('books/{id}/restore', [BookController::class, 'restore'])->name('books.restore');
        
        Route::resource('categories', CategoryController::class);
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
        
        Route::post('transactions/admin', [TransactionController::class, 'storeAdmin'])->name('transactions.store.admin');
        Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
        
        // Transaction Trash & Restore
        Route::get('transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::get('transactions-trash', [TransactionController::class, 'trash'])->name('transactions.trash');
        Route::put('transactions/{id}/restore', [TransactionController::class, 'restore'])->name('transactions.restore');
        Route::post('transactions/{transaction}/confirm-return', [TransactionController::class, 'confirmReturn'])->name('confirm-return');
        Route::get('transactions/{transaction}/inspect', [TransactionController::class, 'inspect'])->name('transactions.inspect');
        Route::post('transactions/{transaction}/inspect', [TransactionController::class, 'inspectStore'])->name('transactions.inspect.store');
        Route::delete('transactions/{id}/force-delete', [TransactionController::class, 'forceDelete'])->name('transactions.force-delete');

         Route::get('users', [AuthController::class, 'index'])
        ->name('users.index');

        Route::get('users/create', [AuthController::class, 'create'])
            ->name('users.create');

        Route::post('users', [AuthController::class, 'store'])
            ->name('users.store');



        Route::delete('users/{user}', [AuthController::class, 'destroy'])
            ->name('users.destroy');
        Route::get('admin/fines', [FineController::class, 'adminIndex'])
            ->name('admin.fines.index');

        Route::post('fines/{fine}/paid', [FineController::class, 'markPaid'])
            ->name('fines.paid');
        Route::post('fines/{fine}/confirm', [FineController::class, 'confirmPayment'])
            ->name('fines.confirm');
        Route::post('fines/{fine}/reject', [FineController::class, 'rejectPayment'])
            ->name('fines.reject');
    });
    
    Route::get('users/{user}', [AuthController::class, 'show'])
        ->name('users.show');
    Route::get('users/{user}/edit', [AuthController::class, 'edit'])
        ->name('users.edit');
    Route::put('users/{user}', [AuthController::class, 'update'])
            ->name('users.update');


    /*
    |--------------------------------------------------------------------------
    | ROLE: USER
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:' . UserRole::USER->value])->group(function () {
        Route::post('{id}/request-return',[TransactionController::class, 'requestReturn'])->name('request-return');
         Route::get('/my-fines', [FineController::class, 'index'])
        ->name('fines.index');
        Route::post('fines/{fine}/pay', [FineController::class, 'pay'])
        ->name('fines.pay');
        Route::get('/books/{book}/related', [BookController::class, 'related'])
        ->name('books.related');
    });
});