-- Seed SQL to create initial super_admin account
-- Replace the email and password_hash with your actual values
-- To generate password_hash, use PHP: password_hash('yourpassword', PASSWORD_DEFAULT)

-- Example (replace with your values):
-- INSERT INTO users (email, password_hash, role, is_verified, password_reset_required, created_at) VALUES (
--   'admin@tbmas.local',
--   '$2y$10$YourGeneratedPasswordHashHere',
--   'super_admin',
--   1,
--   0,
--   NOW()
-- );

-- To generate password hash, run this PHP code:
-- <?php
-- echo password_hash('your_admin_password', PASSWORD_DEFAULT);
-- ?>

