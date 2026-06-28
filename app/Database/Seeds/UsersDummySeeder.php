<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersDummySeeder extends Seeder
{
    public function run()
    {
        // Default password for all dummy accounts: opresent123
        $hash = password_hash('opresent123', PASSWORD_BCRYPT);

        // group_id 2 = admin, group_id 3 = pegawai  (matches AuthGroupsSeeder order)
        $users = [
            // ── OP I ──────────────────────────────────────────────────────
            ['nip' => 'PEG-0020', 'username' => 'hendrasetiawan',  'email' => 'hendrasetiawan@present.com',  'group_id' => 2],
            ['nip' => 'PEG-0021', 'username' => 'budisantoso',     'email' => 'budisantoso@present.com',     'group_id' => 3],
            ['nip' => 'PEG-0022', 'username' => 'rinamarlina',     'email' => 'rinamarlina@present.com',     'group_id' => 3],
            ['nip' => 'PEG-0023', 'username' => 'dedikurniawan',   'email' => 'dedikurniawan@present.com',   'group_id' => 3],
            // ── OP II ─────────────────────────────────────────────────────
            ['nip' => 'PEG-0024', 'username' => 'andikapratama',   'email' => 'andikapratama@present.com',   'group_id' => 2],
            ['nip' => 'PEG-0025', 'username' => 'fitrihandayani',  'email' => 'fitrihandayani@present.com',  'group_id' => 3],
            ['nip' => 'PEG-0026', 'username' => 'rizkyfirmansyah', 'email' => 'rizkyfirmansyah@present.com', 'group_id' => 3],
            ['nip' => 'PEG-0027', 'username' => 'dewikusuma',      'email' => 'dewikusuma@present.com',      'group_id' => 3],
            // ── OP III ────────────────────────────────────────────────────
            ['nip' => 'PEG-0028', 'username' => 'fajarhidayat',    'email' => 'fajarhidayat@present.com',    'group_id' => 2],
            ['nip' => 'PEG-0029', 'username' => 'sriwahyuni',      'email' => 'sriwahyuni@present.com',      'group_id' => 3],
            ['nip' => 'PEG-0030', 'username' => 'wahyunugroho',    'email' => 'wahyunugroho@present.com',    'group_id' => 3],
            ['nip' => 'PEG-0031', 'username' => 'indahpermata',    'email' => 'indahpermata@present.com',    'group_id' => 3],
            // ── OP IV ─────────────────────────────────────────────────────
            ['nip' => 'PEG-0032', 'username' => 'dimaspras',       'email' => 'dimaspras@present.com',       'group_id' => 2],
            ['nip' => 'PEG-0033', 'username' => 'nuraini',         'email' => 'nuraini@present.com',         'group_id' => 3],
            ['nip' => 'PEG-0034', 'username' => 'rezapermana',     'email' => 'rezapermana@present.com',     'group_id' => 3],
            ['nip' => 'PEG-0035', 'username' => 'yuniastuti',      'email' => 'yuniastuti@present.com',      'group_id' => 3],
            // ── OP PIAT ───────────────────────────────────────────────────
            ['nip' => 'PEG-0036', 'username' => 'megapuspita',     'email' => 'megapuspita@present.com',     'group_id' => 2],
            ['nip' => 'PEG-0037', 'username' => 'ahmadfauzir',     'email' => 'ahmadfauzir@present.com',     'group_id' => 3],
            ['nip' => 'PEG-0038', 'username' => 'lestariwulan',    'email' => 'lestariwulan@present.com',    'group_id' => 3],
            ['nip' => 'PEG-0039', 'username' => 'sitirahayu39',    'email' => 'sitirahayu39@present.com',    'group_id' => 3],
        ];

        foreach ($users as $u) {
            $pegawai = $this->db->table('pegawai')
                ->where('nip', $u['nip'])
                ->get()
                ->getRow();

            if ($pegawai === null) {
                continue; // PegawaiDummySeeder hasn't run yet — skip
            }

            // Skip if a user is already linked to this pegawai OR the username is taken
            $exists = $this->db->table('users')
                ->groupStart()
                    ->where('id_pegawai', $pegawai->id)
                    ->orWhere('username', $u['username'])
                ->groupEnd()
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('users')->insert([
                'id_pegawai'    => $pegawai->id,
                'username'      => $u['username'],
                'email'         => $u['email'],
                'password_hash' => $hash,
                'active'        => 1,
            ]);

            $userId = $this->db->insertID();

            $this->db->table('auth_groups_users')->insert([
                'group_id' => $u['group_id'],
                'user_id'  => $userId,
            ]);
        }
    }
}
