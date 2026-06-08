<?php
namespace ME\Erpaccount;

use Illuminate\Support\ServiceProvider;

class ErpaccountServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'erpaccount');
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'erpaccount');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->publishes([
            __DIR__.'/Config' => config_path('erpaccount'),
        ], 'erpaccount-config');
    }

    public function register()
    {
        if (file_exists(__DIR__ . '/Config/config.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/config.php', 'erpaccount');
        }

        if (file_exists(__DIR__ . '/Config/sidebar.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/sidebar.php', 'sidebar');
        }

        if (file_exists(__DIR__ . '/Config/permission.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/permission.php', 'erpaccount.permissions');
        }

        if (file_exists(__DIR__ . '/Config/reports.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/reports.php', 'erpaccount.reports');
        }
    }
}