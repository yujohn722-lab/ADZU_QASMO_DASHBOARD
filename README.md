# Energy Crisis Learning Continuity Dashboard

Laravel Blade + MySQL web system for monthly institutional energy-crisis continuity reporting.

The system includes:

- Login/logout with role-aware access.
- Admin dashboard with institutional summaries and Chart.js visualizations.
- Respondent dashboard scoped to the user's own submitted records.
- CRUD modules for fuel prices, electricity consumption, solar performance, student service volume, estimated savings, and the placeholder fuel/vehicle use module.
- Report generation page with filters, printable layout, and CSV export.
- Migrations, models, controllers, routes, Blade views, validation, and sample seed data.

## Default Seeded Accounts

After running `php artisan db:seed`, use:

- Admin: `admin@example.edu` / `password`
- Respondent: `respondent@example.edu` / `password`

## Local Setup

1. Install PHP 8.2+, Composer, MySQL, and Node.js.
2. Install dependencies:

   ```bash
   composer install
   npm install
   ```

3. Copy and configure the environment file:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Set your MySQL credentials in `.env`.
5. Run migrations and sample data:

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. Start the local server:

   ```bash
   php artisan serve
   ```

7. Open `http://127.0.0.1:8000`.

## Notes

- PDF export is supported through the browser's print dialog using the printable report layout.
- CSV export is available from the Reports page.
- The Fuel and Vehicle Use module is intentionally a placeholder with a database-ready table and CRUD shell for future fields.
