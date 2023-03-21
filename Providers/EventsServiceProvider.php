<?php

namespace Modules\Events\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Event;

use App\Events\SeedEvent;
use Modules\Events\Listeners\SeedEventListener;
use Modules\Events\Managers\EventsManager;
use Modules\Events\Entities\EventEvents;
use Modules\Events\Observers\EventsObserver;

class EventsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFactories();
        $this->app->singleton('modules.events', function ($app) {
            return new EventsManager($app);
        });
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    private function registerFactories()
    {
        if (!app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadObservers();
        $this->loadResources();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerEvents();
        $this->registerConfig();
        $this->rewriteConfig();
        $this->registerCommands();
        $this->registerNotificationTypes();
    }

    /**
     * Load observers.
     *
     * @return void
     */
    private function loadObservers()
    {
        EventEvents::observe(EventsObserver::class);
    }

    /**
     * Load resources.
     *
     * @return void
     */
    private function loadResources()
    {
        $this->registerTranslations();
        $this->registerViews();
    }

    /**
     * Register views.
     *
     * @return void
     */
    private function registerViews()
    {
        $viewPath = resource_path('views/modules/events');

        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/events';
        }, \Config::get('view.paths')), [$sourcePath]), 'events');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    private function registerTranslations()
    {
        $langPath = resource_path('lang/modules/events');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'events');
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'events');
        }
    }

    /**
     * Register events.
     *
     * @return void
     */
    private function registerEvents()
    {
        Event::listen(SeedEvent::class, SeedEventListener::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    private function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('events.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php', 'events'
        );
    }

    /**
     * Rewrite config.
     *
     * @return void
     */
    private function rewriteConfig()
    {
        if (isset($this->app['config']['l5-swagger']) && $config_l5_swagger = $this->app['config']['l5-swagger']) {
            $config_l5_swagger['documentations']['v1']['paths']['annotations'] = array_merge($config_l5_swagger['documentations']['v1']['paths']['annotations'], [
                __DIR__ . '/../Http/Controllers/Api', __DIR__ . '/../Entities'
            ]);
            $this->app['config']['l5-swagger'] = $config_l5_swagger;
        }
    }

    /**
     * Register commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->commands([
            \Modules\Events\Commands\PublicEvents::class,
            \Modules\Events\Commands\EventSoonStart::class
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('public:events')->withoutOverlapping()->everyMinute();
            $schedule->command('reminder:events')->withoutOverlapping()->everyTenMinutes();
        });
    }

    /**
     * Add notifications types to global config.
     *
     * @return void
     */
    private function registerNotificationTypes()
    {
        $notifications_config = $this->app['config']['notifications'];
        $module_notifications = config('events.notifications');
        if ($module_notifications) {
            foreach ($module_notifications as $_type => $category) {
                if (isset($notifications_config['types'][$_type])) {
                    $notifications_config['types'][$_type] = array_merge($notifications_config['types'][$_type], $category);
                } else {
                    $notifications_config['types'][$_type] = $category;
                }
            }
            $this->app['config']['notifications'] = $notifications_config;
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}