<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;

class AdminCouponController
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
        $usages = $pdo->query("
            SELECT cu.*, c.code, u.username 
            FROM coupon_usage cu
            INNER JOIN coupons c ON c.id = cu.coupon_id
            INNER JOIN users u ON u.id = cu.user_id
            ORDER BY cu.id DESC
            LIMIT 25
        ")->fetchAll();

        View::render('admin/coupons', [
            'title' => 'Coupon & Discount Codes',
            'coupons' => $coupons,
            'usages' => $usages,
        ], 'admin');
    }

    public function store(): void
    {
        $request = new Request();
        $code = strtoupper(trim((string)$request->input('code')));
        $type = (string)$request->input('type', 'fixed');
        $value = (float)$request->input('value', 5.0);
        $maxUses = (int)$request->input('max_uses', 100);
        $expiry = trim((string)$request->input('expiry_date')) ?: null;

        if (empty($code) || $value <= 0) {
            Session::setFlash('error', 'Coupon code and a positive value are required.');
            Response::redirect('/admin/coupons');
        }

        $stmt = Database::pdo()->prepare("
            INSERT INTO coupons (code, type, value, max_uses, expiry_date, status, created_at)
            VALUES (:code, :type, :val, :max, :exp, 'active', NOW())
        ");
        $stmt->execute([
            'code' => $code,
            'type' => $type,
            'val' => $value,
            'max' => $maxUses,
            'exp' => $expiry,
        ]);

        Security::logAudit("Admin created coupon: {$code}");
        Session::setFlash('success', "Coupon '{$code}' created successfully.");
        Response::redirect('/admin/coupons');
    }

    public function delete(int $couponId): void
    {
        Database::pdo()->prepare("DELETE FROM coupons WHERE id = :id")->execute(['id' => $couponId]);
        Security::logAudit("Admin deleted coupon #{$couponId}");
        Session::setFlash('success', "Coupon deleted.");
        Response::redirect('/admin/coupons');
    }
}
