# Team Workflow & Contribution Guidelines

This project follows a strict engineering workflow to ensure code quality and architectural integrity.

## 1. Branching Strategy

- **main**: Stable, production-ready code.
- **testing**: Staging branch for QA verification.
- **develop**: Primary integration branch for developers.
- **feature/\***: New features (e.g., `feature/SCRUM-12-auth-login`).
- **bugfix/\***: Issue resolutions (e.g., `bugfix/SCRUM-45-cart-total`).

## 2. Commit Naming Convention

Commits must follow this pattern: `[Jira-Ticket]: [Brief Description]`

- Example: `SCRUM-12: implement stateless login endpoint`

## 3. Workflow

1. Pick a task from Jira.
2. Create a new branch from `develop`.
3. Implement the feature logic (Developers) or tests (QA) on this branch.
4. Test locally using Pest and Postman.
5. Run Static Analysis and Formatting:
   - `composer analyze` (Larastan)
   - `composer lint` (Laravel Pint - PHP)
   - `npm run format` (Prettier - YAML, Markdown, JSON)
6. Open a Pull Request (PR) to `develop`.

## 4. Pull Request Rules

- **No Direct Pushes:** Committing directly to `main`, `testing`, or `develop` is strictly forbidden.
- **Documentation:** All changes must align with the `postman/specs/index.yaml` contract.
- **Testing:** PRs will not be merged unless all Pest tests and Postman collection runs are green.
- **Quality Gates:** All code must pass Static Analysis and Linting checks.
- **Approval:** Every PR requires 1 approval from the **Lead Software Engineer** (Architectural Review) and 1 from **QA**.

## 5. Code Standards

- Adhere to the patterns defined in `docs/ARCHITECTURE.md`.
- Use the `ApiResponse` trait for all controller outputs.
- Never place Eloquent queries in Controllers; use the **Repository Pattern**.
- Business logic belongs in the **Service Layer**.
