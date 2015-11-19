<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20151117173400 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $ad = $schema->createTable('plg_dtb_ad');
        $ad->addColumn('ad_id', 'integer', array('notnull' => true, 'autoincrement' => true));
        $ad->addColumn('media_id', 'smallint', array('notnull' => true));
        $ad->addColumn('name', 'text', array('notnull' => true, 'default' => ''));
        $ad->addColumn('code', 'string', array('notnull' => true));
        $ad->addColumn('create_date', 'datetime', array('notnull' => true));
        $ad->addColumn('update_date', 'datetime', array('notnull' => true));
        $ad->addColumn('del_flg', 'smallint', array('notnull' => true, 'default' => 0));
        $ad->setPrimaryKey(array('ad_id'));
        $ad->addUniqueIndex(array('code'));
        
        $media = $schema->createTable('plg_mtb_media');
        $media->addColumn('id', 'smallint', array('notnull' => true, 'autoincrement' => true));
        $media->addColumn('name', 'text', array('notnull' => false));
        $media->addColumn('rank', 'smallint', array('notnull' => true));
        $media->setPrimaryKey(array('id'));

        $ad->addForeignKeyConstraint($media, array('media_id'), array('id'));
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('plg_dtb_ad');
        $schema->dropTable('plg_mtb_media');
    }
}