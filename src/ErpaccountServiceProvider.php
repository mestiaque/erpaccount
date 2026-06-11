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

        // Keep host permission modules first and append package modules at the end.
        $basePermissionModules = config('permission.modules', []);
        $packagePermissionModules = config('erpaccount.permissions.modules', []);

        if (is_array($basePermissionModules) && is_array($packagePermissionModules)) {
            config([
                'permission.modules' => $this->mergeMissingRecursive(
                    $basePermissionModules,
                    $packagePermissionModules,
                ),
            ]);
        }
        
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

    /**
     * Merge only missing keys from $additional into $base recursively.
     * Existing $base values remain unchanged.
     */
    private function mergeMissingRecursive(array $base, array $additional): array
    {
        foreach ($additional as $key => $value) {
            if (!array_key_exists($key, $base)) {
                $base[$key] = $value;
                continue;
            }

            if (is_array($base[$key]) && is_array($value)) {
                $base[$key] = $this->mergeMissingRecursive($base[$key], $value);
            }
        }

        return $base;
    }
}