import { test, expect } from '@playwright/test';
import { PASSWORD, USERS } from './helpers';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';

// Spec ini menguji halaman login/daftar itu sendiri, jadi mulai tanpa sesi.
test.use({ storageState: { cookies: [], origins: [] } });

test.describe('Autentikasi', () => {
  test('pengunjung baru bisa mendaftar lalu masuk ke dashboard', async ({ page }) => {
    const id = Date.now(); // unik, supaya aman diulang
    const register = new RegisterPage(page);

    await register.goto();
    await register.register({
      email: `baru_${id}@alinea.test`,
      password: PASSWORD,
      username: `baru_${id}`,
      name: 'Pengguna Baru',
      city: 'Malang',
      phone: '08123456789',
      genre: 'Fiksi',
    });

    await expect(page).toHaveURL(/\/dashboard/);
  });

  test('login berhasil dengan akun valid', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login(USERS.borrower.email, PASSWORD);
    await login.expectLoggedIn();
  });

  test('login gagal menampilkan pesan error', async ({ page }) => {
    const login = new LoginPage(page);
    await login.goto();
    await login.login(USERS.borrower.email, 'password-salah');
    await login.expectError('Email atau password salah');
    await expect(page).toHaveURL(/\/login/);
  });
});
