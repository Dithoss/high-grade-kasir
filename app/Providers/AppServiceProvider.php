<?php

namespace App\Providers;

use App\Contracts\Interface\AuditLogInterface;
use App\Contracts\Interface\AuthInterface;
use App\Contracts\Interface\BookInterface;
use App\Contracts\Interface\CategoryInterface;
use App\Contracts\Interface\FineInterface;
use App\Contracts\Interface\LibraryCardInterface;
use App\Contracts\Interface\PreorderInterface;
use App\Contracts\Interface\TransactionInterface;
use App\Contracts\Interface\WishlistInterface;
use App\Contracts\Repositories\AuditLogRepository;
use App\Contracts\Repositories\AuthRepository;
use App\Contracts\Repositories\BookRepository;
use App\Contracts\Repositories\CategoryRepository;
use App\Contracts\Repositories\FineRepository;
use App\Contracts\Repositories\LibraryCardRepository;
use App\Contracts\Repositories\PreorderRepository;
use App\Contracts\Repositories\TransactionRepository;
use App\Contracts\Repositories\WishlistRepository;
use App\Models\Book;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\BookObserver;
use App\Observers\TransactionObserver;
use App\Observers\UserObserver;
use App\Policies\BookPolicy;
use App\Services\SettingService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
   public function register(): void
    {

        $bindings = [
            AuthInterface::class => AuthRepository::class,
            BookInterface::class => BookRepository::class,
            CategoryInterface::class => CategoryRepository::class,  
            TransactionInterface::class => TransactionRepository::class,
            AuditLogInterface::class => AuditLogRepository::class,
            FineInterface::class => FineRepository::class,
            WishlistInterface::class => WishlistRepository::class,
            PreorderInterface::class => PreorderRepository::class,
            LibraryCardInterface::class => LibraryCardRepository::class,
            ];

        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
            $this->app->singleton(SettingService::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    View::composer('*', function ($view) {
        $view->with('settings', app(SettingService::class));
    });
        Book::observe(BookObserver::class);
        Transaction::observe(TransactionObserver::class);
        User::observe(UserObserver::class);
    }
}
