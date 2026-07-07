<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKeteranganToRak extends Migration
{
    public function up()
    {
        $fields = [
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'detail',
            ],
        ];
        $this->forge->addColumn('rak', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('rak', 'keterangan');
    }
}
