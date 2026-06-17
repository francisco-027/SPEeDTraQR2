<?php

namespace App\Providers;

use App\Listeners\LogUserLogin;
use App\Listeners\LogUserLogout;
use App\Models\DepartmentNotification;
use App\Support\Ai\LlmProvider;
use App\Support\Ai\NullProvider;
use App\Support\Ai\OllamaProvider;
use App\Support\DepartmentScope;
use App\Support\DocumentFormOptions;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Provider-agnostic LLM backend for the document assistant (Pillar 3).
        $this->app->singleton(LlmProvider::class, function () {
            return match (config('ai.provider')) {
                'ollama' => new OllamaProvider(
                    config('ai.ollama.url'),
                    config('ai.ollama.model'),
                    config('ai.ollama.timeout'),
                    config('ai.ollama.keep_alive'),
                ),
                default => new NullProvider,
            };
        });
    }

    public function boot(): void
    {
        Event::listen(Login::class, LogUserLogin::class);
        Event::listen(Logout::class, LogUserLogout::class);

        View::composer('layouts.app', function ($view) {
            $notifications = collect();
            $user = auth()->user();

            if ($user && ! DepartmentScope::isOrgWide($user) && $user->department_id
                && Schema::hasTable('department_notifications')) {
                $notifications = DepartmentNotification::query()
                    ->with('document:id,tracking_number,document_type')
                    ->where('department_id', $user->department_id)
                    ->whereNull('read_at')
                    ->latest()
                    ->take(20)
                    ->get();
            }

            $view->with('headerNotifications', $notifications);
        });

        // Feed the New Submission modal (included by the layout) with its form
        // options — only for users who can actually create documents and are
        // not org-wide system admins (who don't submit documents).
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $canCreate = $user && $user->can('create documents') && ! $user->can('manage system');

            $view->with('canCreateDocuments', $canCreate);

            if ($canCreate) {
                $view->with('docFormDepartments', DocumentFormOptions::departments());
                $view->with('docFormDefaultRoutes', DocumentFormOptions::defaultRoutesByType());
                $view->with('docFormCategories', DocumentFormOptions::categoryOptions());
            }
        });
    }
}
