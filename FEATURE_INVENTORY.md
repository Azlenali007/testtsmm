# SMM Panel — Feature Inventory & Implementation Matrix

| Feature/module | Reference evidence | New implementation location | Database tables involved | Backend status | Frontend status | Testing status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **User Registration** | `app/controller/signup.php` | `app/Controllers/AuthController.php::register` | `users`, `user_roles` | Completed | Completed | Tested |
| **User Login & Session** | `app/controller/auth.php` | `app/Controllers/AuthController.php::login` | `users`, `admin_audit_logs` | Completed | Completed | Tested |
| **Password Reset** | `app/controller/resetpassword.php` | `app/Controllers/AuthController.php::resetPassword` | `users` | Completed | Completed | Tested |
| **Email Verification** | `app/controller/confirm_email.php` | `app/Controllers/AuthController.php::verifyEmail` | `users` | Completed | Completed | Tested |
| **Account Profile & Settings** | `app/controller/account.php` | `app/Controllers/AccountController.php` | `users` | Completed | Completed | Tested |
| **API Key Regeneration** | `app/controller/account.php` | `app/Controllers/AccountController.php::generateApiKey` | `users` | Completed | Completed | Tested |
| **Service Categories** | `app/controller/services.php` | `app/Controllers/ServiceController.php` | `categories` | Completed | Completed | Tested |
| **Service Catalog & Search** | `app/controller/services.php` | `app/Controllers/ServiceController.php::index` | `services`, `categories` | Completed | Completed | Tested |
| **Service Details Modal** | `app/controller/ajax_data.php` | `app/Controllers/ServiceController.php::detail` | `services` | Completed | Completed | Tested |
| **Order Placement (Default/Package)** | `app/controller/neworder.php` | `app/Controllers/OrderController.php::store` | `orders`, `services`, `users`, `wallet_transactions` | Completed | Completed | Tested |
| **Custom Comments Order** | `app/controller/neworder.php` | `app/Controllers/OrderController.php::store` | `orders`, `services` | Completed | Completed | Tested |
| **Drip-Feed Order** | `app/controller/dripfeeds.php` | `app/Controllers/OrderController.php::storeDripfeed` | `orders`, `services` | Completed | Completed | Tested |
| **Order History & Status Filters** | `app/controller/orders.php` | `app/Controllers/OrderController.php::index` | `orders`, `services` | Completed | Completed | Tested |
| **Refill Request System** | `app/controller/refill.php` | `app/Controllers/OrderController.php::requestRefill` | `refills`, `orders` | Completed | Completed | Tested |
| **Wallet Balance & Ledger** | `app/controller/addfunds.php` | `app/Controllers/WalletController.php::index` | `users`, `wallet_transactions` | Completed | Completed | Tested |
| **Deposit Initiation (Multi-gateway)** | `app/controller/addfunds.php` | `app/Controllers/PaymentController.php::initiate` | `payments`, `payment_methods` | Completed | Completed | Tested |
| **Payment Webhook Callbacks** | `app/controller/payment.php` | `app/Controllers/PaymentController.php::webhook` | `payments`, `wallet_transactions`, `users` | Completed | Completed | Tested |
| **Manual Bank Transfer Proof** | `app/controller/payment.php` | `app/Controllers/PaymentController.php::submitBankProof` | `payments`, `payment_methods` | Completed | Completed | Tested |
| **Coupons & Promo Codes** | `app/controller/kupon.php` | `app/Controllers/CouponController.php::redeem` | `coupons`, `coupon_usage`, `users` | Completed | Completed | Tested |
| **Affiliate & Referral System** | `app/controller/refer.php` | `app/Controllers/AffiliateController.php` | `referrals`, `referral_payouts`, `users` | Completed | Completed | Tested |
| **Support Tickets & Replies** | `app/controller/tickets.php` | `app/Controllers/TicketController.php` | `tickets`, `ticket_messages` | Completed | Completed | Tested |
| **External SMM v2 Client API** | `app/controller/api.php` | `app/Controllers/ApiController.php` | `orders`, `services`, `users`, `refills` | Completed | Completed | Tested |
| **SMM Provider Management** | `admin/controller/settings.php` | `app/Controllers/Admin/AdminProviderController.php` | `providers`, `services` | Completed | Completed | Tested |
| **Provider Service Auto-Sync** | `crons/providers.php`, `sync.php` | `app/Services/ProviderService.php::syncServices` | `services`, `providers` | Completed | Completed | Tested |
| **Child-Panel Management** | `app/controller/child-panels.php` | `app/Controllers/ChildPanelController.php` | `child_panels`, `users` | Completed | Completed | Tested |
| **System Broadcasts & Popups** | `admin/controller/broadcasts.php` | `app/Controllers/Admin/AdminBroadcastController.php` | `broadcasts` | Completed | Completed | Tested |
| **Admin Overview & Chart Analytics** | `admin/controller/index.php` | `app/Controllers/Admin/AdminDashboardController.php` | `orders`, `users`, `payments`, `providers` | Completed | Completed | Tested |
| **Admin User Management & Balance Adj** | `admin/controller/clients.php` | `app/Controllers/Admin/AdminUserController.php` | `users`, `wallet_transactions` | Completed | Completed | Tested |
| **Admin Category & Service CRUD** | `admin/controller/services.php` | `app/Controllers/Admin/AdminServiceController.php` | `categories`, `services` | Completed | Completed | Tested |
| **Admin Order Actions & Bulk Ops** | `admin/controller/orders.php` | `app/Controllers/Admin/AdminOrderController.php` | `orders`, `users`, `wallet_transactions` | Completed | Completed | Tested |
| **Admin Payment Gateway Settings** | `admin/controller/settings.php` | `app/Controllers/Admin/AdminPaymentController.php` | `payment_methods`, `payments` | Completed | Completed | Tested |
| **Admin Ticket Helpdesk** | `admin/controller/tickets.php` | `app/Controllers/Admin/AdminTicketController.php` | `tickets`, `ticket_messages` | Completed | Completed | Tested |
| **Admin Coupon & Referral Controls** | `admin/controller/kuponlar.php` | `app/Controllers/Admin/AdminCouponController.php` | `coupons`, `referral_payouts` | Completed | Completed | Tested |
| **Website Settings & Theme Selector** | `admin/controller/settings.php` | `app/Controllers/Admin/AdminSettingController.php` | `settings` | Completed | Completed | Tested |
| **Theme System (ThemeManager)** | `app/views/Clementine/` | `app/Core/ThemeManager.php` (`classic` theme) | `settings` | Completed | Completed | Tested |
| **Cron: Order Status Checking** | `crons/orders.php` | `cron/check_orders.php` | `orders`, `users`, `wallet_transactions` | Completed | Completed | Tested |
| **Cron: Service Sync & Pricing** | `crons/sync.php` | `cron/sync_services.php` | `services`, `providers` | Completed | Completed | Tested |
| **Cron: Dripfeed Dispatcher** | `crons/dripfeed.php` | `cron/dripfeed.php` | `orders`, `services` | Completed | Completed | Tested |
| **Cron: Refill Processor** | `crons/refill.php` | `cron/refill.php` | `refills`, `orders` | Completed | Completed | Tested |
| **Cron: Daily Reports** | `crons/average.php` | `cron/daily_reports.php` | `orders`, `payments`, `cron_logs` | Completed | Completed | Tested |
| **Audit Logs & Security Monitoring** | `admin/controller/logs.php` | `app/Controllers/Admin/AdminLogController.php` | `admin_audit_logs` | Completed | Completed | Tested |
