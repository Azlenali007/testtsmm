<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ThemeManager;
use App\Core\View;
use App\Helpers\Security;

class AdminSettingController
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $rawSettings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        $settings = [];
        foreach ($rawSettings as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $installedThemes = ThemeManager::getInstalledThemes();

        View::render('admin/settings', [
            'title' => 'System Settings & Theme Manager',
            'settings' => $settings,
            'installedThemes' => $installedThemes,
            'activeTheme' => ThemeManager::getActiveTheme(),
        ], 'admin');
    }

    public function update(): void
    {
        $request = new Request();
        $pdo = Database::pdo();

        $keys = [
            'site_name',
            'site_title',
            'site_description',
            'site_keywords',
            'site_currency',
            'currency_symbol',
            'maintenance_mode',
            'register_enabled',
            'referral_commission_rate',
            'referral_min_payout',
            'child_panel_price',
            'contact_email',
            'terms_content',
            'privacy_content',
        ];

        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES (:k, :v)
            ON DUPLICATE KEY UPDATE setting_value = :v_up
        ");

        foreach ($keys as $key) {
            $val = $request->input($key, '');
            if ($key === 'maintenance_mode' || $key === 'register_enabled') {
                $val = $request->input($key) ? '1' : '0';
            }
            $stmt->execute(['k' => $key, 'v' => $val, 'v_up' => $val]);
        }

        // Handle Theme Switcher
        $selectedTheme = trim((string)$request->input('active_theme'));
        if (!empty($selectedTheme) && ThemeManager::themeExists($selectedTheme)) {
            ThemeManager::setActiveTheme($selectedTheme);
        }

        Security::logAudit("Admin updated system settings and theme to: {$selectedTheme}");
        Session::setFlash('success', 'System settings and active theme updated successfully.');
        Response::redirect('/admin/settings');
    }
}
