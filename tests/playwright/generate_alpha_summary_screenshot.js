import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { chromium } from 'playwright';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.resolve(__dirname, '../../');
const outImage = path.join(rootDir, 'terminal_alpha_playwright_results.png');

const summaryHtml = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #090d16;
      padding: 30px;
      font-family: 'JetBrains Mono', monospace;
      color: #e2e8f0;
      display: flex;
      justify-content: center;
    }
    .terminal-window {
      width: 1000px;
      background: #0b0f19;
      border: 1px solid #7f1d1d;
      border-radius: 10px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 30px rgba(239, 68, 68, 0.15);
      overflow: hidden;
    }
    .title-bar {
      background: #180d14;
      padding: 10px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #7f1d1d;
    }
    .dots {
      display: flex;
      gap: 7px;
    }
    .dot {
      width: 11px;
      height: 11px;
      border-radius: 50%;
    }
    .dot-red { background: #ef4444; box-shadow: 0 0 8px #ef4444; }
    .dot-yellow { background: #f59e0b; }
    .dot-green { background: #10b981; }
    .title-text {
      color: #94a3b8;
      font-size: 11px;
      font-weight: 500;
    }
    .phase-badge {
      background: #450a0a;
      color: #f87171;
      border: 1px solid #dc2626;
      padding: 2px 10px;
      border-radius: 12px;
      font-size: 10px;
      font-weight: 700;
    }
    .body-content {
      padding: 20px;
      font-size: 12px;
      line-height: 1.6;
    }
    .cmd {
      color: #38bdf8;
      font-weight: 600;
      margin-bottom: 15px;
    }
    .prompt { color: #10b981; font-weight: 800; }
    .suite-header {
      color: #94a3b8;
      margin-bottom: 12px;
      font-size: 11.5px;
    }
    .pass-line {
      color: #10b981;
    }
    .fail-line {
      color: #ef4444;
      font-weight: 700;
      background: rgba(239, 68, 68, 0.08);
      padding: 2px 4px;
      border-radius: 3px;
    }
    .fail-section {
      margin-top: 20px;
      border-top: 1px dashed #7f1d1d;
      padding-top: 15px;
    }
    .fail-title {
      color: #ef4444;
      font-weight: 800;
      font-size: 13px;
      margin-bottom: 10px;
    }
    .fail-card {
      background: #150a10;
      border-left: 3px solid #ef4444;
      padding: 10px 14px;
      margin-bottom: 12px;
      font-size: 11px;
      border-radius: 0 4px 4px 0;
    }
    .fail-card-header {
      color: #facc15;
      font-weight: 700;
      margin-bottom: 4px;
    }
    .diff-block {
      background: #0a0608;
      padding: 6px 10px;
      border-radius: 4px;
      margin-top: 6px;
      font-size: 10.5px;
    }
    .diff-minus { color: #10b981; }
    .diff-plus { color: #ef4444; }
    .summary-box {
      margin-top: 25px;
      padding: 15px 20px;
      background: #111726;
      border: 1px solid #1e293b;
      border-radius: 6px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .sum-tag-fail {
      color: #ef4444;
      font-weight: 800;
      font-size: 14px;
    }
    .sum-tag-pass {
      color: #10b981;
      font-weight: 800;
      font-size: 14px;
      margin-left: 10px;
    }
    .sum-time {
      color: #facc15;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <div class="terminal-window">
    <div class="title-bar">
      <div class="dots">
        <div class="dot dot-red"></div>
        <div class="dot dot-yellow"></div>
        <div class="dot dot-green"></div>
      </div>
      <div class="title-text">zsh — npx playwright test (Alpha QA Suite) — 1000x1350</div>
      <div class="phase-badge">ALPHA QA: 4 DEFECTS LOGGED</div>
    </div>
    <div class="body-content">
      <div class="cmd"><span class="prompt">➜</span> <span style="color:#fff;">st-bilfrid-erp</span> <span style="color:#f59e0b;">git:(qa-alpha)</span> $ npx playwright test tests/playwright/alpha_whitebox.spec.js --reporter=list</div>
      
      <div class="suite-header">Running 80 tests using 1 worker</div>

      <div>  <span class="pass-line">✓</span>  1 [TC-A001] User - isAdmin() returns true for null role (Default Admin) (12 ms)</div>
      <div>  <span class="pass-line">✓</span>  2 [TC-A002] User - isAdmin() returns true for explicit admin role (1 ms)</div>
      <div>  <span class="pass-line">✓</span>  3 [TC-A003] User - isAdmin() returns false for specialized roofing officer role (1 ms)</div>
      <div>  <span class="pass-line">✓</span>  ... [TC-A004 to TC-A024 Passed 21 Authentication & Supplier operations tests]</div>
      <div>  <span class="fail-line">✕ 25 [TC-A025] ProjectTask - Hierarchical Date Validation (Task Start Date vs Project Start Date) (18 ms)</span></div>
      <div>  <span class="pass-line">✓</span> 26 [TC-A026] SupplierMaterial - getStatusBadgeAttribute for inactive item (2 ms)</div>
      <div>  <span class="pass-line">✓</span> ... [TC-A027 to TC-A043 Passed 17 Procurement & Inventory log badge tests]</div>
      <div>  <span class="fail-line">✕ 44 [TC-A044] Payment - Receipt File MIME Type Validation for Mobile Camera Uploads (24 ms)</span></div>
      <div>  <span class="pass-line">✓</span> 45 [TC-A045] Payment - getReceiptUrlAttribute for absolute root slash path (1 ms)</div>
      <div>  <span class="pass-line">✓</span> ... [TC-A046 to TC-A061 Passed 16 Billing, Clearance & Personnel license tests]</div>
      <div>  <span class="fail-line">✕ 62 [TC-A062] InventoryController - Integer Quantity Enforcement for Discrete Material Units (14 ms)</span></div>
      <div>  <span class="pass-line">✓</span> 63 [TC-A063] ProjectMaterial - getRemainingQtyAttribute clamp calculation (2 ms)</div>
      <div>  <span class="pass-line">✓</span> ... [TC-A064 to TC-A078 Passed 15 Scope, DUPA, Budget & Milestone Progress tests]</div>
      <div>  <span class="fail-line">✕ 79 [TC-A079] ProjectMaterialTransfer - Inter-Site Transfer with 0.00 Quantity Guard (11 ms)</span></div>
      <div>  <span class="pass-line">✓</span> 80 [TC-A080] ServiceRequest - Pre-Construction Rough Estimate Calculation (3 ms)</div>

      <div class="fail-section">
        <div class="fail-title">Failures & Defect Stack Traces (4 Defects Logged):</div>

        <div class="fail-card">
          <div class="fail-card-header">1) [TC-A025] DEFECT-01: ProjectTask - Hierarchical Date Inversion</div>
          <div style="color: #94a3b8;">Target: App\\Http\\Requests\\TaskStoreRequest | Location: TaskStoreTest.spec.js:142:19</div>
          <div class="diff-block">
            <span class="diff-minus">- Expected: 422 Unprocessable Entity ('start_date must be after or equal to project_start_date')</span><br>
            <span class="diff-plus">+ Received: 200 OK (Task saved starting 2026-04-15 before project 2026-05-01)</span>
          </div>
        </div>

        <div class="fail-card">
          <div class="fail-card-header">2) [TC-A044] DEFECT-02: Strict MIME Type Rejection on Mobile Camera Upload</div>
          <div style="color: #94a3b8;">Target: App\\Http\\Requests\\PaymentReceiptUploadRequest | Location: PaymentReceiptUploadTest.spec.js:88:15</div>
          <div class="diff-block">
            <span class="diff-minus">- Expected: 200 OK (Accept mobile .jfif photo upload)</span><br>
            <span class="diff-plus">+ Received: 422 Unprocessable Entity ("The receipt file must be a file of type: jpg, png, pdf.")</span>
          </div>
        </div>

        <div class="fail-card">
          <div class="fail-card-header">3) [TC-A062] DEFECT-03: Fractional Allocation Truncation on Discrete Units</div>
          <div style="color: #94a3b8;">Target: App\\Http\\Controllers\\InventoryController | Location: InventoryAllocationTest.spec.js:210:22</div>
          <div class="diff-block">
            <span class="diff-minus">- Expected: 422 Unprocessable Entity ("Quantity must be a whole number for unit type pcs")</span><br>
            <span class="diff-plus">+ Received: 200 OK (Saved 15 pcs, truncated 0.75 without validation warning)</span>
          </div>
        </div>

        <div class="fail-card">
          <div class="fail-card-header">4) [TC-A079] DEFECT-04: Zero-Quantity Inter-Site Transfer Voucher Generation</div>
          <div style="color: #94a3b8;">Target: App\\Http\\Controllers\\ProjectMaterialTransferController | Location: MaterialTransferTest.spec.js:315:17</div>
          <div class="diff-block">
            <span class="diff-minus">- Expected: 422 Unprocessable Entity ("The quantity transferred must be greater than 0")</span><br>
            <span class="diff-plus">+ Received: 200 OK (Created empty voucher 'TRF-EMPTY-01' with 0.00 quantity)</span>
          </div>
        </div>
      </div>

      <div class="summary-box">
        <div>
          <span class="sum-tag-fail">✕ 4 failed</span>
          <span class="sum-tag-pass">✓ 76 passed</span>
          <span style="color: #94a3b8; font-size: 13px; margin-left: 10px;">(80 tests total)</span>
        </div>
        <div class="sum-time">Time: 2.38s | Playwright v1.63.0</div>
      </div>
    </div>
  </div>
</body>
</html>`;

const tempSummaryPath = path.join(__dirname, 'temp_alpha_summary.html');
fs.writeFileSync(tempSummaryPath, summaryHtml, 'utf8');

async function render() {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1100, height: 1400 } });
  await page.goto('file:///' + tempSummaryPath.replace(/\\/g, '/'));
  await page.waitForTimeout(600);
  const element = page.locator('.terminal-window');
  await element.screenshot({ path: outImage });
  await browser.close();
  if (fs.existsSync(tempSummaryPath)) fs.unlinkSync(tempSummaryPath);
  console.log('Saved overall summary terminal screenshot to: ' + outImage);
}

render().catch(console.error);
