# Referral relationships

Each account receives a unique 12-character referral code. Existing accounts receive a code through the additive referral migration. Registration accepts a referral code from the invite URL, validates it on the server, and records the referrer once when the new account is created. The referrer cannot be changed through the application.

The authenticated referral page shows direct relationships, up to three levels of relationship counts, active-and-email-verified direct counts, and a copyable referral link. It does not display referred members' email addresses. Relationship data is not used to grant financial value.

Administrators can search recorded relationships by the joining account's or referrer's email address or referral code at `/admin/referrals`. This view is protected by the verified, active administrator middleware and only reads relationship data.

## Rewards and abuse controls

Referral commissions are disabled. A link or relationship alone never creates a commission. No reward rates, payout amounts, or qualification rules are currently configured. Any future reward feature must use an approved programme policy, legitimate eligible activity, fraud review, idempotent events, and the existing ledger.

Registration cannot reliably identify one person using multiple identities. Do not treat this relationship feature as proof that referrals are independent people. Identity/KYC, privacy, fraud, and applicable legal requirements need review before rewards are enabled. There is no IP-only automatic ban.
