<?php

namespace App\Providers;

use App\Contracts\Interface\AuditLogInterface;
use App\Contracts\Interface\AuthInterface;
use App\Contracts\Interface\BookInterface;
use App\Contracts\Interface\CategoryInterface;
use App\Contracts\Interface\FineInterface;
use App\Contracts\Interface\PreorderInterface;
use App\Contracts\Interface\TransactionInterface;
use App\Contracts\Interface\WishlistInterface;
use App\Contracts\Repositories\AuditLogRepository;
use App\Contracts\Repositories\AuthRepository;
use App\Contracts\Repositories\BookRepository;
use App\Contracts\Repositories\CategoryRepository;
use App\Contracts\Repositories\FineRepository;
use App\Contracts\Repositories\PreorderRepository;
use App\Contracts\Repositories\TransactionRepository;
use App\Contracts\Repositories\WishlistRepository;
use App\Models\Book;
use App\Models\Transaction;
use App\Observers\BookObserver;
use App\Observers\TransactionObserver;
use App\Policies\BookPolicy;
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
            ];

        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Book::observe(BookObserver::class);
        Transaction::observe(TransactionObserver::class);
    }
}
