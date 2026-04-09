<?php

namespace FlexWave\Wysiwyg;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use FlexWave\Wysiwyg\View\Components\Editor;
use FlexWave\Wysiwyg\Helpers\WysiwygHelper;

class WysiwygServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/flexwave-wysiwyg.php',
            'flexwave-wysiwyg'
        );

        $this->app->singleton('flexwave-wysiwyg', function ($app) {
            return new WysiwygHelper($app['config']['flexwave-wysiwyg']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'flexwave-wysiwyg');

        $this->registerBladeComponents();
        $this->registerBladeDirectives();
        $this->registerRoutes();
        $this->registerPublishables();
    }

    /**
     * Register Blade components.
     */
    protected function registerBladeComponents(): void
    {
        Blade::component('flexwave-editor', Editor::class);
    }

    /**
     * Register Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('wysiwygStyles', function () {
            $assetUrl = asset('vendor/flexwave-wysiwyg/css/editor.css');
            return "<?php echo '<link rel=\"stylesheet\" href=\"{$assetUrl}\">'; ?>";
        });

        Blade::directive('wysiwygScripts', function () {
            $assetUrl = asset('vendor/flexwave-wysiwyg/js/editor.js');
            return "<?php echo '<script src=\"{$assetUrl}\" defer></script>'; ?>";
        });
    }

    /**
     * Register package routes.
     */
    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Get the route group configuration.
     */
    protected function routeConfiguration(): array
    {
        return [
            'prefix'     => config('flexwave-wysiwyg.route_prefix', 'flexwave'),
            'middleware' => config('flexwave-wysiwyg.middleware', ['web']),
            'as'         => 'flexwave-wysiwyg.',
        ];
    }

    /**
     * Register publishable resources.
     */
    protected function registerPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__ . '/../config/flexwave-wysiwyg.php' => config_path('flexwave-wysiwyg.php'),
            ], 'flexwave-wysiwyg-config');

            // Views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/flexwave-wysiwyg'),
            ], 'flexwave-wysiwyg-views');

            // Assets (JS/CSS)
            $this->publishes([
                __DIR__ . '/../resources/dist' => public_path('vendor/flexwave-wysiwyg'),
            ], 'flexwave-wysiwyg-assets');

            // All at once
            $this->publishes([
                __DIR__ . '/../config/flexwave-wysiwyg.php' => config_path('flexwave-wysiwyg.php'),
                __DIR__ . '/../resources/views'             => resource_path('views/vendor/flexwave-wysiwyg'),
                __DIR__ . '/../resources/dist'              => public_path('vendor/flexwave-wysiwyg'),
            ], 'flexwave-wysiwyg');
        }
    }
}
