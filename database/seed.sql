-- ==========================================================
-- SMM Panel Production Seed Data
-- ==========================================================

-- 1. Insert Roles
INSERT INTO `roles` (`id`, `name`, `description`, `permissions`) VALUES
(1, 'super_admin', 'Full system access and administration', '["all"]'),
(2, 'admin', 'Administrative privileges for services and orders', '["users.view","services.manage","orders.manage","tickets.manage"]'),
(3, 'support', 'Helpdesk support staff', '["tickets.manage"]'),
(4, 'user', 'Regular platform client', '["orders.create","tickets.create","wallet.deposit"]');

-- 2. Insert Super Admin User (Password: Password123!)
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `name`, `phone`, `balance`, `spent`, `custom_rate`, `status`, `email_verified_at`, `timezone`, `apikey`) VALUES
(1, 'admin', 'admin@smmpanel.local', '$2y$10$2/rpkQT8d4TQkPRMIJ50EOV1hVoa3rJJ.MHVKDtz5v1AaEDaSfcUO', 'Super Administrator', '+1234567890', 5000.0000, 0.0000, 0.00, 'active', NOW(), 'UTC', 'adm_7e8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d'),
(2, 'demo_user', 'user@smmpanel.local', '$2y$10$2/rpkQT8d4TQkPRMIJ50EOV1hVoa3rJJ.MHVKDtz5v1AaEDaSfcUO', 'Demo Client', '+1987654321', 100.0000, 45.5000, 0.00, 'active', NOW(), 'UTC', 'usr_1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d');

-- 3. Assign Roles
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 4);

-- 4. Insert Default SMM API Provider
INSERT INTO `providers` (`id`, `name`, `api_url`, `api_key`, `balance`, `currency`, `sync_status`, `is_active`) VALUES
(1, 'Global SMM Main API', 'https://api.globalsmmdemo.com/v2', 'demo_api_key_globalsmm_secure_token', 450.7500, 'USD', 'Active', 1);

-- 5. Insert Categories
INSERT INTO `categories` (`id`, `name`, `icon`, `sort_order`, `status`) VALUES
(1, 'Instagram Followers & Likes', 'instagram', 1, 'active'),
(2, 'YouTube Views & Subscribers', 'youtube', 2, 'active'),
(3, 'TikTok Growth & Engagement', 'tiktok', 3, 'active'),
(4, 'Twitter / X Engagements', 'twitter', 4, 'active'),
(5, 'Telegram Members & Views', 'telegram', 5, 'active'),
(6, 'Facebook Followers & Likes', 'facebook', 6, 'active'),
(7, 'Spotify Streams & Plays', 'spotify', 7, 'active'),
(8, 'LinkedIn Connections & Endorsements', 'linkedin', 8, 'active');

-- 6. Insert Services
INSERT INTO `services` (`id`, `category_id`, `provider_id`, `provider_service_id`, `name`, `description`, `type`, `price`, `min_quantity`, `max_quantity`, `dripfeed_enabled`, `refill_enabled`, `refill_days`, `sort_order`, `status`) VALUES
(1, 1, 1, '101', 'Instagram Followers [High Quality - Non-Drop] [30 Days Refill]', '⚡ Start Time: 0-15 Minutes\n⚡ Speed: 5K - 10K / Day\n⚡ Quality: High Quality Profiles with profile pictures and posts\n⚡ Guarantee: 30 Days Auto-Refill Guarantee\n⚡ Link Format: Full profile link (e.g., https://instagram.com/username)', 'default', 1.2500, 50, 50000, 1, 1, 30, 1, 'active'),
(2, 1, 1, '102', 'Instagram Likes [Instant - Real Active - Max 100K]', '⚡ Start Time: Instant (0-5 min)\n⚡ Speed: Super Fast 20K/Day\n⚡ Quality: Real Looking Accounts\n⚡ Drop Rate: 0-2% Extremely Low\n⚡ Link Format: Post URL (https://instagram.com/p/...)', 'default', 0.4500, 20, 100000, 1, 0, 0, 2, 'active'),
(3, 1, 1, '103', 'Instagram Custom Comments [Random Positive or Custom]', '⚡ Start: 0-30 min\n⚡ Custom input: One comment per line\n⚡ Quality: Verified looking accounts\n⚡ Link format: Post URL', 'custom_comments', 3.5000, 10, 2000, 0, 0, 0, 3, 'active'),
(4, 2, 1, '201', 'YouTube Views [High Retention - Monetizable - Lifetime Guarantee]', '⚡ Start Time: 1-3 Hours\n⚡ Retention: 2 to 5+ Minutes\n⚡ Real Engagement: Helps ranking on search results\n⚡ Monetization Safe: 100% AdSense Safe\n⚡ Link: YouTube Video URL', 'default', 2.8000, 500, 1000000, 1, 1, 365, 4, 'active'),
(5, 2, 1, '202', 'YouTube Subscribers [Non-Drop - Real - Safe]', '⚡ Start Time: 2-6 Hours\n⚡ Speed: 50-100 / Day (Safe Natural Speed)\n⚡ Quality: Real Worldwide Accounts\n⚡ Guarantee: 60 Days Refill Guarantee\n⚡ Link: YouTube Channel URL', 'default', 18.5000, 50, 10000, 1, 1, 60, 5, 'active'),
(6, 3, 1, '301', 'TikTok Followers [Organic Growth - Instant]', '⚡ Start Time: 0-10 Minutes\n⚡ Speed: 10K / Day\n⚡ Quality: Clean Profiles\n⚡ Drop: Non-Drop\n⚡ Link: TikTok Profile URL (https://tiktok.com/@username)', 'default', 2.1000, 100, 50000, 1, 1, 30, 6, 'active'),
(7, 3, 1, '302', 'TikTok Likes [Instant High Speed]', '⚡ Start Time: Instant\n⚡ Speed: 50K / Day\n⚡ Link: TikTok Video URL', 'default', 0.6500, 50, 100000, 1, 0, 0, 7, 'active'),
(8, 4, 1, '401', 'Twitter / X Followers [Worldwide - Clean]', '⚡ Start: 0-1 Hour\n⚡ Quality: Global Real Users\n⚡ Link: Profile URL (https://x.com/username)', 'default', 4.2000, 50, 25000, 1, 1, 30, 8, 'active'),
(9, 5, 1, '501', 'Telegram Channel Members [Zero Drop - Non-Revocable]', '⚡ Start: Instant\n⚡ Speed: 15K / Day\n⚡ Link: Public Channel Link (https://t.me/channel)', 'default', 1.8000, 100, 50000, 1, 1, 60, 9, 'active'),
(10, 6, 1, '601', 'Facebook Page Likes + Followers [Real Looking]', '⚡ Start: 1-2 Hours\n⚡ Speed: 2K / Day\n⚡ Link: Facebook Page URL', 'default', 3.2000, 100, 20000, 1, 1, 30, 10, 'active');

-- 7. Insert Payment Methods
INSERT INTO `payment_methods` (`id`, `code`, `name`, `icon`, `min_amount`, `max_amount`, `fee_percentage`, `bonus_percentage`, `is_active`, `config_json`) VALUES
(1, 'paypal', 'PayPal Express Checkout', 'paypal', 5.0000, 1000.0000, 2.90, 0.00, 1, '{"mode":"sandbox","client_id":"demo_paypal_id","client_secret":"demo_paypal_secret"}'),
(2, 'stripe', 'Credit / Debit Card (Stripe)', 'credit-card', 5.0000, 2500.0000, 0.00, 2.00, 1, '{"publishable_key":"pk_test_demo","secret_key":"sk_test_demo","webhook_secret":"whsec_demo"}'),
(3, 'coinpayments', 'Cryptocurrency (CoinPayments: BTC/USDT/ETH)', 'bitcoin', 10.0000, 10000.0000, 0.00, 5.00, 1, '{"merchant_id":"demo_merchant_id","ipn_secret":"demo_ipn_secret"}'),
(4, 'paytr', 'PayTR Virtual POS (Cards & EFT)', 'shield-check', 5.0000, 1000.0000, 1.50, 0.00, 1, '{"merchant_id":"demo_paytr_id","merchant_key":"demo_key","merchant_salt":"demo_salt"}'),
(5, 'shopier', 'Shopier Hosted Gateway', 'shopping-bag', 5.0000, 1000.0000, 2.00, 0.00, 1, '{"api_key":"demo_shopier_key","api_secret":"demo_secret"}'),
(6, 'bank', 'Direct Bank Transfer / Wire', 'building-columns', 25.0000, 10000.0000, 0.00, 3.00, 1, '{"bank_name":"Global SMM Bank","account_name":"SMM Global Services LLC","iban":"US980000012345678901","swift":"GSMMUS33"}');

-- 8. Insert Coupons
INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `max_uses`, `used_count`, `min_spend`, `expiry_date`, `status`) VALUES
(1, 'WELCOME10', 'percentage', 10.0000, 500, 1, 10.0000, '2027-12-31', 'active'),
(2, 'BOOST50', 'fixed', 5.0000, 200, 0, 25.0000, '2027-12-31', 'active');

-- 9. Insert Sample Wallet Transactions (for demo_user)
INSERT INTO `wallet_transactions` (`user_id`, `amount`, `balance_before`, `balance_after`, `type`, `reference_id`, `reference_type`, `description`, `created_at`) VALUES
(2, 100.0000, 0.0000, 100.0000, 'deposit', 1, 'payment', 'Initial deposit via Stripe Checkout (Ref: #STR-10023)', NOW() - INTERVAL 2 DAY),
(2, -25.0000, 100.0000, 75.0000, 'order_charge', 1, 'order', 'Order #1 placed for Instagram Followers', NOW() - INTERVAL 1 DAY),
(2, 50.0000, 75.0000, 125.0000, 'deposit', 2, 'payment', 'Deposit via Crypto USDT (Ref: #CP-8849)', NOW() - INTERVAL 12 HOUR),
(2, -25.0000, 125.0000, 100.0000, 'order_charge', 2, 'order', 'Order #2 placed for YouTube Views', NOW() - INTERVAL 2 HOUR);

-- 10. Insert Sample Orders
INSERT INTO `orders` (`id`, `user_id`, `service_id`, `provider_id`, `provider_order_id`, `link`, `quantity`, `charge`, `provider_charge`, `profit`, `start_count`, `remains`, `status`, `created_at`) VALUES
(1, 2, 1, 1, 'PRV-98711', 'https://instagram.com/verified_brand', 20000, 25.0000, 15.0000, 10.0000, 12400, 0, 'completed', NOW() - INTERVAL 1 DAY),
(2, 2, 4, 1, 'PRV-98782', 'https://youtube.com/watch?v=demoVideo123', 8928, 25.0000, 14.5000, 10.5000, 520, 200, 'inprogress', NOW() - INTERVAL 2 HOUR);

-- 11. Insert System Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'ApexSMM Global Panel'),
('site_title', 'ApexSMM — Premium Social Media Marketing Platform'),
('site_description', 'High performance, real-time SMM provider panel with instant delivery and automated API synchronization.'),
('site_keywords', 'smm panel, social media marketing, buy followers, buy likes, youtube views, tiktok growth'),
('site_currency', 'USD'),
('currency_symbol', '$'),
('active_theme', 'classic'),
('maintenance_mode', '0'),
('register_enabled', '1'),
('email_verification_required', '0'),
('referral_system_enabled', '1'),
('referral_commission_rate', '5.00'),
('referral_min_payout', '20.00'),
('child_panel_price', '25.00'),
('contact_email', 'support@smmpanel.local'),
('terms_content', '<h3>1. Terms of Service</h3><p>By placing an order with our SMM Panel, you automatically accept all the below listed terms of services whether you read them or not.</p><h3>2. Service Disclaimer</h3><p>Our services are strictly meant for promotional purposes. We do not guarantee that your new followers will interact with you.</p>'),
('privacy_content', '<h3>Privacy Policy</h3><p>We respect your privacy. All information entered on this website is kept strictly confidential and will never be shared or sold to any third parties.</p>');

-- 12. Insert Broadcast Announcements
INSERT INTO `broadcasts` (`title`, `content`, `type`, `is_active`, `display_pages`, `expiry_date`) VALUES
('System Upgrade & Instant API Delivery', 'Welcome to the newly rebuilt ApexSMM platform! All Instagram, YouTube, and TikTok orders are running with zero delay and 24/7 automated refill monitoring.', 'announcement', 1, 'all', '2027-12-31');

-- 13. Insert Sample Ticket
INSERT INTO `tickets` (`id`, `user_id`, `subject`, `priority`, `status`, `created_at`) VALUES
(1, 2, 'Speed inquiry for Order #2 YouTube Views', 'medium', 'answered', NOW() - INTERVAL 1 HOUR);

INSERT INTO `ticket_messages` (`ticket_id`, `user_id`, `is_admin`, `message`, `created_at`) VALUES
(1, 2, 0, 'Hello support team, could you please confirm the current delivery speed of Order #2? Thank you!', NOW() - INTERVAL 1 HOUR),
(1, 1, 1, 'Hello! Order #2 is actively progressing at roughly 3,000 views per hour to ensure natural retention. Everything is running normally.', NOW() - INTERVAL 30 MINUTE);
