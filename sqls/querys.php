<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}


function getSimulationCompanySQL($idComapny) {
	$q = new DBQuery();
	$valid_ordering = array(
	's.simulation_id',
	's.start_date',
	's.end_date'
	);
	$whereSimulation = "projects.project_company = " . $idComapny;
	$q->clear();
	$q->addQuery($valid_ordering);
	$q->addTable("simulation", "s");
	$q->leftJoin("simulation_results", "sr", "s.simulation_id = sr.simulation_id");
	$q->leftJoin("tasks", "task", "sr.task_id = task.task_id");
	$q->leftJoin("projects", "projects", "task.task_project = projects.project_id");
	$q->addWhere($whereSimulation);
	$q->addGroup("s.simulation_id");
	$activeList = $q->loadList();
	return $activeList;
}

function getTarefas($idProject){
	$q = new DBQuery();
	$valid_ordering = array(
	'T.task_id', 
	'T.task_name', 
	'T.task_start_date', 
	'T.task_end_date',
	'(UNIX_TIMESTAMP(T.task_start_date)*1000) as startJS',
	'IF(DATEDIFF(T.task_end_date, T.task_start_date)=0, 1, DATEDIFF(T.task_end_date, T.task_start_date)) as duration',
	'(UNIX_TIMESTAMP(T.task_end_date)*1000) as endJS'
	);
	//$whereSimulation = "EI.is_leaf=1 AND EI.project_id = " . $idProject . " AND T.task_start_date >0 AND T.task_end_date >= T.task_start_date ";
	// EI.is_leaf=1 -- verificar se irá ser incluso como marco
	// tratar erro de existir um pacote sem atividade
	$whereSimulation = "EI.is_leaf=1 AND EI.project_id = " . $idProject . " AND T.task_order >=0 AND T.task_start_date >0 AND T.task_end_date >=T.task_start_date ";
	$q->clear();
	$q->addQuery($valid_ordering);
	$q->addTable("tasks", "T");
	$q->leftJoin("tasks_workpackages", "R", "R.task_id = T.task_id");
	$q->leftJoin("project_eap_items", "EI", "R.eap_item_id=EI.id");
	$q->addWhere($whereSimulation);
	// $q->addGroup("s.simulation_id");
	$q->addOrder("T.task_start_date, T.task_end_date");
	$activeList = $q->loadList();
	return $activeList;	
	/*	
	SELECT 
		   CONCAT('{id: "', T.task_id, '", name: "', T.task_name, 
		  '", actualStart: Date.UTC(', Year(T.task_start_date),', ', Month(T.task_start_date),', ', Day(T.task_start_date),
			'), actualEnd: Date.UTC(', Year(T.task_end_date),', ', Month(T.task_end_date),', ', Day(T.task_end_date),
			  '), connectTo: "',IF((SELECT dependencies_req_task_id FROM dotp_task_dependencies WHERE dependencies_task_id = T.task_id LIMIT 1) is NULL, 'null' , 
					((SELECT dependencies_req_task_id FROM dotp_task_dependencies WHERE dependencies_task_id = T.task_id LIMIT 1))),
					'", connectorType: "finish-start", progressValue: "75%" },') AS AnyChartGantt

	FROM dotp_tasks AS T LEFT JOIN dotp_tasks_workpackages AS R ON T.task_id = R.task_id
		 LEFT JOIN dotp_project_eap_items AS EI ON R.eap_item_id=EI.id
	WHERE EI.is_leaf=1 AND EI.project_id = 2
	ORDER BY T.task_start_date, T.task_end_date; */	

	/*
	SET @row_num = 0;

	SELECT 
		   CONCAT('{"order": ',(@row_num := @row_num+1),' , "id": ', T.task_id, ', "name": "', T.task_name, 
		   '", "progress": 0, "progressByWorklog": false, "relevance": 0, "type":"", "typeId":"", "description":"", "code": "", "level": 0, "status": "STATUS_ACTIVE", ',
			 '"depends": "',IF((SELECT dependencies_req_task_id FROM dotp_task_dependencies WHERE dependencies_task_id = T.task_id LIMIT 1) is NULL, '' , 
								((SELECT dependencies_req_task_id FROM dotp_task_dependencies WHERE dependencies_task_id = T.task_id LIMIT 1))),'", ',
			 '"canWrite": true, ', 
		   '"start": ', (UNIX_TIMESTAMP(T.task_start_date)*1000), 
		   ', "duration":', (DATEDIFF(T.task_end_date, T.task_start_date)+1),
			 ', "end": ', (UNIX_TIMESTAMP(T.task_end_date)*1000),
		   ', "startIsMilestone": false, "endIsMilestone": false, "collapsed": false, "assigs": [], "hasChild": false},') AS TeamWorkGantt

	FROM dotp_tasks AS T LEFT JOIN dotp_tasks_workpackages AS R ON T.task_id = R.task_id
		 LEFT JOIN dotp_project_eap_items AS EI ON R.eap_item_id=EI.id
	WHERE EI.is_leaf=1 AND EI.project_id = 2
	ORDER BY T.task_start_date, T.task_end_date;	
	*/
}

function getDependencias($idTask){
	// SELECT dependencies_req_task_id FROM dotp_task_dependencies WHERE dependencies_task_id = T.task_id
	$q = new DBQuery();
	$valid_ordering = array(
	'D.dependencies_req_task_id'
	);
	$whereSimulation = "D.dependencies_task_id = " . $idTask;
	$q->clear();
	$q->addQuery($valid_ordering);
	$q->addTable("task_dependencies", "D");
	$q->addWhere($whereSimulation);
	// $q->addGroup("s.simulation_id");
	$q->addOrder("D.dependencies_req_task_id");
	$activeList = $q->loadList();
	return $activeList;	
}

function getRecursosHumanosTask($idTask){
	/*
	SELECT T.task_id, T.task_name, 
	   ER.id,
       U.user_id, U.user_username,
       RO.human_resources_role_id,
       RO.human_resources_role_name
	FROM dotp_project_tasks_estimated_roles as ER 
	      LEFT JOIN dotp_human_resources_role AS RO ON RO.human_resources_role_id = ER.role_id
		   LEFT JOIN dotp_tasks AS T ON T.task_id=ER.task_id
		   LEFT JOIN dotp_tasks_workpackages AS R ON T.task_id = R.task_id
			 LEFT JOIN dotp_human_resource_allocation AS RA ON RA.project_tasks_estimated_roles_id = ER.id
			 LEFT JOIN dotp_human_resource as RH ON RH.human_resource_id = RA.human_resource_id
			LEFT JOIN dotp_users AS U ON U.user_id = RH.human_resource_user_id
	WHERE T.task_id = 79;
	*/
	$q = new DBQuery();
	$valid_ordering = array(
	'ER.id',
	'U.user_id', 
	'U.user_username',
	'RO.human_resources_role_id',
    'RO.human_resources_role_name'
	);
	$whereSimulation = "T.task_id = " . $idTask;
	$q->clear();
	$q->addQuery($valid_ordering);
	$q->addTable("project_tasks_estimated_roles", "ER");
	$q->leftJoin("human_resources_role", "RO", "RO.human_resources_role_id = ER.role_id");
	$q->leftJoin("tasks", "T", "T.task_id = ER.task_id");
	$q->leftJoin("tasks_workpackages", "R", "T.task_id = R.task_id");
	// $q->leftJoin("project_eap_items", "EI", "R.eap_item_id=EI.id");
	$q->leftJoin("human_resource_allocation", "RA", "RA.project_tasks_estimated_roles_id = ER.id");
	$q->leftJoin("human_resource", "RH", "RH.human_resource_id = RA.human_resource_id");
	$q->leftJoin("users", "U", "U.user_id = RH.human_resource_user_id");
	$q->addWhere($whereSimulation);
	//$q->addGroup("U.user_id");
	$q->addOrder("U.user_username");
	$activeList = $q->loadList();
	return $activeList;	
}

function getRecursosHumanosProjeto($idProject){
	/*
	-- apenas recursos alocados para um projeto 
	SELECT -- T.task_id, T.task_name, 
		   U.user_id, U.user_username
		 --  , ER.*, RA.*, ER.*, RH.*, U.*
	FROM dotp_project_tasks_estimated_roles as ER 
		   LEFT JOIN dotp_tasks AS T ON T.task_id=ER.task_id
		   LEFT JOIN dotp_tasks_workpackages AS R ON T.task_id = R.task_id
			 LEFT JOIN dotp_project_eap_items AS EI ON R.eap_item_id=EI.id
			 LEFT JOIN dotp_human_resource_allocation AS RA ON RA.project_tasks_estimated_roles_id = ER.id
			 LEFT JOIN dotp_human_resource as RH ON RH.human_resource_id = RA.human_resource_id
			LEFT JOIN dotp_users AS U ON U.user_id = RH.human_resource_user_id
	WHERE EI.is_leaf=1 AND EI.project_id = 2
	GROUP BY U.user_id; */
	$q = new DBQuery();
	$valid_ordering = array(
	'U.user_id', 
	'U.user_username'
	);
	//$whereSimulation = "EI.is_leaf=1 AND EI.project_id = " . $idProject;
	$whereSimulation = "EI.project_id = " . $idProject;
	$q->clear();
	$q->addQuery($valid_ordering);
	$q->addTable("project_tasks_estimated_roles", "ER");
	$q->leftJoin("tasks", "T", "T.task_id = ER.task_id");
	$q->leftJoin("tasks_workpackages", "R", "T.task_id = R.task_id");
	$q->leftJoin("project_eap_items", "EI", "R.eap_item_id=EI.id");
	$q->leftJoin("human_resource_allocation", "RA", "RA.project_tasks_estimated_roles_id = ER.id");
	$q->leftJoin("human_resource", "RH", "RH.human_resource_id = RA.human_resource_id");
	$q->leftJoin("users", "U", "U.user_id = RH.human_resource_user_id");
	$q->addWhere($whereSimulation);
	$q->addGroup("U.user_id");
	$q->addOrder("U.user_username");
	$activeList = $q->loadList();
	return $activeList;	
}

function getRolesProjeto($idProject){
	/* -- apenas roles alocados para um projeto 
	SELECT RO.human_resources_role_id, RO.human_resources_role_name
	FROM dotp_project_tasks_estimated_roles as ER 
		   LEFT JOIN dotp_tasks AS T ON T.task_id=ER.task_id
		   LEFT JOIN dotp_tasks_workpackages AS R ON T.task_id = R.task_id
			 LEFT JOIN dotp_project_eap_items AS EI ON R.eap_item_id=EI.id
			LEFT JOIN dotp_human_resources_role AS RO ON RO.human_resources_role_id = ER.role_id
	WHERE EI.is_leaf=1 AND EI.project_id = 2
	GROUP BY RO.human_resources_role_id; */
	$q = new DBQuery();
	$valid_ordering = array(
	'RO.human_resources_role_id', 
	'RO.human_resources_role_name'
	);
	//$whereSimulation = "EI.is_leaf=1 AND EI.project_id = " . $idProject;
	$whereSimulation = "EI.project_id = " . $idProject;
	$q->clear();
	$q->addQuery($valid_ordering);
	$q->addTable("project_tasks_estimated_roles", "ER");
	$q->leftJoin("tasks", "T", "T.task_id = ER.task_id");
	$q->leftJoin("tasks_workpackages", "R", "T.task_id = R.task_id");
	$q->leftJoin("project_eap_items", "EI", "R.eap_item_id=EI.id");
	$q->leftJoin("human_resources_role", "RO", "RO.human_resources_role_id = ER.role_id");
	$q->addWhere($whereSimulation);
	$q->addGroup("RO.human_resources_role_id");
	$q->addOrder("RO.human_resources_role_name");
	$activeList = $q->loadList();
	return $activeList;	
}

function getResumoProjeto($idProject){
	$q = new DBQuery();
	$valid_ordering = array(
	'(UNIX_TIMESTAMP(MIN(T.task_start_date))*1000) as start', 
	'(UNIX_TIMESTAMP(MAX(T.task_end_date))*1000) as end', 
	'(DATEDIFF(MAX(T.task_end_date), MIN(T.task_start_date))+1) as duration'
	);
	// EI.is_leaf=1 -- verificar se irá ser incluso como marco
	$whereSimulation = "EI.project_id = " . $idProject . " AND EI.is_leaf=1 ";
	$q->clear();
	$q->addQuery($valid_ordering);
	$q->addTable("tasks", "T");
	$q->leftJoin("tasks_workpackages", "R", "R.task_id = T.task_id");
	$q->leftJoin("project_eap_items", "EI", "EI.id = R.eap_item_id");
	$q->addWhere($whereSimulation);
	// $q->addGroup("s.simulation_id");
	// $q->addOrder("T.task_start_date, T.task_end_date");
	$activeList = $q->loadList();
	return $activeList;	
	/*
	SELECT 
	       MIN(T.task_start_date) as inicio , MAX(T.task_end_date) as fim,  
	        (DATEDIFF(MAX(T.task_end_date), MIN(T.task_start_date))+1) as duracao
	FROM dotp_tasks AS T LEFT JOIN dotp_tasks_workpackages AS R ON T.task_id = R.task_id
		 LEFT JOIN dotp_project_eap_items AS EI ON R.eap_item_id=EI.id
	WHERE EI.is_leaf=1 AND EI.project_id = 2;	
	*/
}

/*
// atribuição de usuário e função (resource / role) para todas atividades do projeto
SELECT T.task_id, T.task_name, 
       U.user_id, U.user_username,
       RO.human_resources_role_id,
       RO.human_resources_role_name
	FROM dotp_project_tasks_estimated_roles as ER 
	      LEFT JOIN dotp_human_resources_role AS RO ON RO.human_resources_role_id = ER.role_id
		   LEFT JOIN dotp_tasks AS T ON T.task_id=ER.task_id
		   LEFT JOIN dotp_tasks_workpackages AS R ON T.task_id = R.task_id
		 	 LEFT JOIN dotp_project_eap_items AS EI ON R.eap_item_id=EI.id
			 LEFT JOIN dotp_human_resource_allocation AS RA ON RA.project_tasks_estimated_roles_id = ER.id
			 LEFT JOIN dotp_human_resource as RH ON RH.human_resource_id = RA.human_resource_id
			LEFT JOIN dotp_users AS U ON U.user_id = RH.human_resource_user_id
	WHERE EI.is_leaf=1 AND EI.project_id = 2;

	// atribuição de usuário e função (resource / role) para uma atividade especifica
SELECT T.task_id, T.task_name, 
       U.user_id, U.user_username,
       RO.human_resources_role_id,
       RO.human_resources_role_name
	FROM dotp_project_tasks_estimated_roles as ER 
	      LEFT JOIN dotp_human_resources_role AS RO ON RO.human_resources_role_id = ER.role_id
		   LEFT JOIN dotp_tasks AS T ON T.task_id=ER.task_id
		   LEFT JOIN dotp_tasks_workpackages AS R ON T.task_id = R.task_id
		-- 	 LEFT JOIN dotp_project_eap_items AS EI ON R.eap_item_id=EI.id
			 LEFT JOIN dotp_human_resource_allocation AS RA ON RA.project_tasks_estimated_roles_id = ER.id
			 LEFT JOIN dotp_human_resource as RH ON RH.human_resource_id = RA.human_resource_id
			LEFT JOIN dotp_users AS U ON U.user_id = RH.human_resource_user_id
	WHERE T.task_id = 79;


// atividades com seus recursos alocados
 SELECT T.task_id, T.task_name,
       ER.*
FROM dotp_tasks AS T LEFT JOIN dotp_tasks_workpackages AS R ON T.task_id = R.task_id
		 LEFT JOIN dotp_project_eap_items AS EI ON R.eap_item_id=EI.id
		 LEFT JOIN dotp_project_tasks_estimated_roles as ER ON T.task_id=ER.task_id
	WHERE EI.is_leaf=1 AND EI.project_id = 2;

// sql ativo no dotproject plus
SELECT u.user_username,u.user_id
FROM dotp_project_tasks_estimated_roles AS tr
     LEFT JOIN dotp_human_resource_allocation AS hr_al ON hr_al.project_tasks_estimated_roles_id=tr.id
	  LEFT JOIN dotp_human_resource AS hr ON hr_al.human_resource_id=hr.human_resource_id
	  LEFT JOIN dotp_users AS u ON hr.human_resource_user_id=u.user_id
WHERE tr.task_id=66;

	
// atividades com seus recursos alocados, cruzando com a tabela de recurso
SELECT T.task_id, T.task_name, 
       U.user_id, U.user_username,
       ER.*,
       RA.*,
       ER.*,
       RH.*,
       U.*
FROM dotp_tasks AS T 
       LEFT JOIN dotp_tasks_workpackages AS R ON T.task_id = R.task_id
		 LEFT JOIN dotp_project_eap_items AS EI ON R.eap_item_id=EI.id
		 LEFT JOIN dotp_project_tasks_estimated_roles as ER ON T.task_id=ER.task_id
		 LEFT JOIN dotp_human_resource_allocation AS RA ON RA.project_tasks_estimated_roles_id = ER.id
		 LEFT JOIN dotp_human_resource as RH ON RH.human_resource_id = RA.human_resource_id
	    LEFT JOIN dotp_users AS U ON U.user_id = RH.human_resource_user_id
WHERE EI.is_leaf=1 AND EI.project_id = 2;
		
//  atividades com as funções utilizadas

	
*/

?>