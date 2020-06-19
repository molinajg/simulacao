<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly');
}
$config = array(
	'mod_name' => 'Simulação ExecProject',
	'mod_version' => '1.0.0',
	'mod_directory' => 'simulacao',
	'mod_setup_class' => 'SetupSimulacao',
	'mod_type' => 'user',
	'mod_ui_name' => 'Simulação',
	'mod_ui_icon' => 'Gear_icon_svg.png',
	'mod_description' => '',
	'permissions_item_table' => 'simulacao_inicio',
	'permissions_item_field' => 'pma_id',
	'permissions_item_label' => 'project_name'
	);
if (@$a == 'setup') {
	echo dPshowModuleConfig($config);
}
class SetupSimulacao {
	function install() {
		$ok = true;
		/*
		$q = new DBQuery;
		//Recursos Humanos
		$q->clear();
		$q->addTable("config");
		$q->addInsert("config_name", 'pm_human_resources');
		$q->addInsert("config_value", 10);
		$q->addInsert("config_group", 'simulation');
		$q->addInsert("config_type", 'number');
		$q->exec();
		//Técnico
		$q->clear();
		$q->addTable("config");
		$q->addInsert("config_name", 'pm_technical');
		$q->addInsert("config_value", 10);
		$q->addInsert("config_group", 'simulation');
		$q->addInsert("config_type", 'number');
		$q->exec();
		//Custos
		$q->clear();
		$q->addTable("config");
		$q->addInsert("config_name", 'pm_cost');
		$q->addInsert("config_value", 30);
		$q->addInsert("config_group", 'simulation');
		$q->addInsert("config_type", 'number');
		$q->exec();
		//Aquisição
		$q->clear();
		$q->addTable("config");
		$q->addInsert("config_name", 'pm_acquisition');
		$q->addInsert("config_value", 10);
		$q->addInsert("config_group", 'simulation');
		$q->addInsert("config_type", 'number');
		$q->exec();
		//Comunicação
		$q->clear();
		$q->addTable("config");
		$q->addInsert("config_name", 'pm_communication');
		$q->addInsert("config_value", 40);
		$q->addInsert("config_group", 'simulation');
		$q->addInsert("config_type", 'number');
		$q->exec();
		*/
		if (!$ok)
		return false;
		return null;
		}
		
	function remove() {
		/*
		$q = new DBQuery;
		$q->clear();
		$q->setDelete("config");
		$q->addWhere("config_name= 'pm_human_resources' AND config_group= 'simulation'");
		$q->exec();
		$q->clear();$q->setDelete("config");
		$q->addWhere("config_name= 'pm_technical' AND config_group= 'simulation'");
		$q->exec();
		$q->clear();
		$q->setDelete("config");
		$q->addWhere("config_name= 'pm_cost' AND config_group= 'simulation'");
		$q->exec();
		$q->clear();
		$q->setDelete("config");
		$q->addWhere("config_name= 'pm_acquisition' AND config_group= 'simulation'");
		$q->exec();
		$q->clear();
		$q->setDelete("config");
		$q->addWhere("config_name= 'pm_communication' AND config_group= 'simulation'");
		$q->exec();
		*/
		return null;
		}function upgrade($old_version) {
		return true;
	}
}?>