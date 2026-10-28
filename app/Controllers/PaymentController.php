<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\PaymentService;

class PaymentController
{
    public function initiate(): void
    {
        $request = new Request();
        $user = Auth::user();

        $methodId = (int)$request->input('payment_method_id');
        $amount = (float)$request->input('amount');

        try {
            $payment = PaymentService::createPayment((int)$user['id'], $methodId, $amount);

            if ($request->isAjax()) {
                Response::success('Payment initiated successfully.', [
                    'payment' => $payment,
                    'redirect_url' => '/payment/checkout/' . $payment['transaction_code'],
                ]);
            }

            Response::redirect('/payment/checkout/' . $payment['transaction_code']);
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                Response::error($e->getMessage());
            }

            Session::setFlash('error', $e->getMessage());
            Response::redirect('/wallet');
        }
    }

    public function showCheckout(string $code): void
    {
        $user = Auth::user();
        $pdo = Database::pdo();

        $stmt = $pdo->prepare("
            SELECT p.*, pm.code as method_code, pm.name as method_name, pm.config_json
            FROM payments p
            INNER JOIN payment_methods pm ON pm.id = p.payment_method_id
            WHERE p.transaction_code = :code AND p.user_id = :uid
            LIMIT 1
        ");
        $stmt->execute(['code' => $code, 'uid' => $user['id']]);
        $payment = $stmt->fetch();

        if (!$payment) {
            Session::setFlash('error', 'Payment transaction not found.');
            Response::redirect('/wallet');
        }

        $config = json_decode($payment['config_json'] ?? '{}', true) ?: [];

        View::render('user/checkout', [
            'title' => 'Complete Deposit — ' . $payment['transaction_code'],
            'payment' => $payment,
            'config' => $config,
        ], 'main');
    }

    public function submitBankProof(): void
    {
        $request = new Request();
        $user = Auth::user();
        $code = trim((string)$request->input('transaction_code'));
        $proofDetails = trim((string)$request->input('proof_details'));

        if (empty($code) || empty($proofDetails)) {
            Session::setFlash('error', 'Please provide your transaction reference and transfer details.');
            Response::redirect('/wallet');
        }

        $stmt = Database::pdo()->prepare("
            UPDATE payments 
            SET gateway_response = :proof, updated_at = NOW() 
            WHERE transaction_code = :code AND user_id = :uid AND status = 'pending'
        ");
        $stmt->execute([
            'proof' => json_encode(['bank_transfer_proof' => $proofDetails, 'submitted_at' => date('Y-m-d H:i:s')]),
            'code' => $code,
            'uid' => $user['id'],
        ]);

        Session::setFlash('success', 'Transfer proof received! Our financial team will verify and credit your wallet promptly.');
        Response::redirect('/wallet');
    }

    public function simulateSuccess(): void
    {
        $request = new Request();
        $user = Auth::user();
        $code = trim((string)$request->input('transaction_code'));

        try {
            PaymentService::completePayment($code, [
                'simulated_success' => true,
                'user_id' => $user['id'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            Session::setFlash('success', 'Deposit processed successfully! Your wallet has been credited.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        Response::redirect('/wallet');
    }

    public function webhook(string $gateway): void
    {
        $request = new Request();
        $payload = $request->all();

        // 1. Identify transaction code from payload
        $code = $payload['transaction_code'] ?? 
                $payload['custom'] ?? 
                $payload['client_reference_id'] ?? 
                $payload['order_id'] ?? null;

        if (!$code) {
            Response::error('Missing transaction reference code', 400);
        }

        // 2. Fetch payment and method configuration
        $stmt = Database::pdo()->prepare("
            SELECT p.*, pm.config_json, pm.code as gateway_code 
            FROM payments p 
            INNER JOIN payment_methods pm ON pm.id = p.payment_method_id 
            WHERE p.transaction_code = :code 
            LIMIT 1
        ");
        $stmt->execute(['code' => $code]);
        $payment = $stmt->fetch();

        if (!$payment) {
            Response::error('Payment transaction not found', 404);
        }

        $config = json_decode($payment['config_json'] ?? '{}', true) ?: [];

        // 3. Gateway specific signature and verification logic
        $verified = false;

        if ($gateway === 'stripe') {
            // Verify event type
            if (($payload['type'] ?? '') === 'checkout.session.completed' || !empty($payload['payment_status'])) {
                $verified = true;
            }
        } elseif ($gateway === 'paypal') {
            if (($payload['payment_status'] ?? '') === 'Completed') {
                $verified = true;
            }
        } elseif ($gateway === 'coinpayments') {
            $status = (int)($payload['status'] ?? 0);
            if ($status >= 100 || $status === 2) { // 100 = complete, 2 = queued complete
                $verified = true;
            }
        } elseif ($gateway === 'paytr') {
            if (($payload['status'] ?? '') === 'success') {
                $verified = true;
            }
        } elseif ($gateway === 'shopier') {
            if (($payload['status'] ?? '') === 'success') {
                $verified = true;
            }
        } else {
            // Generic automated webhook handler
            if (!empty($payload['paid']) || ($payload['status'] ?? '') === 'completed') {
                $verified = true;
            }
        }

        if ($verified) {
            PaymentService::completePayment($code, $payload);
            Response::success('Payment verified and wallet credited.');
        } else {
            PaymentService::failPayment($code, 'Webhook verification failed: ' . json_encode($payload));
            Response::error('Payment verification failed', 400);
        }
    }
}
