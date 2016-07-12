<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151222183900 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $access = $schema->getTable('plg_dtb_access');
        $access->addIndex(array('history'), 'plg_dtb_access_history_idx');
    }

    public function down(Schema $schema)
    {
        $access = $schema->getTable('plg_dtb_access');
        $access->dropIndex('plg_dtb_access_history_idx');
    }
}