<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Gudang Teknik</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
  }

  /* Background foto */
  .bg-image {
    position: fixed;
    inset: 0;
    background: url('/img/bg-login.png') center center / cover no-repeat;
    z-index: 0;
  }

  /* Overlay gelap supaya card terbaca */
  .bg-overlay {
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg, rgba(10,20,50,.62) 0%, rgba(10,20,50,.45) 100%);
    z-index: 1;
  }

  /* Wrapper card */
  .login-wrap {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 420px;
    padding: 1rem;
  }

  .login-card {
    background: rgba(255,255,255,.97);
    border-radius: 20px;
    box-shadow: 0 24px 64px rgba(0,0,0,.35), 0 4px 16px rgba(0,0,0,.15);
    padding: 2.2rem 2rem;
    backdrop-filter: blur(8px);
  }

  /* Logo area */
  .logo-area {
    text-align: center;
    margin-bottom: 1.6rem;
  }
  .logo-area img {
    height: 72px;
    margin-bottom: .6rem;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,.12));
  }
  .logo-area .company {
    font-size: .95rem;
    font-weight: 700;
    color: #1a2744;
    line-height: 1.3;
  }
  .logo-area .subtitle {
    font-size: .75rem;
    color: #8d9ab5;
    margin-top: 2px;
  }

  /* Divider */
  .divider {
    height: 1px;
    background: #e8e8f0;
    margin: 1.2rem 0;
  }

  /* Form */
  .form-label {
    font-size: .78rem;
    font-weight: 700;
    color: #1a2744;
    margin-bottom: 5px;
    display: block;
  }
  .form-input {
    width: 100%;
    border: 1.5px solid #dde1ec;
    border-radius: 10px;
    padding: .6rem .9rem;
    font-size: .88rem;
    font-family: inherit;
    color: #1a2744;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    background: #f8f9fc;
  }
  .form-input:focus {
    border-color: #1a2744;
    box-shadow: 0 0 0 3px rgba(26,39,68,.09);
    background: #fff;
  }
  .form-group { margin-bottom: 1rem; }

  /* Password wrapper */
  .pw-wrap { position: relative; }
  .pw-wrap .form-input { padding-right: 2.6rem; }
  .pw-toggle {
    position: absolute;
    right: .75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #8d9ab5;
    font-size: 1rem;
    line-height: 1;
    padding: 0;
  }
  .pw-toggle:hover { color: #1a2744; }

  /* Error alert */
  .alert-error {
    background: #fff5f5;
    border: 1.5px solid #fecaca;
    color: #b91c1c;
    border-radius: 10px;
    padding: .6rem .9rem;
    font-size: .8rem;
    font-weight: 600;
    margin-bottom: 1rem;
  }

  /* Submit btn */
  .btn-login {
    width: 100%;
    background: #1a2744;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: .72rem;
    font-size: .9rem;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background .2s, transform .1s;
    margin-top: .4rem;
  }
  .btn-login:hover { background: #243460; }
  .btn-login:active { transform: scale(.98); }

  /* Footer hint */
  .login-footer {
    text-align: center;
    margin-top: 1.2rem;
    font-size: .72rem;
    color: #9ca3af;
  }

  /* ── Responsive ── */
  @media (max-width: 480px) {
    .login-card { padding: 1.6rem 1.2rem; border-radius: 16px; }
    .logo-area img { height: 56px; }
  }
</style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="login-wrap">
  <div class="login-card">

    <div class="logo-area">
      <img src="/img/sasa.png" alt="Sasa">
      <div class="company">PT Sasa Inti Gending</div>
      <div class="subtitle">Sistem Informasi Gudang Teknik</div>
    </div>

    <div class="divider"></div>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert-error">⚠️ <?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="/login" method="post">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-input"
               placeholder="Masukkan username" required autofocus
               value="<?= old('username') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="pw-wrap">
          <input type="password" name="password" id="pw-input" class="form-input"
                 placeholder="Masukkan password" required>
          <button type="button" class="pw-toggle" onclick="togglePw()" id="pw-eye">👁</button>
        </div>
      </div>

      <button type="submit" class="btn-login">Masuk</button>
    </form>

    <div class="login-footer">
      Gudang Teknik &mdash; PT Sasa Inti Gending
    </div>

  </div>
</div>

<script>
function togglePw() {
  var inp = document.getElementById('pw-input');
  var btn = document.getElementById('pw-eye');
  if (inp.type === 'password') {
    inp.type = 'text';
    btn.textContent = '🙈';
  } else {
    inp.type = 'password';
    btn.textContent = '👁';
  }
}
</script>
</body>
</html>