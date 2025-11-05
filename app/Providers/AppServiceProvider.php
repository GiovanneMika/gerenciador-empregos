<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Company;
use App\Policies\UserPolicy;
use App\Policies\CompanyPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Auth\MultiModelUserProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
        JsonResource::withoutWrapping();
        
        // Registra o custom user provider para suportar User e Company
        Auth::provider('multi_model', function ($app, array $config) {
            return new MultiModelUserProvider();
        });
    }
}
