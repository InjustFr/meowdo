# Accounts

- **A1** No sign-up page. `app:user:create <email> --name --cat --coat --timezone` creates the user, their player profile (0 XP) and their cat, then emails an invitation valid 7 days to choose a password (≥ 12 chars). — `CreateUserHandler`, `SetPasswordHandler`
- **A2** Forgot password sends a link valid 1 hour (3 requests per 15 min per email and IP); an unknown email gets the same answer. — `RequestPasswordResetHandler`, `ForgotPasswordController`
- **A3** Each user sees only their own data (owner scoping). Another user's task or project id answers 404. — `OwnerScope`
- **A4** The time zone decides when "today" starts and ends; it can be changed in settings. — `User::today()`, `ChangeTimezoneHandler`
