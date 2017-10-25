<?php 
require_once 'libs/db_stdlib.php';
require_once 'libs/db_conecta_plugin.php';
require_once 'libs/db_sessoes.php';
require_once 'libs/db_utils.php';
require_once 'dbforms/db_funcoes.php';
require_once 'fpdf151/PDFDocument.php';

$oGET = db_utils::postMemory($_GET);

try {
	
	$oDocumento = new AcordoItemExecucaoRelatorioHTML($oGET->acordoitemexecucao);
    $oDocumento->emitir();
    
} catch (Exception $e) {
    db_redireciona('db_erros.php?fechar=true&db_erro=' . urlencode($e->getMessage()));
}
?>