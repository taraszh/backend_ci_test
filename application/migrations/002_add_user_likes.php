<?php

class Migration_Add_user_likes extends CI_Migration
{
    public function up()
    {
        $fields = [
            'likes_total' => ['type' => 'INT', 'after' => 'wallet_total_withdrawn']
        ];

        $this->dbforge->add_column('user', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('user', 'likes_total');
    }
}