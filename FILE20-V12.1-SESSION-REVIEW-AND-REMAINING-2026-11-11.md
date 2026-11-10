# فائل 20 — ماسٹر پلان V12.1: موجودہ نشست کی تنقیدی کارکردگی، نقائص اور باقی ذمہ داریاں

**منصوبے کی منظور شدہ تاریخ:** 10 نومبر 2026ء | **اس رپورٹ کی تاریخ:** 11 نومبر 2026ء، پاکستان

## 1. قطعی حدود و اصل ثبوت

- بنیادی منصوبہ: «صابری سوشل ہومیوپیتھی پلیٹ فارم — جامع مرکزی ماسٹر پلان V12.1»؛ باب 3 فائل 20، باب 11 کی CV-026 تا CV-036، CEN-NAV-001 اور باب 12.5۔
- GitHub مرکزی برانچ کا آخری مصدقہ HEAD: `8a4dbcaf4fef8e926b9b834ecfde16c21a0f00ca`؛ بنیادی ماخذی ورژن 1.4.17۔
- اصلاحی **Draft PR #43**، ماخذی امیدوار 1.4.18؛ اس رپورٹ سے قبل آخری آزمودہ HEAD: `cc47624712461c1b4f21f1410038cf1a933120e6`۔
- اسی HEAD پر GitHub Actions: File20 V12.1 Quality **SUCCESS**، run `42899647368`؛ Baseline Archive Integrity **SUCCESS**، run `42899647298`۔
- Quality jobs: PHP 7.4، PHP 8.3، تمام run*.php regressions، Node dynamic Forward، JSON/JS/CSS static checks، deterministic ZIP staging/source/extracted checks: **SUCCESS**۔
- CI Artifact ID `13800679716`؛ اس کے envelope کا SHA-256 `6e08c7356475226a5e17ca4de2f2f7e0da3cbcf93a74f1af695a716eddf4a15d` ہے۔ **یہ اندرونی WordPress پلگ اِن ZIP کا SHA-256 نہیں**؛ اس کا الگ extraction/record ہونا باقی ہے۔
- یہ رپورٹ-صرف Commit اس HEAD کے بعد آئے گا؛ اس کے لیے نئی exact-head CI الگ لازم ہے۔
- Live deployed runtime/package، wp_options، database schema، migration state، active theme، owner-provider versions اور current error logs تک یہاں براہِ راست مصدقہ رسائی نہیں تھی۔

## 2. سات بنیادی عملی نتائج

1. `CV-029`: حقیقی Native Navigation API history کے تحت Back/Forward؛ Forward hidden-by-default؛ same-origin check، محفوظ Back fallback، RTL/LTR سمتی تصحیح؛ غیر حقیقی history تخلیق نہیں ہوتی۔
2. Sabri Green کی مرکزی موجودہ قدر `#087A4E` کو File20 کے context-navigation continuity fallback میں برقرار کیا گیا۔
3. `CV-033`: `ContextHelp` کا جدا، قابل رسائی details/summary جزو؛ version `1.0.0`، اصل ماڈیول کا موجودہ route/owner approval، public-safe متن، bounded glossary، same-origin support URL؛ کوئی ٹکٹ/مریض ڈیٹا/صارف فارم کا متن محفوظ نہیں۔
4. `CV-034`: `WorkspaceEntries` موجودہ authenticated principal کے لیے اصل ماڈیول کے Generic Recent Hub رابطے دکھا سکتا ہے؛ اصل private per-entity recents کی ملکیت اپنے owner ہی کے پاس ہے۔
5. `CV-035`: یہی محدود adapter authorized Saved Hub رابطے دکھا سکتا ہے؛ unified bookmark/collection storage اس فائل میں قائم نہیں کیا گیا۔
6. ذاتی عنوان یا مریض/چیٹ کا نام ظاہر ہونے سے روکنے کے لیے owner-provided titles مکمل نظر انداز کرکے fixed generic labels استعمال کیے گئے؛ کوئی private localStorage یا مشترک user-store نہیں۔
7. دوسرا global navigation bar بننے کے اندیشے کو دور کرنے کے لیے Workspace رابطوں کو `<section>` contextual disclosure میں تبدیل کیا گیا؛ الگ `<nav>` ممنوع ہے۔

## 3. defect ledger: ثبوت اور اصلاح الگ

| # | ماخذی مسئلہ یا تقاضائی خلا | ماخذی حالت | Live حالت |
|---|---|---|---|
| 1 | V12.1 کا Conditional Forward موجود نہیں تھا | PR #43 میں اصلاح اور کامیاب CI | غیر مصدقہ |
| 2 | Context Navigation fallback green مرکزی token سے مختلف | اصلاح اور کامیاب CI | غیر مصدقہ |
| 3 | نئے Help جزو کی CSS late dynamic contract میں miss ہو سکتی تھی | اہل layouts پر پیشگی CSS enqueue؛ CI کامیاب | غیر مصدقہ |
| 4 | نئے Workspace adapter سے provider-supplied private title ظاہر ہو سکتا تھا | fixed generic labels؛ adversarial test کامیاب | غیر مصدقہ |
| 5 | نئے Workspace links کا علیحدہ nav عالمی single-nav ضابطے سے متصادم ہو سکتا تھا | non-nav contextual section؛ regression کامیاب | غیر مصدقہ |
| 6 | بدلتے نئے source کو پرانے 1.4.17 artifact نام سے پیک کرنا provenance drift تھا | منفرد draft 1.4.18 runtime/doc/test/ZIP identity؛ CI کامیاب | غیر مصدقہ |

**اس PR کے معلوم ان 6 ماخذی نکات میں:** اصلاح شدہ 6؛ ابھی معلوم غیر اصلاح شدہ 0 **صرف اسی محدود source-review ledger کے دائرے میں**۔ پورے live system کے نقائص کی تعداد نامعلوم ہے۔ وسطی 1.4.18 identity migration کے دوران CI regressions سامنے آئے، version assertions درست ہوئے اور بعد کے exact-head checks کامیاب ہوئے؛ ان وسطی failures کو live defects نہ سمجھا جائے۔

## 4. ابھی باقی — integration، approval اور operational gates

- **CV-033:** CF02 / متعلقہ owner کی حقیقی، صفحہ وار Help mapping، ترجمہ، permissions، Glossary، Help contact اور real-browser/accessibility acceptance۔
- **CV-034:** File05/12/17/22 اور دوسرے owners کی per-entity private recent shortcuts؛ logout، owner-permission revocation، shared-device privacy؛ generic links مکمل private recents نہیں۔
- **CV-035:** canonical bookmarks/collections owner، واحد متحدہ hub کی authorized projections، actual saved-object revocation؛ ابھی generic links ہی بنے ہیں۔
- **File 02/24 حفاظتی صفحے:** File02 `/account/safety/` کی draft #27 اور File24 `[sabri_security_guidance]` کی draft #32 کو بلا منظوری deployed موجودگی نہ سمجھیں۔ شائع شدہ حقیقی صفحہ، منتخب مینو مقام، Role/Owner contract اور Founder approval کے بعد ہی File20 میں actual menu acceptance۔
- **دیگر الگ PRs:** File20 #41 Welcome frequency ownership اور File20 #42 File14 controlled CAS restore کو اس PR میں ضم نہیں کیا گیا؛ دونوں اپنی approval/deployment evidence مانگتے ہیں۔
- **Release gates:** حتمی branch source review، final artifact inner ZIP SHA-256، coherent File00/02/13/17/19/21/24/25/26 staging versions/contracts، WordPress theme و PHP Compatibility، 320–1920px، RTL/LTR، 200%/400% zoom، keyboard/screen readers، PWA، SafeMode، Backup/Restore، Rollback، Founder قبولیت، controlled Live deploy + re-test، اور exact deployed parity۔

## 5. لازمی آخری پانچ حقائق

| لازمی خانہ | آج کی مصدقہ حقیقت |
|---|---|
| Repository HEAD | main `8a4dbcaf4fef8e926b9b834ecfde16c21a0f00ca`؛ PR #43 reviewed candidate `cc47624712461c1b4f21f1410038cf1a933120e6` |
| Deployed Version | ابھی موجودہ حالت غیر مصدقہ؛ 29 اگست کا File01 مخصوص 1.4.17 Live ثبوت تاریخی ہے |
| DB Version | غیر مصدقہ |
| Migration State | غیر مصدقہ |
| Live Verification Status | اس 1.4.18 candidate کے لیے نہیں ہوئی |

**حاکم فیصلہ:** اس PR کا code/package/automated-QA کام **ماخذی سطح پر کامیاب**؛ پورے V12.1 کی بین الفائل عملی تکمیل، Staging، Live اور Operational حالت **ابھی مکمل نہیں**۔ ایک تحقیقاتی نتیجہ دوسرے ماحول کی حقیقت نہیں بن سکتا۔
