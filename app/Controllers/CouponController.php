<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\CouponService;

class CouponController
{
    public function redeem(): void
    {
        $request = new Request();
        $user = Auth::user();
        $code = trim((string)$request->input('coupon_code'));

        if (empty($code)) {
            if ($request->isAjax()) {
                Response::error('Please enter a coupon code.');
            }
            Session::setFlash('error', 'Please enter a coupon code.');
            Response::redirect('/wallet');
        }

        try {
            $credited = CouponService::redeem((int)$user['id'], $code);

            if ($request->isAjax()) {
                Response::success("Coupon applied! +$" . number_format($credited, 2) . " has been credited to your balance.", [
                    'amount' => $credited,
                ]);
            }

            Session::setFlash('success', "Coupon applied! +$" . number_format($credited, 2) . " has been credited to your wallet balance.");
            Response::redirect('/wallet');
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                Response::error($e->getMessage());
            }

            Session::setFlash('error', $e->getMessage());
            Response::redirect('/wallet');
        }
    }
}
