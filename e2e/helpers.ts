// Akun hasil E2ESeeder (database/seeders/E2ESeeder.php).
export const PASSWORD = process.env.E2E_PASSWORD ?? 'e2e-Pass-2026';

export const USERS = {
  admin: { email: 'admin@alinea.test', username: 'e2e_admin', name: 'E2E Admin' },
  owner: { email: 'owner@alinea.test', username: 'e2e_owner', name: 'E2E Owner' },
  borrower: { email: 'borrower@alinea.test', username: 'e2e_borrower', name: 'E2E Borrower' },
} as const;

export const BOOK_TITLE = 'Laskar Pelangi E2E';

export const AUTH_FILE = {
  admin: 'e2e/.auth/admin.json',
  owner: 'e2e/.auth/owner.json',
  borrower: 'e2e/.auth/borrower.json',
} as const;
