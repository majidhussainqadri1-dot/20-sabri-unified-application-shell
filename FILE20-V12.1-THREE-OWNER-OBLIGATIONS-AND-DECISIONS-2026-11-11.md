# فائل 20 — ماسٹر پلان V12.1 کے تحت تین بین الفائل ذمہ داریوں کی تحقیقی تکمیل و باقی ثبوت

تاریخ: 11 نومبر 2026ء (پاکستان) | محل: File20 GitHub کا Draft PR #43

## تمہیدِ حاکم
یہ دستاویز Repository source، Owner contract، Staging اور Live حقیقت کو الگ رکھتی ہے۔ V12.1 میں File20 صرف مشترک Shell، عالمی Navigation اور محفوظ نمائشی جگہوں کا مالک ہے؛ طبی ریکارڈ، کتاب، مسودہ، نجی رابطے، Support مقدمات اور سکیورٹی حکمرانی ان کے متعلقہ اصل ماڈیولز کی ملکیت ہیں۔ پچھلی کامیاب CI یا 29 اگست 2026ء کی File01 مخصوص Live تصدیق سے موجودہ نفاذ ثابت نہیں ہوتا۔

## ذمہ داری اول — CV-033، CV-034، CV-035

| صاحبِ اختصاص | ماخذی ثبوت | File20 کی تیار شدہ خدمت | باقی اصل مالک کا کام |
|---|---|---|---|
| File 05 | main صرف README؛ ترقی یافتہ کوڈ Draft PRs #1, #2, #4, #5, #6, #7 میں | V12.1 ContextHelp اور Generic Recent/Saved entry consumer | منظور شدہ source/CI، owner-authenticated lesson/course recent، Saved ACL اور page help |
| File 12 | main صرف README؛ ترقی یافتہ PDF Reader اور حقوق محفوظی Draft PRs، تازہ #47 | Generic public/private hub-entry consumer | حقیقی Bookmarks/Annotations/Reading Position، حقوق، current-user ACL، revoke/delete اور Saved projection |
| File 17 | مرکزی Network/Message source main میں | safe owner-approved generic Messages hub link consumers | private message/room recent کے وجود و اجازت کا ہر طلب پر native ثبوت؛ نجی subject کا title کبھی File20 نہ آئے |
| File 22 | Composer/Draft lifecycle main میں | publication/draft-hub generic link consumer | canonical draft editor/permission/revision/recovery/status، logout-safe/role-safe handoff |
| CF02 | Support/Appeals main source | public-safe contextual Help slot اور محفوظ Support link | native owner verified support guidance, relevant screen-to-support mapping, role/account proof, sensitive case text not injected |

### File20 پہلے سے تیار شدہ قابلِ استعمال Contracts
**Contextual Help (CV-033):**
- `sabri_shell_context_help_contract` بطور filter؛ `contract_version: 1.0.0`, `owner`, `route_url`, `visibility: public-safe-guidance`, `explanation`؛ اختیاری `example`, زیادہ سے زیادہ آٹھ `glossary` items، `support_url`۔
- `sabri_shell_context_help_owner_approved`، پہلے سے false؛ حقیقی اصل ماڈیول کی per-route منظوری کے بغیر کچھ ظاہر نہیں۔ موجودہ route، strict same-origin، length limits اور no-JS `details` کے ثبوت شامل ہیں۔
- ہر مدد اصل صفحے پر متن/وضاحت/مثال/اصطلاح/رابطہ دے، مرکزی کام میں مداخلت نہ کرے؛ کوئی clinical data، form values یا Support case File20 میں محفوظ نہیں۔

**Recent/Saved Workspace (CV-034/035):**
- `sabri_shell_workspace_entries_contract`، `version: 1.0.0`, `principal_id` صحیح موجودہ login user، `items` 10 سے زیادہ نہیں؛ ہر item: `kind: recent|saved`, `owner`, `intent: entry-only`, same-origin `url`۔
- `sabri_shell_workspace_entry_owner_approved`، پہلے سے false؛ current-user per-owner اجازت؛ File20 supplied item titles کو نظر انداز کرکے fixed generic labels دکھاتا ہے۔
- یہ **صرف محفوظ Hub Entry Link** ہے، خودبخود private per-entity recent یا unified Saved/Collections backend نہیں؛ ہر اصل مالک کا مقصدی database/permissions، revocation، freshness، logout اور shared-device acceptance الگ ضروری ہے۔
- Source-only filter نام main repositories میں ابھی نافذ ملنے کا ثبوت نہیں؛ پورے عملی اتصال کو Complete نہ لکھا جائے۔

## ذمہ داری دوم — File02 + File24 حفاظتی صفحات کا حقیقی مینو

**File02 PR #27 — Account Safety**
- `SAUTH_Canonical_Routes` کی `/account/safety/` public canonical rewrite، `render_safety`, `sauth_canonical_route_url`, `sabri_auth_account_safety`, `SAUTH_Account_Safety` اور current rewrite-ready version کے اصل ماخذی شواہد مطلوب ہیں۔
- یہ WordPress Page ہونا **لازم نہیں**؛ صرف غیر فعال/غیر شائع شدہ/غلط route پر Link دکھانا ممنوع ہے۔
- File02 کی ذاتی Passkeys/Sessions/MFA خدمت logged-in owner کے پاس؛ public guidance نجی session detail نہیں دیتی۔

**File24 PR #32 — Public Security Guidance**
- `Sabri\Platform\Security\Guidance\SecurityGuidance::renderPublic` کا اصل shortcode callback `[sabri_security_guidance]` اور صرف **ایک منفرد شائع شدہ WordPress Page** لازم ہے۔
- File20 کی existing collision-aware shortcode-to-published-page resolver اور strict origin/path حفاظت قبولیت کی پہلی ماخذی شرطیں ہیں۔
- Admin-only security guidance عوامی صفحہ یا فعال MFA/WAF/Backup کا ثبوت نہیں؛ صفحہ خودکار نہیں بنایا یا شائع کیا گیا۔

**File20 کے ماخذی اضافہ:**
- `class-security-navigation.php` موجودہ resolved navigation کو per-request صرف اصل مالک کے ثبوت ملنے پر `account_safety` اور `security_guidance` سے بڑھاتا ہے؛ یہ owner health کو NAV transient میں محفوظ نہیں کرتا۔
- دونوں رابطے صرف موجودہ **More** میں؛ مستقل دوسری Navigation Bar نہیں۔
- کلاس/فلٹر/shortcode/route/published-page proof، تبدیلیِ مالک، duplicate route، external domain، encoded traversal، query/fragment، page collision اور safety regression آزمائشی مقدمات ہیں۔
- اس PR کی CI صرف Repository کے Mock اور ماخذی تقاضے ثابت کرتی ہے؛ File02/24 کے Draft PRs کی منظوری، حقیقی WordPress شائع شدہ صفحہ، WordPress REST/session اور Live check باقی ہیں۔

## ذمہ داری سوم — PR #41 اور PR #42 کا محاکمہ

**PR #41: Welcome Preference Ownership — Merge پر روک**
- V12.1 کی File13 ذمہ داری میں مختصر تعارفی تجربہ، Escape/Skip اور کم از کم 30 روزہ وقفہ درج ہے؛ File20 کی Navigation/Layout ذمہ داری الگ اور File25 کا بصری انٹرفیس الگ ہے۔
- GitHub File13 main میں پچھلا public `Renderer::invoke` تاریخ/compatibility کے طور پر غیر registered no-op ہے؛ PR41 اس خلا کو File20 user-meta/cookie/localStorage نئی ملکیت بنا کر حل کرنا چاہتا ہے۔ یہ ماخذی **ownership drift** ہے، خودکار قابلِ نفاذ اجازت نہیں۔
- Review فیصلہ: PR41 **DRAFT، DO NOT MERGE**۔ منظوری سے قبل Founder کی V12.1 میں صریح ترمیم یا File13 کی فعال canonical preference/intro صورت پر واپسی، guest/member dismissal migration، File25 renderer proof، dual-hook prevention اور staged 30-day test ضروری ہیں۔ PR #41 پر comment درج ہے۔

**PR #42: File14 experiment preflight — اجرای بحالی ممنوع**
- File14 کی 11-field placement baseline اور File20 preflight کے 40 mocked حالات صرف request shape و hash جانچتے ہیں؛ اصل Founder approval، immutable snapshot اور File20/25 last-writer/CAS نہیں۔
- Review فیصلہ: PR42 **DRAFT**، `cas_write_executable=false`, `owner_authorized=false`, `staging_accepted=false` برقرار۔ کوئی SQL restore، فعال experiment rollback، DB update یا CI-based Live resolution نہیں۔ PR #42 پر comment درج ہے۔
- مستقبل کے executable restore سے قبل signed baseline، original mutation provenance، approved native owner command، per-version CAS، concurrent conflict tests، Staging+Rollback+Live evidence لازم ہے۔

## اس نشست کا Live Reality Freeze — دستیاب حقائق
- اصل سائٹ: `https://www.sabrihomeopathy.com`؛ WordPress/Jetpack connector نے `site_disconnected` رپورٹ کیا۔
- WPVibe ربط کی درخواست میں `HTTP 530` ملا؛ یہ اصل DNS/Origin/Plugin/Protection کی تشخیص نہیں اور نہ کسی حفاظتی تہہ کو بند کرنے کی اجازت ہے۔
- Live page publishing، plugin activation، deployed source/version، DB schema، migration/option rows، logs یا checksums اس نشست میں تصدیق نہیں ہوسکے۔
- **Exact deployed code ابھی unverified ہے؛ repository-based diagnosis provisional ہے۔**

## قبولیت و نقائص کی حیثیت
- File20 کی مشروط Security Menu routing کو source PR کی حد تک مکمل سمجھا جا سکتا ہے **صرف کامیاب exact-head CI کے بعد**؛ actual public File24 page اور File02 rewrite testing صفر Live evidence رکھتی ہے۔
- Cross-module Help/Recent/Saved: File20 consumer present؛ حقیقی مستقل owner providers اور user permissions **Pending**۔
- PR41/PR42: merge/authority **Not Approved**؛ محفوظ تحقیقات و preflight برقرار۔
- **Repository HEAD / Deployed Version / DB Version / Migration State / Live Verification Status** ہمیشہ علیحدہ درج ہوں؛ پوری Production تکمیل کا دعویٰ نہ کیا جائے۔

## اگلے عمل کی مرتب ترتیب
1. File05 اور File12 کے مناسب Draft source کی reviewer approval و Build provenance۔ File17/22/CF02 کے موجودہ owner pages/contracts کا source evidence۔ 
2. اصل ماڈیولز کے owner-provider filters، permission proof، privacy redaction، canonical route و provider version کے Regression tests۔
3. File02/24 کے الگ owner PRs کی منظوری، Staging، حقیقی public guidance Page کی founder-approved اشاعت، File20 conditional More links کی real check۔
4. V12.1 کے تحت File13/20/25 Welcome ملکیت پر Founder کا تحریری فیصلہ، پھر migration review؛ File14 CAS پر الگ refusal/approval۔
5. محفوظ Backups، exact packages، Staging/Live freeze، controlled deployment، live re-test، parity report۔
