<?php

use PhpOffice\PhpSpreadsheet\Helper\Size;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="description" content="Sistem Monitoring Presensi" />
    <title>BBWS | Present</title>
    <!-- CSS files -->
    <link href="<?= base_url('../assets/css/tabler.min.css?1684106062') ?>" rel="stylesheet" />
    <link href="<?= base_url('../assets/css/tabler-flags.min.css?1684106062') ?>" rel="stylesheet" />
    <link href="<?= base_url('../assets/css/tabler-payments.min.css?1684106062') ?>" rel="stylesheet" />
    <link href="<?= base_url('../assets/css/tabler-vendors.min.css?1684106062') ?>" rel="stylesheet" />
    <link href="<?= base_url('../assets/css/demo.min.css?1684106062') ?>" rel="stylesheet" />
    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>

    <!-- Website Icon -->
    <link rel="website icon" type="png" href="<?= base_url('../assets/img/company/new-logo.png') ?>">
</head>

<body class="d-flex flex-column">
    <script src="<?= base_url('../assets/js/demo-theme.min.js?1684106062') ?>"></script>
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="<?= base_url() ?>" class="navbar-brand navbar-brand-autodark align-items-center">
                    <img src="<?= base_url('../assets/img/company/new-logo.png') ?>" height="75" alt="O-Present">
                    <span><h1>SIMPATI</h1></span>
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
            <!-- <div class="text-center text-muted mt-3">
                <a href="<?= site_url('reset-password') ?>">Reset Password</a>
            </div> -->
        </div>
    </div>
    <!-- Libs JS -->
    <!-- Tabler Core -->
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

            // Strip any character that cannot appear in a standard email as the user types
            loginInput.addEventListener('input', function () {
                var start = this.selectionStart;
                var before = this.value;
                var after = before.replace(EMAIL_CHARS, '');
                if (after !== before) {
                    var removed = before.length - after.length;
                    this.value = after;
                    this.setSelectionRange(Math.max(0, start - removed), Math.max(0, start - removed));
                }

                // Clear client-side error once the value looks valid
                if (this.dataset.clientError && EMAIL_FORMAT.test(this.value.trim())) {
                    this.classList.remove('is-invalid');
                    var fb = this.parentElement.querySelector('.invalid-feedback');
                    if (fb) fb.textContent = '';
                    delete this.dataset.clientError;
                }
            });

            // Validate format on submit before the request goes out
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