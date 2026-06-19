<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Reset Password | O-Present</title>
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
    <link rel="website icon" type="png" href="<?= base_url('../assets/img/company/logo.png') ?>">
</head>

<body class="d-flex flex-column">
    <script src="<?= base_url('../assets/js/demo-theme.min.js?1684106062') ?>"></script>
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="<?= base_url() ?>" class="navbar-brand navbar-brand-autodark align-items-center">
                    <img src="<?= base_url('../assets/img/company/logo.png') ?>" height="75" alt="O-Present">
                    <span><h1>BBWS Brantas Presensi</h1></span>
                </a>
            </div>
            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Reset Password</h2>

                    <?= view('Myth\Auth\Views\_message_block') ?>

                    <form action="<?= site_url('reset-password/update') ?>" method="post" autocomplete="off" novalidate>
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input name="email" type="email" class="form-control <?php if (session('errors.email')) : ?>is-invalid<?php endif ?>" placeholder="Email" value="<?= old('email') ?>" autocomplete="off">
                            <div class="invalid-feedback">
                                <?= session('errors.email') ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input name="new_password" type="password" class="form-control <?php if (session('errors.new_password')) : ?>is-invalid<?php endif ?>" placeholder="Password Baru" autocomplete="off">
                            <div class="invalid-feedback">
                                <?= session('errors.new_password') ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input name="confirm_new_password" type="password" class="form-control <?php if (session('errors.confirm_new_password')) : ?>is-invalid<?php endif ?>" placeholder="Konfirmasi Password Baru" autocomplete="off">
                            <div class="invalid-feedback">
                                <?= session('errors.confirm_new_password') ?>
                            </div>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Simpan Password Baru</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center text-muted mt-3">
                <a href="<?= url_to('login') ?>">Kembali ke halaman login</a>
            </div>
        </div>
    </div>
    <!-- Libs JS -->
    <!-- Tabler Core -->
    <script src="<?= base_url('../assets/js/tabler.min.js?1684106062') ?>" defer></script>
    <script src="<?= base_url('../assets/js/demo.min.js?1684106062') ?>" defer></script>
</body>

</html>
