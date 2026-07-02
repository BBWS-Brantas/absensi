<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="description" content="Sistem Monitoring Presensi" />
    <title>BBWS | Present</title>
    <link rel="icon" type="image/png" href="<?= base_url('../assets/img/company/new-logo.png') ?>">

    <!-- Critical CSS: render the login card immediately, without waiting for tabler.min.css (530 KB) -->
    <style>
        *,*::before,*::after{box-sizing:border-box}
        html{font-size:16px}
        body{margin:0;font-family:-apple-system,BlinkMacSystemFont,'San Francisco','Segoe UI',Roboto,'Helvetica Neue',sans-serif;font-size:.9375rem;font-weight:400;line-height:1.5;color:#182433;background-color:#f6f8fb;font-feature-settings:"cv03","cv04","cv11"}
        [data-bs-theme=dark] body{color:#fcfdfe;background-color:#040a11}
        .d-flex{display:flex!important}.flex-column{flex-direction:column!important}
        .justify-content-center{justify-content:center!important}.align-items-center{align-items:center!important}
        .py-4{padding-top:1.5rem!important;padding-bottom:1.5rem!important}
        .mb-0{margin-bottom:0!important}.mb-2{margin-bottom:.5rem!important}.mb-3{margin-bottom:1rem!important}.mb-4{margin-bottom:1.5rem!important}
        .w-100{width:100%!important}.text-center{text-align:center!important}
        .text-muted{color:#616876!important}
        [data-bs-theme=dark] .text-muted{color:#a0acb8!important}
        .page{display:flex;flex-direction:column;min-height:100vh}
        .page-center{justify-content:center;align-items:center}
        .container{width:100%;padding-right:1rem;padding-left:1rem;margin-right:auto;margin-left:auto}
        .container-tight{max-width:30rem!important}
        .navbar-brand{display:inline-flex;align-items:center;gap:.75rem;font-weight:700;text-decoration:none;color:inherit}
        h1{font-size:1.5rem;margin:0}
        .h2{font-size:1.3125rem;font-weight:600;line-height:1.2;margin-top:0;margin-bottom:.5rem}
        .card{position:relative;display:flex;flex-direction:column;background-color:#fff;border:1px solid #dadfe5;border-radius:4px}
        [data-bs-theme=dark] .card{background-color:#1a2332;border-color:#1f2e41}
        .card-body{flex:1 1 auto;padding:1.25rem}
        .card-md .card-body{padding:2rem}
        .form-label{display:block;margin-bottom:.375rem;font-size:.875rem;font-weight:500}
        .form-control{display:block;width:100%;padding:.4375rem .75rem;font-size:.9375rem;font-weight:400;line-height:1.5;color:#182433;background-color:#fff;border:1px solid #c8cdd2;border-radius:4px;appearance:none;transition:border-color .15s ease-in-out,box-shadow .15s ease-in-out}
        [data-bs-theme=dark] .form-control{color:#fcfdfe;background-color:#1a2332;border-color:#1f2e41}
        .form-control:focus{border-color:#90b5d9;outline:0;box-shadow:0 0 0 .25rem rgba(32,107,196,.25)}
        .form-control.is-invalid{border-color:#d63939}
        .invalid-feedback{display:none;width:100%;margin-top:.25rem;font-size:.875em;color:#d63939}
        .is-invalid~.invalid-feedback,.is-invalid+.invalid-feedback{display:block}
        .form-footer{margin-top:1.25rem}
        .input-group{position:relative;display:flex;flex-wrap:wrap;align-items:stretch;width:100%}
        .input-group>.form-control{flex:1 1 auto;width:1%;min-width:0}
        .input-group-flat>.form-control{border-top-right-radius:0;border-bottom-right-radius:0;border-right:0}
        .input-group-text{display:flex;align-items:center;padding:.4375rem .75rem;font-size:.9375rem;line-height:1.5;color:#616876;background-color:#f6f8fb;border:1px solid #c8cdd2;border-left:0;border-radius:4px;border-top-left-radius:0;border-bottom-left-radius:0}
        [data-bs-theme=dark] .input-group-text{color:#a0acb8;background-color:#0d1626;border-color:#1f2e41}
        .btn{display:inline-flex;align-items:center;justify-content:center;font-size:.875rem;font-weight:500;line-height:1.4285714;padding:.4375rem 1rem;border:1px solid transparent;border-radius:4px;cursor:pointer;user-select:none;text-decoration:none;transition:color .15s,background-color .15s,border-color .15s}
        .btn-primary{color:#fff;background-color:#206bc4;border-color:#206bc4}
        .btn-primary:hover{color:#fff;background-color:#1b5aa4;border-color:#195299}
        .alert{padding:.75rem 1rem;margin-bottom:1rem;border:1px solid transparent;border-radius:4px}
        .alert-danger{color:#a11f1f;background-color:#fce8e8;border-color:#f5c2c2}
        .alert-success{color:#1d6a3a;background-color:#d1f0dd;border-color:#b1e1c2}
        [data-bs-theme=dark] .alert-danger{color:#f07070;background-color:#2c1010;border-color:#5c2020}
        [data-bs-theme=dark] .alert-success{color:#5fd38d;background-color:#0d2c1c;border-color:#1c5a38}
    </style>

    <!-- Non-blocking load of full Tabler CSS — applied after FCP, so no render-blocking penalty -->
    <link rel="preload" href="<?= base_url('../assets/css/tabler.min.css?1684106062') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="<?= base_url('../assets/css/demo.min.css?1684106062') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="<?= base_url('../assets/css/tabler.min.css?1684106062') ?>" rel="stylesheet">
        <link href="<?= base_url('../assets/css/demo.min.css?1684106062') ?>" rel="stylesheet">
    </noscript>
</head>

<body class="d-flex flex-column">
    <!-- Inlined: sets dark/light theme from localStorage before first paint (687 B, must be synchronous) -->
    <script>!function(e){"function"==typeof define&&define.amd?define(e):e()}((function(){"use strict";var e,t="tablerTheme",a=new Proxy(new URLSearchParams(window.location.search),{get:function(e,t){return e.get(t)}});if(a.theme)localStorage.setItem(t,a.theme),e=a.theme;else{var n=localStorage.getItem(t);e=n||"light"}"dark"===e?document.body.setAttribute("data-bs-theme",e):document.body.removeAttribute("data-bs-theme")}));</script>

    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="container mb-4 d-flex justify-content-center">
                <a href="<?= base_url() ?>" class="navbar-brand navbar-brand-autodark align-items-center">
                    <img src="<?= base_url('../assets/img/company/new-logo.png') ?>" width="75" height="75" alt="O-Present" fetchpriority="high">
                    <span>
                        <h1 class="mb-0">SIMPATI</h1>
                        <p class="text-muted mb-0" style="font-size: 0.80rem;">Sistem Monitoring Presensi TPM dan KMB</p>
                    </span>
                </a>
            </div>
            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4"><?= lang('Auth.loginTitle') ?></h2>

                    <?= view('Myth\Auth\Views\_message_block') ?>

                    <form action="<?= url_to('login') ?>" method="post" autocomplete="off" novalidate>
                        <?= csrf_field() ?>

                        <?php if ($config->validFields === ['email']) : ?>
                            <div class="mb-3">
                                <label class="form-label"><?= lang('Auth.email') ?></label>
                                <input name="login" type="email" class="form-control <?php if (session('errors.login')) : ?>is-invalid<?php endif ?>" placeholder="<?= lang('Auth.email') ?>" autocomplete="off">
                                <div class="invalid-feedback">
                                    <?= session('errors.login') ?>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="mb-3">
                                <label class="form-label"><?= lang('Auth.emailOrUsername') ?></label>
                                <input type="email" class="form-control <?php if (session('errors.login')) : ?>is-invalid<?php endif ?>" name="login" placeholder="<?= lang('Auth.emailOrUsername') ?>" autocomplete="off">
                                <div class="invalid-feedback">
                                    <?= session('errors.login') ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-2">
                            <label class="form-label">
                                <?= lang('Auth.password') ?>
                                <?php if ($config->activeResetter) : ?>
                                    <span class="form-label-description">
                                        <!-- <a href="<?= url_to('forgot') ?>"><?= lang('Auth.forgotYourPassword') ?></a> -->
                                    </span>
                                <?php endif; ?>
                            </label>
                            <div class="input-group input-group-flat">
                                <input id="password" name="password" type="password" class="form-control <?php if (session('errors.password')) : ?>is-invalid<?php endif ?>" placeholder="<?= lang('Auth.password') ?>" autocomplete="off">
                                <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword()">
                                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </span>
                                <div class="invalid-feedback">
                                    <?= session('errors.password') ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100"><?= lang('Auth.loginAction') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('../assets/js/tabler.min.js?1684106062') ?>" defer></script>
    <script src="<?= base_url('../assets/js/demo.min.js?1684106062') ?>" defer></script>
    <script>
        function togglePassword() {
            var input = document.getElementById('password');
            var eyeIcon = document.getElementById('eye-icon');
            var eyeOffIcon = document.getElementById('eye-off-icon');
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = '';
            } else {
                input.type = 'password';
                eyeIcon.style.display = '';
                eyeOffIcon.style.display = 'none';
            }
        }

        (function () {
            var loginInput = document.querySelector('input[name="login"]');
            var form = document.querySelector('form');
            if (!loginInput || !form) return;

            var EMAIL_CHARS = /[^a-zA-Z0-9._%+\-@]/g;
            var EMAIL_FORMAT = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;

            loginInput.addEventListener('input', function () {
                var start = this.selectionStart;
                var before = this.value;
                var after = before.replace(EMAIL_CHARS, '');
                if (after !== before) {
                    var removed = before.length - after.length;
                    this.value = after;
                    this.setSelectionRange(Math.max(0, start - removed), Math.max(0, start - removed));
                }

                if (this.dataset.clientError && EMAIL_FORMAT.test(this.value.trim())) {
                    this.classList.remove('is-invalid');
                    var fb = this.parentElement.querySelector('.invalid-feedback');
                    if (fb) fb.textContent = '';
                    delete this.dataset.clientError;
                }
            });

            form.addEventListener('submit', function (e) {
                var val = loginInput.value.trim();
                if (!EMAIL_FORMAT.test(val)) {
                    e.preventDefault();
                    loginInput.classList.add('is-invalid');
                    loginInput.dataset.clientError = '1';
                    var fb = loginInput.parentElement.querySelector('.invalid-feedback');
                    if (fb) fb.textContent = val === '' ? 'Email wajib diisi.' : 'Format email tidak valid.';
                    loginInput.focus();
                }
            });
        })();
    </script>
</body>

</html>
