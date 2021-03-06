<?php

use Phinx\Migration\AbstractMigration;

class Avatars extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('user_properties');
	    $table->addColumn('avatar', 'string', ['limit' => 40, 'default' => null, 'null' => true])
		    ->update();
    }
    
    /**
     * Migrate Up.
     */
    public function up()
    {
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}