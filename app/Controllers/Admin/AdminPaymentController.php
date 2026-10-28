<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Security;
use App\Services\PaymentService;

class AdminPaymentController
{
    public function index(): void
    {
        $request = new Request();
        $status = strtolower((string)$request->get('status', 'all'));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $pdo = Database::pdo();
        $query = "
            SELECT p.*, u.username, u.email as user_email, pm.name as method_name
            FROM payments p
            INNER JOIN users u ON u.id = p.user_id
            INNER JOIN payment_methods pm ON pm.id = p.payment_method_id
            WHERE 1=1
        ";
        $params = [];

        if ($status !== 'all' && in_array($status, ['pending', 'completed', 'failed', 'canceled'])) {
            $query .= " AND p.status = :status";
            $params['status'] = $status;
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM (" . $query . ") as c");
        $countStmt->execute($params);
        $totalPayments = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($totalPayments / $perPage);

        $query .= " ORDER BY p.id DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $payments = $stmt->fetchAll();

        View::render('admin/payments', [
            'title' => 'Deposit Transactions & Verification',
            'payments' => $payments,
            'currentStatus' => $status,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPayments' => $totalPayments,
        ], 'admin');
    }

    public function approve(int $paymentId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT transaction_code, status FROM payments WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $paymentId]);
        $payment = $stmt->fetch();

        if (!$payment || $payment['status'] !== 'pending') {
            Session::setFlash('error', 'Payment cannot be approved.');
            Response::redirect('/admin/payments');
        }

        PaymentService::completePayment($payment['transaction_code'], [
            'approved_by_admin' => true,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        Security::logAudit("Admin approved payment #{$paymentId} [{$payment['transaction_code']}]");
        Session::setFlash('success', "Payment #{$paymentId} approved and funds credited.");
        Response::redirect('/admin/payments');
    }

    public function reject(int $paymentId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT transaction_code, status FROM payments WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $paymentId]);
        $payment = $stmt->fetch();

        if (!$payment || $payment['status'] !== 'pending') {
            Session::setFlash('error', 'Payment cannot be rejected.');
            Response::redirect('/admin/payments');
        }

        PaymentService::failPayment($payment['transaction_code'], 'Rejected by administrator.');
        Security::logAudit("Admin rejected payment #{$paymentId}");
        Session::setFlash('success', "Payment #{$paymentId} rejected.");
        Response::redirect('/admin/payments');
    }

    public function settings(): void
    {
        $methods = Database::pdo()->query("SELECT * FROM payment_methods ORDER BY id ASC")->fetchAll();
        View::render('admin/payment_settings', [
            'title' => 'Payment Gateway Settings',
            'paymentMethods' => $methods,
        ], 'admin');
    }

    public function updateMethod(int $methodId): void
    {
        $request = new Request();
        $min = (float)$request->input('min_amount', 5.0);
        $max = (float)$request->input('max_amount', 5000.0);
        $fee = (float)$request->input('fee_percentage', 0.0);
        $bonus = (float)$request->input('bonus_percentage', 0.0);
        $isActive = $request->input('is_active') ? 1 : 0;
        $configRaw = $request->input('config', []);

        $stmt = Database::pdo()->prepare("
            UPDATE payment_methods 
            SET min_amount = :min, max_amount = :max, fee_percentage = :fee, 
                bonus_percentage = :bonus, is_active = :active, config_json = :config, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'min' => $min,
            'max' => $max,
            'fee' => $fee,
            'bonus' => $bonus,
            'active' => $isActive,
            'config' => json_encode($configRaw),
            'id' => $methodId,
        ]);

        Security::logAudit("Admin updated payment method #{$methodId}");
        Session::setFlash('success', 'Payment gateway settings updated.');
        Response::redirect('/admin/payments/settings');
    }
}
