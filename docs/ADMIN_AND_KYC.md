# Administrator and identity verification setup

## Local setup

After pulling the project changes, apply the additive database migrations:

```powershell
php artisan migrate
```

Create an account through the normal registration flow and verify its email. An administrator can then grant that existing account administrator access from the application console:

```powershell
php artisan mytradex:admin:grant verified-account@example.com
```

The command asks for confirmation, grants no password, and refuses unverified or inactive accounts. Administrator roles are not assignable through public registration.

## KYC document handling

Documents are validated as JPG, PNG, or PDF files up to 5 MB and stored on the `private` local disk under `storage/app/private`. They are not published through `public/storage`. The document route is inside the authenticated, active, verified administrator area and returns a download only after authorization.

Reviews are allowed only while an application is pending. Rejections require a reason. Every decision creates a review-history row with its reviewer, decision, reason, and timestamp. A user may submit another application after rejection; earlier submissions and their private documents remain stored for audit purposes.

## Before production use

- Do not upload real identity documents to the local preview.
- Define and implement legally reviewed retention and deletion schedules before collecting real documents.
- Configure encrypted private storage, backups, access monitoring, and least-privilege production credentials.
- Review the applicable KYC/AML process and privacy notice for the operating jurisdictions.
- Provision administrators only through a trusted server console and protect that access.

This workflow provides application plumbing only. It does not establish regulatory approval or legal compliance.
