import type { Page } from '@playwright/test';

export interface NewUser {
  email: string;
  password: string;
  username: string;
  name: string;
  city: string;
  phone: string;
  genre: string;
}

/** Form pendaftaran 3 langkah: akun -> profil -> genre. */
export class RegisterPage {
  constructor(private readonly page: Page) {}

  async goto() {
    await this.page.goto('/daftar');
  }

  async register(u: NewUser) {
    const p = this.page;

    // Langkah 1: akun
    await p.locator('#email').fill(u.email);
    await p.locator('#password').fill(u.password);
    await p.locator('#password_confirmation').fill(u.password);
    await p.locator('#step-1').getByRole('button', { name: 'Lanjut' }).click();

    // Langkah 2: profil
    await p.locator('#username').fill(u.username);
    await p.locator('#name').fill(u.name);
    await p.locator('#city').fill(u.city);
    await p.locator('#phone').fill(u.phone);
    await p.locator('#step-2').getByRole('button', { name: 'Lanjut' }).click();

    // Langkah 3: genre (checkbox disembunyikan, yang diklik adalah label-nya)
    await p.locator('#step-3').getByText(u.genre, { exact: true }).click();
    await p.locator('#step-3').getByRole('button', { name: 'Daftar' }).click();
  }
}
