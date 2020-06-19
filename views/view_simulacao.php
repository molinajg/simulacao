<?php
	if (!defined('DP_BASE_DIR')) {
		die('You should not access this file directly');
	}
	
	require_once (DP_BASE_DIR . "/modules/simulacao/classes/gantt.class.php");
	require_once (DP_BASE_DIR . "/modules/simulacao/sqls/querys.php");
	
	$AppUI->savePlace();
	$project_id = intval(dPgetParam($_GET, "project_id", 0));
	//$project_id = $_GET["project_id"];	
	
	//limpar a sessão
	//unset($_SESSION['receptors']);
	//unset($_SESSION['emitters']);

?>

<?php
// limpar localStorage do navegador
echo "<script>
        localStorage.removeItem('TWProjeto');
    </script>";

     //------------
	$gant1 = new TWGanttClass();
	$tasks_ = array();
	//$project_id=2;
	$tarefasProjeto = getTarefas($project_id);
	$indices_ = array();
	$indices_[1] = 0; // prazo total do projeto
	// salva os indices vs id tarefa
	foreach ($tarefasProjeto as $tarefa) {
		$indices_[count($indices_)+1] = $tarefa['task_id']; // armazena o indice da tarefa e ID da base, para corrigir as dependencias
	}
	// atualiza todos recursos do projeto ======================================
	$resources_ = array();
	$recursoIndices_ = array();
	$recursosProjeto = getRecursosHumanosProjeto($project_id);//$AppUI->user_company);
	foreach ($recursosProjeto as $recurso) {
		$recursoIndices_[count($recursoIndices_)+1] = $recurso['user_id'];
		$resource = new TWResourceClass();
		$resource->id = 'tmp_'.count($recursoIndices_); //intval($recurso['user_id']);
		$resource->name = $recurso['user_username'];
		$resource->performance = 1; // verificar se utilizar este campo 
		array_push($resources_, $resource);
	}
	$gant1->resources = $resources_;
	// atualiza todos roles do projeto ===========================================
	$roles_ = array();
	$rolesIndices_ = array();
	$rolesProjeto = getRolesProjeto($project_id);//$AppUI->user_company);
	foreach ($rolesProjeto as $funcao) {
		$rolesIndices_[count($rolesIndices_)+1] = $funcao['human_resources_role_id'];
		$role = new TWRoleClass();
		$role->id = 'tmp_'. count($rolesIndices_); //intval($funcao['human_resources_role_id']);
		$role->name = $funcao['human_resources_role_name'];
		array_push($roles_, $role);
		
	}
	$gant1->roles = $roles_;
	
	// teste inclusão projeto como nível inicial ------------------------------
	$resumoProjeto = getResumoProjeto($project_id);//$AppUI->user_company);
	$resumoP = $resumoProjeto[0];
	// echo $resumoP['start'];
	$task = new TWTaskClass();
	$task->id = 0;
	$task->code = '';
	$task->name = 'Prazo total do Projeto';
	$task->start = floatval($resumoP['start']);
	$task->duration = intval($resumoP['duration']);
	$task->end = floatval($resumoP['end']);
	$task->level = intval(0); // nível zero (raiz)
	$task->hasChild = true;
	$task->status = 'STATUS_UNDEFINED'; //'STATUS_SUSPENDED';
	$task->canWrite = false;
	array_push($tasks_, $task );

	$gant1->progressLine = floatval($resumoP['start']); // inicio do projeto

	// percorre as tarefas armazenando com as dependencias corrigidas =========================================
	foreach ($tarefasProjeto as $tarefa) {
		if ($tarefa['task_id']){
			$task = new TWTaskClass();
			$task->id = intval($tarefa['task_id']);
			$task->code = $tarefa['task_id'];
			$task->name = $tarefa['task_name']; // utf8_decode($tarefa['task_name']);
			$task->start = floatval($tarefa['startJS']);
			$task->duration = intval($tarefa['duration']);
			$task->end = floatval($tarefa['endJS']);
			$task->level = intval(0);// nível 1 - filhos do resmo do projeto
			$task->hasChild = false;
			// ADICIONA DEPENDENCIAS
			$dependenciasTask = getDependencias($task->id);
			$dependencias_ = '';
			foreach ($dependenciasTask as $dependencia) {
				$ind_ = array_search($dependencia['dependencies_req_task_id'], $indices_); 
				//$dependencias_ = $dependencias_ . ($dependencias_!='')?(','.$dependencia['dependencies_req_task_id']):$dependencia['dependencies_req_task_id'];
				$dependencias_ = $dependencias_ . ($dependencias_!='')?($dependencias_.','.$ind_):(''.$ind_);
			}
			$task->depends = $dependencias_;
			// ACIDIONA RECURSOS ALOCADOS
			$recursosTask = getRecursosHumanosTask($task->id);
			$assigs_ = array();
			foreach ($recursosTask as $recursoH) {
				// pegar id do recurso no vetor
				// pegar id do role no vetor
				$idrec_  = array_search($recursoH['user_id'], $recursoIndices_);
				$idrole_ = array_search($recursoH['human_resources_role_id'], $rolesIndices_);
				$atribuicao = new TWAssigsClass();
				$atribuicao->id = strval($recursoH['id']);
				$atribuicao->resourceId = 'tmp_'. $idrec_; //$recursoH['user_id'];
				$atribuicao->roleId = 'tmp_'. $idrole_; // $recursoH['human_resources_role_id'];
				// $atribuicao->resourceId = intval($idrec_);
				// $atribuicao->roleId = intval($idrole_);
				// $atribuicao->effort = intval($recursoH['id']);
				array_push($assigs_, $atribuicao );
			}
			$task->assigs = $assigs_;
			array_push($tasks_, $task );
		}
	}
	$gant1->tasks = $tasks_;
	
	echo "<script>localStorage.setItem('TWPGanttSplitPos',0.47);</script> ";	
	echo "<script>localStorage.setItem('TWPGanttSavedZooms','{\"66\":\"1M\"}');</script>";	
	echo "<script>var projeto=".json_encode($gant1, JSON_PRETTY_PRINT).";
			localStorage.removeItem('TWProjeto');
			localStorage.setItem('TWProjeto', JSON.stringify(projeto));
		</script>";	
	// function que prepara a URL para carregar no iFrame de visualização
	function URL_SIMULADOR_HTML_2(){
		$dirSimul = "modules/simulacao/ExecProject/index.html";
		$host_ = $_SERVER['HTTP_HOST'];
		$dirCur_ = str_replace("index.php", $dirSimul, $_SERVER['PHP_SELF']); 
		return sprintf(
						"%s://%s%s",
						isset($_SERVER['HTTPS'])&& $_SERVER['HTTP'] !='off'?'https':'http',
						$host_,
						$dirCur_
						);
	}		
?>

<div>
	<!-- src="http://localhost/dotproject_sim/modules/simulacao/ExecProject/index.html" -->
	<iframe style="border: none;
					/*position: fixed;*/
					top: 0;
					left: 0;
					width: 100%;
					height: 700px;"
		src="<?php echo URL_SIMULADOR_HTML_2();?>"
		scrolling="no" 
		frameborder="no">
	</iframe>	
</div>




