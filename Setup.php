<?php

namespace ddimavo\MCabinet;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait, StepRunnerUpgradeTrait, StepRunnerUninstallTrait;

    public function installStep1()
    {
        $this->schemaManager()->createTable('xf_mc_user_skins', function(Create $table) {
            $table->addColumn('skin_id', 'int')->autoIncrement();
            $table->addColumn('user_id', 'int');
            $table->addColumn('uuid', 'varchar', 36)->setDefault('');
            $table->addColumn('skin_name', 'varchar', 255);
            $table->addColumn('skin_texture', 'varchar', 255)->setDefault('');
            $table->addColumn('cape_texture', 'varchar', 255)->setDefault('');
            $table->addColumn('is_hd_skin', 'tinyint')->setDefault(0);
            $table->addColumn('is_hd_cape', 'tinyint')->setDefault(0);
            $table->addColumn('skin_size', 'varchar', 20)->setDefault('64x64');
            $table->addColumn('cape_size', 'varchar', 20)->setDefault('64x32');
            $table->addColumn('is_active', 'tinyint')->setDefault(0);
            $table->addColumn('is_public', 'tinyint')->setDefault(0);
            $table->addColumn('is_in_catalog', 'tinyint')->setDefault(0);
            $table->addColumn('catalog_approved', 'tinyint')->setDefault(0);
            $table->addColumn('view_count', 'int')->setDefault(0);
            $table->addColumn('like_count', 'int')->setDefault(0);
            $table->addColumn('download_count', 'int')->setDefault(0);
            $table->addColumn('upload_date', 'int')->setDefault(0);
            $table->addPrimaryKey('skin_id');
            $table->addKey('user_id');
            $table->addKey('uuid');
            $table->addKey('is_active');
            $table->addKey('is_in_catalog');
        });
    }

    public function installStep2()
    {
        $this->schemaManager()->alterTable('xf_user', function(Alter $table) {
            $table->addColumn('mc_uuid', 'varchar', 36)->setDefault('');
            $table->addColumn('mc_username', 'varchar', 255)->setDefault('');
        });
    }

    public function installStep3()
    {
        $this->registerReportHandler('mc_skin', 'ddimavo\ddimavo/MCabinet:Skin');
    }

    public function uninstallStep1()
    {
        $this->schemaManager()->dropTable('xf_mc_user_skins');
    }

    public function uninstallStep2()
    {
        $this->schemaManager()->alterTable('xf_user', function(Alter $table) {
            $table->dropColumns(['mc_uuid', 'mc_username']);
        });
    }

    public function uninstallStep3()
    {
        $this->unregisterReportHandler('mc_skin');
    }
}