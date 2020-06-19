<?php

if (! defined('DP_BASE_DIR')) {
	die('This file should not be called directly.');
}

class TWGanttClass  implements JsonSerializable {
	/** @var array generic array  */
	var $tasks=[];
	/** @var int */
	var $selectedRow=0;
	/** @var @var array generic array  */
	var $deletedTaskIds=[];
	/** @var @var array generic array  */
	var $resources=[];
	/** @var @var array generic array  */
	var $roles=[];
	/** @var bool */
	var $canWrite=false;
	/** @var bool */
	var $canWriteOnParent=false;
	/** @var string */
	var $zoom='1Q';
	var $canDelete=false;
	var $canAdd = false;
	/** @var string */
	var $progressLine=0;
	
	public function jsonSerialize() {
        return $this;
    }
}


class TWTaskClass  implements JsonSerializable {
	/** @var int  */
	var $id=0;
	/** @var string */
	var $name='';
	/** @var int */
	var $progress=0;
	/** @var bool */
	var $progressByWorklog=false;
	/** @var int*/
	var $relevance=0;
	/** @var string */
	var $type='';
	/** @var string */
	var $typeId='';
	/** @var string */
	var $description='';
	/** @var string */
	var $code='';
	/** @var int */
	var $level=0;
	/** @var string */
	var $status='STATUS_ACTIVE'; // 'STATUS_UNDEFINED';
	/** @var string */
	var $depends=''; // x:x significa o cуdigo da atividade com alguma margem de variaзгo, para mais de uma dependencia usar virgula x,y,z,m 
	/** @var string */
	var $start=0;
	/** @var int */
	var $duration=0;
	/** @var string */
	var $end=0;
	/** @var bool */
	var $startIsMilestone=false;
	/** @var bool */
	var $endIsMilestone=false;
	/** @var bool */
	var $collapsed=false;
	/** @var bool */
	var $canWrite=true;
	/** @var bool */
	var $canAdd=true;
	/** @var bool */
	var $canDelete=true;
	/** @var bool */
	var $canAddIssue=true;
	/** @var @array */
	var $assigs=[];
	/** @var bool */
	var $hasChild=false;
	
	public function jsonSerialize() {
        return $this;
    }
}

class TWResourceClass implements JsonSerializable {
	// {"id":"tmp_1","name":"Resource 1"}
	/** @var string */
	var $id='0';
	/** @var string */
	var $name='';
	/** @var int  */
	var $performance=0;
	
	public function jsonSerialize() {
        return $this;
    }
}

class TWRoleClass implements JsonSerializable {
	// {"id":"tmp_1","name":"Resource 1"}
	/** @var int */
	var $id=0;
	/** @var string */
	var $name='';
	
	public function jsonSerialize() {
        return $this;
    }
}

class TWAssigsClass implements JsonSerializable {
	// {"resourceId":"tmp_1", "id":"tmp_1345560373990", "roleId":"tmp_1", "effort":36000000}
	/*
	resourceId : й o ID exclusivo do recurso. Refere-se а matriz "recursos"
	id : й o identificador exclusivo desta atribuiзгo
	roleId : й o identificador exclusivo desta atribuiзгo. Refere-se а matriz de "papйis"
	esforзo : й o esforзo estimado em milissegundos  - nгo utilizado no simulador
	*/
	/** @var int */
	var $id='0';
	var $roleId='0';
	var $resourceId='0';
	var $effort=0; // nгo utilizado no simulador
	
	public function jsonSerialize() {
        return $this;
    }
}

?>