<?php

class Migration_Add_likes extends CI_Migration
{
    public function up()
    {
        $fields = array(
            'likes' => array('type' => 'INT', 'after' => 'text')
        );

        $this->dbforge->add_column('post', $fields);
        $this->dbforge->add_column('comment', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('post', 'likes');
        $this->dbforge->drop_column('comment', 'likes');
    }
}