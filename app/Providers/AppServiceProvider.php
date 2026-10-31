<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Paginator::defaultView('components.pagination');
        Blade::directive('tel', fn (string $numero) => "<?php echo e(\\App\\Support\\Telephone::format({$numero})); ?>");
        Blade::directive('fcfa', fn (string $montant) => "<?php echo e(\\App\\Support\\Money::fcfa({$montant})); ?>");

        // L'administratrice a accès à tout.
        Gate::before(fn (User $user) => $user->isAdmin() ? true : null);

        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, fn (User $user) => $user->hasPermission($permission));
        }
    }
}
