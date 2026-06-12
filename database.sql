-- OTP Verification System – Schema
-- Run this AFTER the main schema.sql has been executed.
-- Adds OTP columns to the existing `users` table.

ALTER TABLE users
    ADD COLUMN otp VARCHAR(6) DEFAULT NULL AFTER remember_token,
    ADD COLUMN otp_expiry DATETIME DEFAULT NULL AFTER otp;
