<?php

/**
 * Helper scoping unit operasional (wilayah OP).
 *
 * Role mapping:
 *  - head  : melihat SEMUA unit  -> tidak ada scoping (return null)
 *  - admin : hanya unit miliknya -> return id_unit (0 jika belum di-set, sehingga tidak ada data yang cocok)
 *  - pegawai: query pegawai sudah self-scoped via id_pegawai, helper ini hanya dipakai di layar admin
 */

if (!function_exists('current_unit_id')) {
    /**
     * Mengembalikan id_unit yang harus dipakai untuk memfilter query layar admin.
     * Mengembalikan null bila user adalah head (lihat semua unit / tanpa filter).
     *
     * @return int|null
     */
    function current_unit_id()
    {
        // Head melihat semua data -> tanpa scoping
        if (function_exists('in_groups') && in_groups('head')) {
            return null;
        }

        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $db = \Config\Database::connect();
        $row = $db->table('users')
            ->select('pegawai.id_unit')
            ->join('pegawai', 'pegawai.id = users.id_pegawai')
            ->where('users.id', user_id())
            ->get()
            ->getRow();

        // (int) null = 0 -> admin tanpa unit tidak melihat data unit manapun
        $cache = $row ? (int) $row->id_unit : 0;

        return $cache;
    }
}
