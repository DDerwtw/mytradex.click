# Financial ledger foundation

The ledger is an internal accounting foundation only. It is not connected to deposits, withdrawals, balances, payment providers, referrals, trading, or investment screens. Financial activity must remain disabled until the ledger is reviewed against the chosen operating model and a complete workflow is implemented and verified.

## Journal rules

- Every posted journal is single-currency and must contain at least two entries.
- Debit and credit totals must match exactly. Amounts are decimal strings with up to eight fractional places; floating-point input is rejected.
- Each journal requires a unique idempotency key. Repeating the same event returns its original journal; reusing the key for different journal data is rejected.
- Account rows are locked in stable ID order while a journal is posted. Entries and posted transaction records cannot be edited or deleted through their Eloquent models; corrections must be implemented later as separately audited reversal journals.
- Balances are not cached or exposed yet. They must be derived from ledger entries by a separately reviewed account-balance service before any funds workflow can use them.

## Current boundary

No accounts are automatically seeded and no public route can post ledger entries. The service is groundwork for later controlled flows. It does not by itself provide a chart of accounts, overdraft policy, provider reconciliation, authorization for account provisioning, or production-grade concurrent posting tests. Do not enter real customer funds or financial events into this preview.
