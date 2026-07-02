<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuthLoginsDateIndex extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE auth_logins ADD INDEX idx_date (date)');
        $this->db->query("DELETE FROM auth_logins WHERE date < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    }

    public function down()
    {
        $this->db->query('ALTER TABLE auth_logins DROP INDEX idx_date');
    }
}
