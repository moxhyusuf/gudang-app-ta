<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $title ?? 'Gudang Teknik' ?> — Gudang Teknik</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ============================================================
   GUDANG TEKNIK — DESIGN SYSTEM v2
   Palette: Navy anchor + Terracotta clay accent + Warm parchment bg
   Signature: frosted-glass card surface with clay left-accent bars
   ============================================================ */

:root {
  /* Core palette */
  --ink:        #1c2535;      /* near-black navy for headings */
  --ink2:       #1a2332;      /* dark grey body text */
  --ink3:       #4a5568;      /* muted label/meta */
  --ink4:       #64748b;      /* placeholder/disabled */

  /* Background */
  --bg:         #f0ede3;      /* warm parchment */
  --bg2:        #e8e4d8;      /* slightly deeper parchment */
  --surface:    rgba(255,253,245,0.82);  /* frosted warm white */
  --surface2:   rgba(255,253,245,0.60);
  --surface-solid: #fffdf5;

  /* Terracotta clay accent (replaces red) */
  --clay:       #a05a42;
  --clay2:      #8a4a34;
  --clay3:      #c07a60;
  --clay-bg:    rgba(160,90,66,0.08);
  --clay-bg2:   rgba(160,90,66,0.14);
  --clay-border:rgba(160,90,66,0.22);

  /* Navy accent */
  --navy:       #1c2d4f;
  --navy2:      #253d6a;
  --navy3:      #0f1d38;
  --navy-bg:    rgba(28,45,79,0.06);

  /* Semantic */
  --green:      #16a34a;
  --green-bg:   rgba(22,163,74,0.09);
  --green-border:rgba(22,163,74,0.2);
  --amber:      #d97706;
  --amber-bg:   rgba(217,119,6,0.09);
  --amber-border:rgba(217,119,6,0.22);
  --red:        #dc2626;
  --red-bg:     rgba(220,38,38,0.08);
  --red-border: rgba(220,38,38,0.2);
  --blue:       #2563eb;
  --blue-bg:    rgba(37,99,235,0.08);
  --blue-border:rgba(37,99,235,0.2);

  /* Border */
  --border:     rgba(160,130,100,0.18);
  --border2:    rgba(160,130,100,0.28);

  /* Typography */
  --font:       'DM Sans', sans-serif;
  --mono:       'Inter', sans-serif;

  /* Geometry */
  --r:          8px;
  --r-lg:       14px;
  --r-xl:       18px;

  /* Shadows — subtle depth, no dramatic drop */
  --shadow-xs:  0 1px 3px rgba(28,37,53,.06);
  --shadow-sm:  0 2px 8px rgba(28,37,53,.08), 0 1px 2px rgba(28,37,53,.05);
  --shadow-md:  0 4px 16px rgba(28,37,53,.10), 0 1px 4px rgba(28,37,53,.06);
  --shadow-lg:  0 12px 32px rgba(28,37,53,.14), 0 3px 8px rgba(28,37,53,.08);
}

/* ── RESET ── */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { -webkit-text-size-adjust:100%; }
body {
  background: var(--bg);
  font-family: var(--font);
  font-size: 15px;
  color: var(--ink2);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
  position: relative;
}

/* Watermark Sasa — tetap terlihat */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background:
    url('/img/sasa-bg.png') center center / 42% auto no-repeat;
  opacity: 0.055;
  pointer-events: none;
  z-index: 0;
}

.gtsis-wrap, .gtsis-nav { position: relative; z-index: 1; }

/* ── SCROLLBAR ── */
::-webkit-scrollbar { width:9px; height:9px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:rgba(28,37,53,.15); border-radius:20px; border:2px solid transparent; background-clip:content-box; }
::-webkit-scrollbar-thumb:hover { background:rgba(28,37,53,.28); background-clip:content-box; }

/* ── TYPOGRAPHY ── */
h1,h2,h3,h4,h5,h6 { font-family:var(--font); font-weight:700; color:var(--ink); letter-spacing:-0.3px; }
.mono { font-family:var(--mono); }
.text-muted-g  { color:var(--ink3); }
.text-navy     { color:var(--navy); }
.text-primary-g{ color:var(--clay); }
.text-blue     { color:var(--blue); }

/* ============================================================
   NAVBAR
   ============================================================ */
.gtsis-navbar {
  background: linear-gradient(160deg, var(--navy3) 0%, var(--navy) 60%, var(--navy2) 100%);
  height: 62px;
  display: flex;
  align-items: center;
  padding: 0 28px;
  position: sticky;
  top: 0;
  z-index: 1000;
  border-bottom: 1px solid rgba(255,255,255,.055);
  box-shadow: 0 2px 16px rgba(0,0,0,.28), 0 1px 0 rgba(255,255,255,.04) inset;
}

/* Brand */
.gtsis-brand {
  display:flex; align-items:center; gap:10px;
  text-decoration:none; margin-right:20px; flex-shrink:0;
}
.gtsis-brand img {
  height:28px; border-radius:6px;
  background:rgba(255,255,255,.95);
  padding:3px 6px;
  box-shadow:0 2px 8px rgba(0,0,0,.2);
}
.gtsis-brand .brand-name {
  color:#fff; font-weight:700; font-size:15.5px; letter-spacing:-0.3px;
}
.gtsis-brand .brand-sub {
  color:rgba(255,255,255,.38); font-size:10px; display:block; line-height:1; margin-top:1px;
}

.nav-div {
  width:1px; height:24px;
  background:linear-gradient(to bottom, transparent, rgba(255,255,255,.14), transparent);
  margin:0 14px; flex-shrink:0;
}

/* Nav links */
.gtsis-menu { display:flex; align-items:center; gap:1px; flex:1; overflow:visible; }

.gnav {
  padding:7px 12px; border-radius:8px;
  color:rgba(255,255,255,.62); font-size:13px; font-weight:500;
  text-decoration:none; white-space:nowrap;
  transition:color .14s, background .14s, transform .1s;
  position:relative; border:none; background:none;
  cursor:pointer; font-family:var(--font); letter-spacing:0.1px;
}
.gnav:hover { color:#fff; background:rgba(255,255,255,.07); }
.gnav:active { transform:translateY(1px); }
.gnav.active { color:#fff; background:rgba(255,255,255,.09); }
.gnav.active::after {
  content:''; position:absolute; bottom:-2px; left:10px; right:10px;
  height:2.5px; background:linear-gradient(90deg, var(--clay3), var(--clay));
  border-radius:3px 3px 0 0; box-shadow:0 0 8px rgba(160,90,66,.55);
}

/* Dropdown */
.gdrop { position:relative; }
.gdrop > .gnav { display:inline-flex; align-items:center; gap:5px; }
.gdrop .chev { width:11px; height:11px; transition:transform .18s; opacity:.75; }
.gdrop.open .chev { transform:rotate(180deg); }
.gdrop-menu {
  position:absolute; top:calc(100% + 10px); left:0;
  background:var(--surface-solid);
  border:1px solid var(--border2);
  border-radius:var(--r-lg);
  box-shadow:var(--shadow-lg);
  min-width:210px; padding:6px; display:none; z-index:500;
  animation:dropIn .15s ease;
}
@keyframes dropIn {
  from { opacity:0; transform:translateY(-5px) scale(.98); }
  to   { opacity:1; transform:translateY(0) scale(1); }
}
.gdrop-item {
  display:flex; align-items:center; gap:10px; padding:9px 11px;
  border-radius:8px; color:var(--ink2); font-size:13px; font-weight:500;
  text-decoration:none; transition:all .13s;
}
.gdrop-item svg { width:15px; height:15px; flex-shrink:0; color:var(--ink4); transition:color .13s; }
.gdrop-item:hover { background:var(--clay-bg); color:var(--ink); }
.gdrop-item:hover svg { color:var(--clay); }
.gdrop-item.active { background:var(--clay-bg2); color:var(--clay2); font-weight:600; }
.gdrop-item.active svg { color:var(--clay); }
.gdrop-sep { height:1px; background:var(--border); margin:4px 6px; }

/* Nav right */
.gtsis-right { display:flex; align-items:center; gap:8px; margin-left:auto; flex-shrink:0; }

.user-chip {
  display:flex; align-items:center; gap:8px; padding:5px 11px 5px 5px;
  background:rgba(255,255,255,.065); border-radius:10px;
  border:1px solid rgba(255,255,255,.09); transition:background .14s;
}
.user-chip:hover { background:rgba(255,255,255,.1); }
.user-avatar {
  width:30px; height:30px; border-radius:8px;
  display:flex; align-items:center; justify-content:center;
  font-size:12.5px; font-weight:700; color:#fff; flex-shrink:0;
  box-shadow:0 2px 6px rgba(0,0,0,.22), inset 0 1px 0 rgba(255,255,255,.18);
}
.ua-admin   { background:linear-gradient(145deg, #2c4068, var(--navy2)); }
.ua-petugas { background:linear-gradient(145deg, #1a7f4b, #16a34a); }
.ua-plant   { background:linear-gradient(145deg, var(--clay), var(--clay2)); }
.user-txt strong { display:block; font-size:12.5px; font-weight:600; color:#fff; line-height:1.3; }
.user-txt small  { font-size:10px; color:rgba(255,255,255,.42); text-transform:capitalize; }

.btn-keluar {
  display:inline-flex; align-items:center; gap:6px; padding:7px 14px;
  background:rgba(160,90,66,.14); border:1px solid rgba(160,90,66,.28);
  color:rgba(255,255,255,.88); border-radius:9px; font-size:12.5px;
  font-weight:600; text-decoration:none; transition:all .14s; font-family:var(--font);
}
.btn-keluar svg { width:13px; height:13px; }
.btn-keluar:hover { background:var(--clay); color:#fff; border-color:var(--clay); box-shadow:0 3px 12px rgba(160,90,66,.38); }

/* ── HAMBURGER ── */
.hamburger {
  display:none; flex-direction:column; gap:4px; cursor:pointer;
  padding:7px; border:none; background:rgba(255,255,255,.06); border-radius:7px;
}
.hamburger span { width:19px; height:2px; background:rgba(255,255,255,.8); border-radius:2px; transition:all .22s; }
.hamburger.is-open span:nth-child(1) { transform:translateY(6px) rotate(45deg); }
.hamburger.is-open span:nth-child(2) { opacity:0; }
.hamburger.is-open span:nth-child(3) { transform:translateY(-6px) rotate(-45deg); }

/* ── MOBILE MENU ── */
.mobile-menu {
  display:none; position:fixed; top:62px; left:0; right:0;
  background:linear-gradient(180deg, var(--navy3), var(--navy));
  z-index:999; padding:10px; box-shadow:0 12px 32px rgba(0,0,0,.38);
  max-height:calc(100vh - 62px); overflow-y:auto;
  border-top:1px solid rgba(255,255,255,.055);
}
.mobile-menu.show { display:block; animation:dropIn .17s ease; }
.mob-nav {
  display:flex; align-items:center; gap:9px; padding:11px 14px;
  color:rgba(255,255,255,.8); font-size:13.5px; font-weight:500;
  text-decoration:none; border-radius:var(--r); transition:all .13s;
  margin-bottom:2px; font-family:var(--font);
}
.mob-nav svg { width:16px; height:16px; flex-shrink:0; opacity:.82; }
.mob-nav:hover, .mob-nav.active { background:rgba(255,255,255,.09); color:#fff; }
.mob-sep { height:1px; background:rgba(255,255,255,.09); margin:7px 0; }
.mob-section { font-size:10px; font-weight:700; color:rgba(255,255,255,.32); text-transform:uppercase; letter-spacing:1px; padding:7px 14px 3px; }

/* ============================================================
   BREADCRUMB
   ============================================================ */
.gtsis-bc {
  background:rgba(255,253,245,.78);
  backdrop-filter:blur(8px) saturate(160%);
  -webkit-backdrop-filter:blur(8px) saturate(160%);
  border-bottom:1px solid var(--border);
  padding:9px 28px;
  font-size:12px; color:var(--ink3); font-weight:500;
  display:flex; align-items:center; gap:5px;
}
.gtsis-bc span { color:var(--ink); font-weight:600; }

/* ============================================================
   CONTENT WRAPPER
   ============================================================ */
.gtsis-content { padding:24px 28px; }

/* ============================================================
   PAGE HEADER
   ============================================================ */
.page-hd {
  margin-bottom:22px; display:flex; align-items:flex-start;
  justify-content:space-between; flex-wrap:wrap; gap:10px;
}
.page-hd-left h1 {
  font-size:22px; font-weight:800; color:var(--ink);
  margin-bottom:3px; letter-spacing:-0.6px;
}
.page-hd-left p { font-size:13px; color:var(--ink3); margin:0; }

/* ============================================================
   CARD — frosted surface with clay top-bar option
   ============================================================ */
.card-g {
  background:var(--surface);
  backdrop-filter:blur(6px) saturate(140%);
  -webkit-backdrop-filter:blur(6px) saturate(140%);
  border:1px solid var(--border2);
  border-radius:var(--r-xl);
  padding:22px;
  box-shadow:var(--shadow-sm);
  margin-bottom:16px;
  transition:box-shadow .2s;
}
.card-g:hover { box-shadow:var(--shadow-md); }
.card-g .card-title {
  font-size:13.5px; font-weight:700; color:var(--clay2);
  margin-bottom:16px; padding-bottom:11px;
  border-bottom:1px solid var(--border); letter-spacing:-0.2px;
  display:flex; align-items:center; gap:7px;
}

/* ============================================================
   STAT CARD — signature element
   Frosted surface + clay left-accent bar + clean number
   ============================================================ */
.stat-g {
  background:var(--surface);
  backdrop-filter:blur(6px) saturate(140%);
  -webkit-backdrop-filter:blur(6px) saturate(140%);
  border:1px solid var(--border2);
  border-radius:var(--r-xl);
  padding:20px 20px 20px 22px;
  box-shadow:var(--shadow-sm);
  position:relative; overflow:hidden; height:100%;
  transition:transform .18s, box-shadow .18s;
}
.stat-g:hover { transform:translateY(-2px); box-shadow:var(--shadow-md); }

/* Accent bar — left edge */
.stat-acc {
  position:absolute; top:0; left:0;
  width:4px; height:100%;
  border-radius:4px 0 0 4px;
}

/* Subtle shimmer in top-right corner */
.stat-g::after {
  content:''; position:absolute; top:-20px; right:-20px;
  width:80px; height:80px; border-radius:50%;
  background:rgba(255,255,255,.12); pointer-events:none;
}

.stat-lbl {
  font-size:10.5px; font-weight:700; color:var(--ink3);
  text-transform:uppercase; letter-spacing:.9px; margin-bottom:9px;
  font-family:var(--font);
}
.stat-val {
  font-size:28px; font-weight:800; color:var(--ink); line-height:1;
  margin-bottom:5px; font-family:var(--mono); letter-spacing:-1px;
}
.stat-sub { font-size:11.5px; color:var(--ink3); font-weight:400; }
.stat-icon {
  position:absolute; top:18px; right:18px;
  width:36px; height:36px; border-radius:10px;
  display:flex; align-items:center; justify-content:center;
  font-size:17px; opacity:.9;
}
.stat-trend {
  display:inline-flex; align-items:center; gap:3px;
  font-size:11px; margin-top:7px; padding:3px 8px;
  border-radius:20px; font-weight:600;
}
.trend-up   { background:var(--green-bg); color:var(--green); }
.trend-down { background:var(--red-bg);   color:var(--red);   }
.trend-warn { background:var(--amber-bg); color:var(--amber); }

/* ============================================================
   TABLE
   ============================================================ */
.tbl-wrap { overflow-x:auto; border-radius:var(--r); }
.tbl-g { width:100%; border-collapse:separate; border-spacing:0; font-size:13px; }
.tbl-g thead th {
  background:var(--surface-solid);
  font-size:12px; font-weight:700; color:var(--ink);
  text-transform:uppercase; letter-spacing:.6px;
  border-bottom:2px solid var(--border2);
  padding:12px 14px; text-align:left; white-space:nowrap;
  font-family:'Georgia', 'Times New Roman', serif;
  position:sticky; top:0; z-index:2;
}
.tbl-g tbody td {
  padding:13px 14px; color:var(--ink2);
  border-bottom:1px solid rgba(200,185,160,.14);
  vertical-align:middle; font-weight:400;
}
.tbl-g tbody td .bold { color:var(--ink); font-weight:600; }
.tbl-g tbody tr { transition:background .11s; }
.tbl-g tbody tr:hover td { background:rgba(160,90,66,.04); }
.tbl-g tbody tr:last-child td { border-bottom:none; }

/* ============================================================
   BADGE
   ============================================================ */
.bg-g {
  display:inline-flex; align-items:center; padding:3px 9px;
  border-radius:20px; font-size:11px; font-weight:600;
  white-space:nowrap; font-family:var(--font); letter-spacing:.15px;
}
.bg-navy  { background:rgba(28,45,79,.08);  color:var(--navy2); }
.bg-red   { background:var(--red-bg);        color:var(--red);   border:1px solid var(--red-border); }
.bg-green { background:var(--green-bg);      color:var(--green); border:1px solid var(--green-border); }
.bg-amber { background:var(--amber-bg);      color:var(--amber); border:1px solid var(--amber-border); }
.bg-gray  { background:rgba(100,116,139,.08);color:var(--ink3);  }
.bg-blue  { background:var(--blue-bg);       color:var(--blue);  border:1px solid var(--blue-border); }
.bg-clay  { background:var(--clay-bg2);      color:var(--clay2); border:1px solid var(--clay-border); }
.bg-teal  { background:rgba(13,148,136,.09); color:#0f766e;      }

/* ============================================================
   BUTTON
   ============================================================ */
.btn-g {
  display:inline-flex; align-items:center; gap:6px; padding:9px 18px;
  border-radius:var(--r); font-size:13px; font-weight:600; cursor:pointer;
  border:none; transition:transform .11s, box-shadow .14s, background .13s;
  font-family:var(--font); text-decoration:none; letter-spacing:0.1px;
}
.btn-g:active { transform:translateY(1px) scale(.99); }

.btn-primary-g {
  background:linear-gradient(150deg, var(--clay3), var(--clay));
  color:#fff; box-shadow:0 3px 10px rgba(160,90,66,.3);
}
.btn-primary-g:hover { background:linear-gradient(150deg, var(--clay), var(--clay2)); color:#fff; box-shadow:0 5px 16px rgba(160,90,66,.38); }

.btn-navy-g {
  background:linear-gradient(150deg, var(--navy), var(--navy2));
  color:#fff; box-shadow:0 3px 10px rgba(28,45,79,.28);
}
.btn-navy-g:hover { box-shadow:0 5px 16px rgba(28,45,79,.36); color:#fff; }

.btn-blue-g {
  background:linear-gradient(150deg, #3b82f6, var(--blue));
  color:#fff; box-shadow:0 3px 10px rgba(37,99,235,.28);
}
.btn-blue-g:hover { color:#fff; box-shadow:0 5px 14px rgba(37,99,235,.36); }

.btn-out-g {
  background:var(--surface-solid); color:var(--ink);
  border:1px solid var(--border2);
}
.btn-out-g:hover { border-color:var(--clay-border); background:var(--clay-bg); color:var(--clay2); }

.btn-sm-g  { padding:6px 12px; font-size:12px; }
.btn-danger-g {
  background:linear-gradient(150deg, #ef4444, var(--red));
  color:#fff; box-shadow:0 3px 10px rgba(220,38,38,.28);
}
.btn-danger-g:hover { color:#fff; box-shadow:0 5px 16px rgba(220,38,38,.36); }

/* ============================================================
   FORM
   ============================================================ */
.form-lbl {
  font-size:11px; font-weight:700; color:var(--ink2);
  text-transform:uppercase; letter-spacing:.55px;
  margin-bottom:5px; display:block; font-family:var(--font);
}
.form-inp {
  width:100%; padding:9.5px 12px;
  border:1.5px solid var(--border2); border-radius:var(--r);
  font-size:13px; color:var(--ink); background:#fff;
  outline:none; transition:border .18s, box-shadow .18s; font-family:var(--font);
}
.form-inp:focus { border-color:var(--clay3); box-shadow:0 0 0 3px rgba(160,90,66,.12); }
.form-inp::placeholder { color:var(--ink4); }
.form-inp.is-valid   { border-color:var(--green); }
.form-inp.is-invalid { border-color:var(--red); }
.form-hint     { font-size:11px; color:var(--ink3); margin-top:4px; }
.form-hint.hint-ok  { color:var(--green); }
.form-hint.hint-err { color:var(--red); }

/* ============================================================
   ALERT / FLASH
   ============================================================ */
.flash-ok {
  background:var(--green-bg); border:1px solid var(--green-border);
  color:var(--green); padding:12px 15px; border-radius:var(--r);
  margin-bottom:14px; font-size:13px; font-weight:500;
  display:flex; align-items:center; gap:8px;
}
.flash-err {
  background:var(--red-bg); border:1px solid var(--red-border);
  color:var(--red); padding:12px 15px; border-radius:var(--r);
  margin-bottom:14px; font-size:13px; font-weight:500;
  display:flex; align-items:center; gap:8px;
}
.alert-warn { background:var(--amber-bg); border:1px solid var(--amber-border); color:var(--amber); padding:11px 13px; border-radius:var(--r); margin-bottom:12px; font-size:13px; display:flex; align-items:flex-start; gap:7px; }
.alert-info { background:var(--blue-bg);  border:1px solid var(--blue-border);  color:var(--blue);  padding:11px 13px; border-radius:var(--r); margin-bottom:12px; font-size:13px; display:flex; align-items:flex-start; gap:7px; }
.alert-ok   { background:var(--green-bg); border:1px solid var(--green-border); color:var(--green); padding:11px 13px; border-radius:var(--r); margin-bottom:12px; font-size:13px; display:flex; align-items:flex-start; gap:7px; }
.alert-err  { background:var(--red-bg);   border:1px solid var(--red-border);   color:var(--red);   padding:11px 13px; border-radius:var(--r); margin-bottom:12px; font-size:13px; display:flex; align-items:flex-start; gap:7px; }

/* ============================================================
   SEARCH BAR
   ============================================================ */
.search-bar {
  display:flex; align-items:center; gap:8px;
  background:#fff; border:1.5px solid var(--border2);
  border-radius:var(--r); padding:8px 13px;
  transition:border .18s, box-shadow .18s; margin-bottom:14px;
}
.search-bar:focus-within { border-color:var(--clay3); box-shadow:0 0 0 3px rgba(160,90,66,.1); }
.search-bar input { background:none; border:none; font-size:13px; color:var(--ink); flex:1; outline:none; font-family:var(--font); }
.search-bar input::placeholder { color:var(--ink4); }
.search-icon { color:var(--ink4); }

/* ============================================================
   TABS
   ============================================================ */
.tabs-g { display:flex; gap:2px; border-bottom:1.5px solid var(--border); margin-bottom:18px; }
.tab-g {
  padding:9px 16px; font-size:13px; font-weight:600; color:var(--ink3);
  cursor:pointer; border-bottom:2.5px solid transparent; margin-bottom:-1.5px;
  transition:all .14s; text-decoration:none; font-family:var(--font);
  background:none; border-top:none; border-left:none; border-right:none; border-radius:7px 7px 0 0;
}
.tab-g:hover { color:var(--ink); background:var(--clay-bg); }
.tab-g.active { color:var(--clay2); border-bottom-color:var(--clay); }

/* ============================================================
   MISC UTILITIES
   ============================================================ */
.sep-g { height:1px; background:var(--border); margin:14px 0; }
.sh-g { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:7px; }
.sh-g h2 { font-size:13.5px; font-weight:700; color:var(--ink); margin:0; }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width:992px) {
  .gtsis-menu { display:none; }
  .hamburger  { display:flex; margin-left:auto; }
  .gtsis-right { gap:5px; }
  .user-txt { display:none; }
  .user-chip { padding:5px; }
  .gtsis-content { padding:16px; }
  .gtsis-bc { padding:8px 16px; }
  .gtsis-navbar { padding:0 16px; }
}
@media (max-width:768px) {
  .page-hd { flex-direction:column; gap:7px; }
  .page-hd-left h1 { font-size:18px; }
  .stat-val { font-size:24px; }
  .card-g { padding:14px; }
  .card-g .card-title { margin-bottom:12px; padding-bottom:9px; }
  .tbl-g thead th, .tbl-g tbody td { padding:9px 11px; font-size:12px; }
  .btn-g { padding:8px 14px; font-size:12px; }
  .gtsis-navbar { height:56px; padding:0 12px; }
  .gtsis-brand img { height:24px; }
  .gtsis-brand .brand-name { font-size:14px; }
  .gtsis-brand .brand-sub { display:none; }
  .nav-div { margin:0 8px; }
  .mobile-menu { top:56px; max-height:calc(100vh - 56px); }
}
@media (max-width:480px) {
  .gtsis-content { padding:12px; }
  .stat-g { padding:15px 15px 15px 19px; }
  .stat-val { font-size:22px; }
  .btn-keluar span { display:none; }
  .btn-keluar { padding:7px 9px; }
  .gtsis-bc { padding:7px 12px; font-size:11px; }
  .page-hd-left h1 { font-size:16.5px; }
  .page-hd-left p { font-size:12px; }
  .card-g { padding:12px; border-radius:12px; }
  .stat-g { border-radius:12px; }
  .tbl-g thead th, .tbl-g tbody td { padding:8px 9px; font-size:11.5px; }
  .btn-g { width:100%; justify-content:center; }
  .gtsis-brand .brand-name { font-size:13px; }
  .modal-box { max-width:100% !important; margin:0 10px; }
}
@media (max-width:360px) {
  .gtsis-brand .brand-name { display:none; }
  .stat-val { font-size:20px; }
}

/* ═══════════════════════════════════════════════════════
   MODAL ALERT GLOBAL (pengganti window.alert bawaan browser)
═══════════════════════════════════════════════════════ */
.ga-overlay {
  position:fixed; inset:0;
  background:rgba(15,32,68,.55);
  backdrop-filter:blur(3px); -webkit-backdrop-filter:blur(3px);
  z-index:9999;
  display:flex; align-items:center; justify-content:center;
  padding:1rem;
  opacity:0; pointer-events:none;
  transition:opacity .15s;
}
.ga-overlay.show { opacity:1; pointer-events:auto; }
.ga-box {
  background:#fff; border-radius:18px;
  width:100%; max-width:400px;
  box-shadow:0 24px 64px rgba(0,0,0,.22);
  transform:scale(.94) translateY(12px);
  transition:transform .18s cubic-bezier(.34,1.56,.64,1);
}
.ga-overlay.show .ga-box { transform:scale(1) translateY(0); }
.ga-head {
  color:#1f2937; padding:.9rem 1.2rem; border-radius:18px 18px 0 0;
  display:flex; align-items:center; gap:12px;
  background:#ffffff; border-bottom:1px solid #eef0f5;
}
.ga-head.ga-info    { color:var(--navy, #1e3a5f); }
.ga-head.ga-success { color:#16a34a; }
.ga-head.ga-error   { color:#ef4444; }
.ga-icon {
  width:34px; height:34px; background:rgba(0,0,0,.05);
  border-radius:10px; display:flex; align-items:center; justify-content:center;
  font-size:1rem; flex-shrink:0;
}
.ga-head.ga-info    .ga-icon { background:rgba(30,58,95,.10); }
.ga-head.ga-success .ga-icon { background:rgba(22,163,74,.12); }
.ga-head.ga-error   .ga-icon { background:rgba(239,68,68,.12); }
.ga-title { margin:0; font-weight:700; font-size:.92rem; }
.ga-body { padding:1.3rem 1.3rem 1.1rem; }
.ga-msg  { margin:0; font-size:.9rem; color:#374151; line-height:1.55; white-space:pre-line; }
.ga-actions { display:flex; justify-content:center; margin-top:1.2rem; }
.ga-btn-ok {
  background:var(--navy, #1e3a5f); color:#fff; border:none;
  padding:9px 28px; border-radius:10px; font-size:.85rem; font-weight:600;
  cursor:pointer; transition:.15s;
}
.ga-btn-ok:hover { box-shadow:0 5px 16px rgba(28,45,79,.36); }

/* RakPicker — komponen input lokasi rak terstruktur */
.rakpicker-box{border:1.5px solid var(--border);border-radius:10px;padding:.7rem;background:#f8f9fc;margin-top:.3rem}
.rakpicker-liminfo{font-size:.75rem;color:#6b7280;align-self:center;padding-top:1.2rem}
.rakpicker-newbox{margin-top:.5rem;padding:.6rem;background:#fff;border:1.5px dashed var(--navy);border-radius:8px}
.rakpicker-expand-row{display:flex;gap:.4rem;margin-top:.4rem}
.rakpicker-preview{margin-top:.4rem;font-size:.8rem;font-weight:700;color:var(--navy);background:#eef1fb;border-radius:6px;padding:.4rem .6rem}
</style>
<!-- Pengingat "ada data belum disimpan" — berlaku global di semua halaman & role -->
<script src="/js/unsaved-guard.js"></script>
</head>
<body>

<!-- NAVBAR DESKTOP -->
<nav class="gtsis-navbar">
  <a class="gtsis-brand" href="/dashboard">
    <img src="/img/sasa.png" alt="Sasa">
    <div>
      <span class="brand-name">Gudang Teknik</span>
    </div>
  </a>
  <div class="nav-div"></div>

  <!-- MENU DESKTOP -->
  <div class="gtsis-menu">
    <a href="/dashboard" class="gnav <?= (uri_string()==='dashboard')?'active':'' ?>">Dashboard</a>
    <a href="/monitoring" class="gnav <?= (uri_string()==='monitoring')?'active':'' ?>">Monitoring Stok</a>

    <?php if(in_array(session()->get('role'),['petugas_gt'])): ?>
    <a href="/penerimaan" class="gnav <?= (uri_string()==='penerimaan')?'active':'' ?>">Penerimaan</a>
    <a href="/pengeluaran" class="gnav <?= (uri_string()==='pengeluaran')?'active':'' ?>">Pengeluaran</a>
    <a href="/mapping" class="gnav <?= (uri_string()==='mapping')?'active':'' ?>">Mapping Rak</a>
    <a href="/laporan" class="gnav <?= (uri_string()==='laporan')?'active':'' ?>">Laporan</a>
    <?php endif; ?>

    <?php if(session()->get('role')==='admin_gt'): ?>
    <a href="/penerimaan" class="gnav <?= (uri_string()==='penerimaan')?'active':'' ?>">Penerimaan</a>
    <a href="/pengeluaran" class="gnav <?= (uri_string()==='pengeluaran')?'active':'' ?>">Pengeluaran</a>
    <a href="/verifikasi-booking" class="gnav <?= (uri_string()==='verifikasi-booking')?'active':'' ?>">Verifikasi Booking</a>
    <a href="/laporan" class="gnav <?= (uri_string()==='laporan')?'active':'' ?>">Laporan</a>
    <div class="gdrop">
      <button class="gnav" type="button">
        Master Data
        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="gdrop-menu">
        <a href="/user" class="gdrop-item <?= (uri_string()==='user')?'active':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Manajemen User
        </a>
        <a href="/mapping" class="gdrop-item <?= (uri_string()==='mapping')?'active':'' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
          Mapping Rak
        </a>
      </div>
    </div>
    <?php endif; ?>

    <?php if(session()->get('role')==='plant'): ?>
    <a href="/booking" class="gnav <?= (uri_string()==='booking')?'active':'' ?>">Booking Material</a>
    <?php endif; ?>

    <?php if(session()->get('role') !== 'plant'): ?>
    <a href="/profil" class="gnav <?= (str_starts_with(uri_string(),'profil'))?'active':'' ?>">Profil</a>
    <?php endif; ?>
  </div>

  <!-- RIGHT DESKTOP -->
  <div class="gtsis-right">
    <div class="user-chip">
      <?php
        $role = session()->get('role');
        $avatarClass = $role==='admin_gt' ? 'ua-admin' : ($role==='petugas_gt' ? 'ua-petugas' : 'ua-plant');
        $initial = strtoupper(substr(session()->get('nama') ?? 'U', 0, 1));
      ?>
      <div class="user-avatar <?= $avatarClass ?>"><?= $initial ?></div>
      <div class="user-txt">
        <strong><?= esc(session()->get('nama')) ?></strong>
        <small><?= esc($role) ?></small>
      </div>
    </div>
    <a href="/logout" class="btn-keluar">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      <span>Keluar</span>
    </a>
  </div>

  <!-- HAMBURGER MOBILE -->
  <button class="hamburger" onclick="toggleMobileMenu()" id="hamburger-btn" type="button" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobile-menu">
  <a href="/dashboard" class="mob-nav <?= (uri_string()==='dashboard')?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Dashboard
  </a>
  <a href="/monitoring" class="mob-nav <?= (uri_string()==='monitoring')?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
    Monitoring Stok
  </a>

  <?php if(in_array(session()->get('role'),['admin_gt','petugas_gt'])): ?>
  <div class="mob-sep"></div>
  <div class="mob-section">Transaksi</div>
  <a href="/penerimaan" class="mob-nav">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
    Penerimaan
  </a>
  <a href="/pengeluaran" class="mob-nav">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"/></svg>
    Pengeluaran
  </a>
  <a href="/laporan" class="mob-nav">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    Laporan
  </a>
  <?php endif; ?>

  <?php if(session()->get('role')==='petugas_gt'): ?>
  <a href="/mapping" class="mob-nav">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
    Mapping Rak
  </a>
  <?php endif; ?>

  <?php if(session()->get('role')==='admin_gt'): ?>
  <a href="/verifikasi-booking" class="mob-nav">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    Verifikasi Booking
  </a>
  <div class="mob-sep"></div>
  <div class="mob-section">Master Data</div>
  <a href="/user" class="mob-nav">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    Manajemen User
  </a>
  <a href="/mapping" class="mob-nav">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
    Mapping Rak
  </a>
  <?php endif; ?>

  <?php if(session()->get('role')==='plant'): ?>
  <div class="mob-sep"></div>
  <a href="/booking" class="mob-nav <?= (uri_string()==='booking')?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
    Booking Material
  </a>
  <?php endif; ?>

  <?php if(session()->get('role') !== 'plant'): ?>
  <div class="mob-sep"></div>
  <a href="/profil" class="mob-nav <?= (str_starts_with(uri_string(),'profil'))?'active':'' ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    Profil Saya
  </a>
  <?php endif; ?>

  <div class="mob-sep"></div>
  <a href="/logout" class="mob-nav" style="color:rgba(255,160,130,.9);">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    Keluar
  </a>
</div>

<!-- BREADCRUMB -->
<div class="gtsis-bc">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="12" height="12" style="opacity:.65"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
  Gudang Teknik &rsaquo; <span><?= $title ?? 'Dashboard' ?></span>
</div>

<!-- FLASH MESSAGE -->
<div class="gtsis-content" style="padding-bottom:0; padding-top:0;">
  <?php if(session()->getFlashdata('success')): ?>
    <div class="flash-ok" style="margin-top:14px;">✓ <?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>
  <?php if(session()->getFlashdata('error')): ?>
    <div class="flash-err" style="margin-top:14px;">✕ <?= session()->getFlashdata('error') ?></div>
  <?php endif; ?>
</div>

<!-- CONTENT -->
<div class="gtsis-content">
  <?= $this->renderSection('content') ?>
</div>

<script>
// ===== MOBILE MENU =====
function toggleMobileMenu() {
  const menu = document.getElementById('mobile-menu');
  const btn  = document.getElementById('hamburger-btn');
  menu.classList.toggle('show');
  btn.classList.toggle('is-open');
}
document.addEventListener('click', function(e) {
  const menu = document.getElementById('mobile-menu');
  const btn  = document.getElementById('hamburger-btn');
  if (menu && menu.classList.contains('show') && !menu.contains(e.target) && !btn.contains(e.target)) {
    menu.classList.remove('show');
    btn.classList.remove('is-open');
  }
});

// ===== DROPDOWN MASTER DATA =====
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.gdrop').forEach(function(drop) {
    const btn  = drop.querySelector('button.gnav');
    const menu = drop.querySelector('.gdrop-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const isOpen = menu.style.display === 'block';
      document.querySelectorAll('.gdrop-menu').forEach(function(m) { m.style.display = 'none'; });
      document.querySelectorAll('.gdrop').forEach(function(d)       { d.classList.remove('open'); });
      menu.style.display = isOpen ? 'none' : 'block';
      drop.classList.toggle('open', !isOpen);
    });
  });
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.gdrop')) {
      document.querySelectorAll('.gdrop-menu').forEach(function(m) { m.style.display = 'none'; });
      document.querySelectorAll('.gdrop').forEach(function(d)       { d.classList.remove('open'); });
    }
  });
});
</script>

<!-- ═══════════════════════════════════════════════════════
     MODAL ALERT GLOBAL — pengganti window.alert() bawaan browser
     Semua pemanggilan alert('...') di seluruh halaman otomatis
     memakai modal ini, tanpa perlu ubah kode tiap menu.
═══════════════════════════════════════════════════════ -->
<div id="ga-overlay" class="ga-overlay">
  <div class="ga-box">
    <div id="ga-head" class="ga-head ga-info">
      <div class="ga-icon" id="ga-icon">ℹ️</div>
      <h6 class="ga-title" id="ga-title">Informasi</h6>
    </div>
    <div class="ga-body">
      <p class="ga-msg" id="ga-msg"></p>
      <div class="ga-actions">
        <button class="ga-btn-ok" id="ga-btn-ok">OK</button>
      </div>
    </div>
  </div>
</div>
<script>
(function() {
  var overlay = document.getElementById('ga-overlay');
  var head    = document.getElementById('ga-head');
  var icon    = document.getElementById('ga-icon');
  var title   = document.getElementById('ga-title');
  var msgEl   = document.getElementById('ga-msg');
  var btnOk   = document.getElementById('ga-btn-ok');
  var resolveFn = null;

  function detectType(message) {
    if (message.indexOf('✅') !== -1) return 'success';
    if (message.indexOf('❌') !== -1 || /\bgagal\b/i.test(message)) return 'error';
    return 'info';
  }

  function close() {
    overlay.classList.remove('show');
    if (resolveFn) { var r = resolveFn; resolveFn = null; r(); }
  }

  window.showAlert = function(message, forceType) {
    message = String(message).replace(/✅|❌/g, '').trim();
    var type = forceType || detectType(String(message));

    head.className = 'ga-head ga-' + type;
    icon.textContent  = type === 'success' ? '✅' : (type === 'error' ? '⚠️' : 'ℹ️');
    title.textContent = type === 'success' ? 'Berhasil' : (type === 'error' ? 'Gagal' : 'Informasi');
    msgEl.textContent  = message;

    overlay.classList.add('show');

    return new Promise(function(resolve) { resolveFn = resolve; });
  };

  btnOk.addEventListener('click', close);
  overlay.addEventListener('click', function(e) { if (e.target === overlay) close(); });

  // Timpa window.alert bawaan browser supaya semua alert('...') yang
  // sudah ada di kode (booking, penerimaan, pengeluaran, laporan, dll)
  // otomatis tampil sebagai modal ini, bukan popup "localhost:8080 says".
  window.alert = function(message) { window.showAlert(message); };
})();
</script>

</body>
</html>