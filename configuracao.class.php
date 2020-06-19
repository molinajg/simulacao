<?php
	if (!defined('DP_BASE_DIR')) {
		die('You should not access this file directly');
	}
	class SimulacaoConfig extends CDpObject {
		var $class_id = null;
		var $dataInicial = null;
		var $dataFinal = null;
		var $sensibilidade = null;
		var $itensSimular = array();
		var $projetosSimular = array();
		function SimulacaoConfig() {
		// parent::CDpObject('', 'class_id');
		}
	} 