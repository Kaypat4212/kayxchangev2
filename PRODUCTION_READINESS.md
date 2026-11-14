# Production readiness additions

This release adds wallet-ledger records, an admin audit log, transaction-linked support cases, user authenticator-based sign-in verification, crypto-withdrawal risk flags, archive-integrity checks, and scheduled provider reconciliation.

## Deploying the database change

Take a database backup before deploying. The cPanel deployment file runs `php artisan migrate --force`; this migration creates the operations tables, adds provider-check and withdrawal-risk fields, and records opening ledger entries from current NGN and non-NGN balances. It does not rewrite balances.

Verify the new migration ran:

```bash
php artisan migrate:status
```

## Required scheduler on cPanel

The reconciliation command only runs when Laravel's scheduler runs. In cPanel → Cron Jobs, add this every minute (use the actual PHP binary and repository path for the account):

```bash
* * * * * cd /home/ACCOUNT/public_html && /usr/local/bin/ea-php82 artisan schedule:run >> /dev/null 2>&1
```

Check the path and PHP executable in the cPanel terminal. The schedule now rechecks up to 20 stale NOWPayments/Cryptomus crypto invoices every five minutes. Make sure outbound HTTPS requests to the payment provider are allowed. The IPN URL remains the `nowpayments.webhook` route, normally `https://tradewithkay.com/api/nowpayments/webhook` when `APP_URL` is configured correctly.

## Operational pages

- **Admin → Tools → Operations Health** shows provider reconciliation state, requests older than 30 minutes, recent wallet-ledger movements, and recent admin actions.
- Admin notifications are generated for persistent provider failures, transactions awaiting review for more than 30 minutes, and crypto withdrawals that exceed configured risk thresholds. Duplicate alerts are throttled for 24 hours per issue.
- **Admin → Tools → Admin Audit Log** shows admin mutations; encrypted setting contents are redacted.
- **Admin → Support → Support Cases** is the response queue. Users can open a deposit-linked case from the deposit status page.
- **Admin → Features** configures the single and daily USD thresholds that flag elevated-risk crypto withdrawals. All crypto withdrawals continue to require admin handling.
- **Admin → Backup** checks ZIP structure and each entry's CRC before labeling an archive verified. Keep an off-server copy and test restoration in a separate environment before relying on a backup.
- Users can enable authenticator verification under **Profile → Manage sign-in security**.

## Release verification

After deployment, check the scheduler cron, review Operations Health for a successful provider check, create a low-value sandbox deposit and confirm it progresses from IPN and polling, verify a support case and admin reply, and confirm an NGN or crypto balance movement appears in wallet history. Do not use live deposits to test callback changes.
