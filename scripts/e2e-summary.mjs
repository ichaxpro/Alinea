// Ringkasan hasil Playwright untuk tab "Summary" di GitHub Actions.
// Dipanggil workflow: node scripts/e2e-summary.mjs >> "$GITHUB_STEP_SUMMARY"
import { readFileSync, existsSync } from 'node:fs';

const file = 'test-results/results.json';
if (!existsSync(file)) {
  console.log('## E2E Playwright\n\n:warning: `results.json` tidak ditemukan. Test mungkin gagal sebelum berjalan.');
  process.exit(0);
}

const report = JSON.parse(readFileSync(file, 'utf8'));
const rows = [];

const walk = (suite, parents = []) => {
  const path = suite.title ? [...parents, suite.title] : parents;
  for (const spec of suite.specs ?? []) {
    const test = spec.tests?.[0];
    const results = test?.results ?? [];
    const last = results.at(-1);
    rows.push({
      title: [...path, spec.title].filter((p) => !p.endsWith('.ts')).join(' › '),
      status: test?.status ?? 'unknown', // expected | unexpected | flaky | skipped
      ms: results.reduce((s, r) => s + r.duration, 0),
      error: last?.error?.message?.split('\n')[0] ?? '',
    });
  }
  for (const child of suite.suites ?? []) walk(child, path);
};
report.suites.forEach((s) => walk(s));

const icon = { expected: ':white_check_mark:', unexpected: ':x:', flaky: ':warning:', skipped: ':fast_forward:' };
const count = (s) => rows.filter((r) => r.status === s).length;

console.log('## Hasil E2E Playwright\n');
console.log(`**${count('expected')} lulus** · **${count('unexpected')} gagal** · ${count('flaky')} flaky · ${count('skipped')} dilewati\n`);
console.log('| Status | Test | Durasi |\n|---|---|---|');
for (const r of rows) {
  console.log(`| ${icon[r.status] ?? r.status} | ${r.title} | ${(r.ms / 1000).toFixed(1)}s |`);
}
const failed = rows.filter((r) => r.status === 'unexpected');
if (failed.length) {
  console.log('\n### Detail kegagalan\n');
  for (const r of failed) console.log(`- **${r.title}**: \`${r.error.replace(/\u001b\[[0-9;]*m/g, '')}\``);
  console.log('\nUnduh artifact **playwright-report** lalu jalankan `npx playwright show-trace <trace.zip>` untuk melihat langkahnya.');
}
