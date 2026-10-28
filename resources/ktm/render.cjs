const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1712, height: 1080 } });
  await p.goto('file://' + process.argv[2]);
  await p.waitForTimeout(300);
  await p.screenshot({ path: process.argv[3], type: process.argv[3].endsWith('.jpg') ? 'jpeg' : 'png', quality: process.argv[3].endsWith('.jpg') ? 90 : undefined, clip: { x: 0, y: 0, width: 1712, height: 1080 } });
  await b.close();
})();
