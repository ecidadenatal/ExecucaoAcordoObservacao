<?php
/**
 * E-cidade Software Publico para Gestão Municipal
 *   Copyright (C) 2015 DBSeller Serviços de Informática Ltda
 *                          www.dbseller.com.br
 *                          e-cidade@dbseller.com.br
 *   Este programa é software livre; você pode redistribuí-lo e/ou
 *   modificá-lo sob os termos da Licença Pública Geral GNU, conforme
 *   publicada pela Free Software Foundation; tanto a versão 2 da
 *   Licença como (a seu critério) qualquer versão mais nova.
 *   Este programa e distribuído na expectativa de ser útil, mas SEM
 *   QUALQUER GARANTIA; sem mesmo a garantia implícita de
 *   COMERCIALIZAÇÃO ou de ADEQUAÇÃO A QUALQUER PROPÓSITO EM
 *   PARTICULAR. Consulte a Licença Pública Geral GNU para obter mais
 *   detalhes.
 *   Você deve ter recebido uma cópia da Licença Pública Geral GNU
 *   junto com este programa; se não, escreva para a Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *   02111-1307, USA.
 *   Cópia da licença no diretório licenca/licenca_en.txt
 *                                 licenca/licenca_pt.txt
 */

class AcordoItemExecucaoRelatorioHTML {

  /**
   *
   * @var integer
   */
  private $iCodigoItemExecucao;

  /**
   *
   * @var text $html
   */
  private $html;

  /**
   *
   * @param integer $iCodigoNota
   */
  public function __construct($iCodigoItemExecucao) {

    $this->iCodigoItemExecucao = $iCodigoItemExecucao;
  }

  /**
   *
   * @param  integer $iCodigoNota
   * @return stdClass
   */
  public function getDados($iCodigoItemExecucao) {

    $oDaoAcordoItemExecucao = db_utils::getDao("acordoitemexecutado");
    
    
    $sCampos         = "ac16_numero, 
    		            ac16_anousu, 
    		            ac16_datainicio, 
    		            ac16_datafim, 
    		            ac16_objeto, 
    		            ac16_deptoresponsavel, 
    		            descrdepto, 
    		            z01_nome, 
    		            ac29_observacao,
    		            (select z01_nome||'|'||rh01_regist 
    		               from acordocomissaomembro 
    		                    inner join cgm on z01_numcgm = ac07_numcgm
    		                    inner  join rhpessoal on rh01_numcgm = z01_numcgm
    		              where ac07_acordocomissao = ac16_acordocomissao
    		                and ac07_tipomembro = 4
    		              limit 1) as fiscal";
    $sSql  =  " select $sCampos ";
    $sSql .=  "   from acordo "; 
    $sSql .=  "        inner join db_depart                            on coddepto           = ac16_deptoresponsavel";
    $sSql .=  "        inner join cgm                                  on z01_numcgm         = ac16_contratado";
    $sSql .=  "        inner join acordoposicao                        on ac26_acordo        = ac16_sequencial";
    $sSql .=  "        inner join acordoitem                           on ac20_acordoposicao = ac26_sequencial ";
    $sSql .=  "        inner join acordoitemexecutado                  on ac20_sequencial    = ac29_acordoitem ";
    $sSql .=  "         left join pcmater                              on pc01_codmater      = ac20_pcmater    ";
    $sSql .=  "         left join matunid                              on m61_codmatunid     = ac20_matunid    ";
    $sSql .=  "  where ac29_sequencial = {$iCodigoItemExecucao}";

    $rsDados  = $oDaoAcordoItemExecucao->sql_record($sSql);
    if ($oDaoAcordoItemExecucao->numrows > 0) {
      return db_utils::fieldsMemory($rsDados, 0);
    }

    throw new Exception("Dados não encontrados");
  }

  /**
   * Emite o documento.
   */
  public function emitir() {
  	 
    $oDados = $this->getDados($this->iCodigoItemExecucao);
    
    $oInstituicao = InstituicaoRepository::getInstituicaoSessao();
    $sCidade      = mb_convert_case($oInstituicao->getMunicipio(), MB_CASE_TITLE);
    
    $aFiscal = explode("|",$oDados->fiscal);
    
    $this->html  = "<html>";
    $this->html .= "<head>";
    $this->html .= "<title></title>";
    
    //estilos
    $this->html .= "<style type='text/css'>";
    $this->html .= "<!--";
    $this->html .= ".ft0{font-style:normal;font-weight:bold;font-size:14px;font-family:Arial;color:#000000;}";
    $this->html .= ".ft1{font-style:normal;font-weight:normal;font-size:13px;font-family:Times New Roman;color:#000000;}";
    $this->html .= ".ft2{font-style:normal;font-weight:bold;font-size:15px;font-family:Arial;color:#000000;}";
    $this->html .= ".ft2{font-style:normal;font-weight:bold;font-size:15px;font-family:Arial;color:#000000;}";
    $this->html .= "-->";
    $this->html .= "</style>";
    $this->html .= "</head>";
    $this->html .= "<body>";

    /*
     * Cabeçalho
     */
    $this->html .= "<table width='750'>
    		          <tr>
    		            <td rowspan='6' width='15%'><img width='85' height='95' src='imagens/files/logologoBrasao.jpg' ALT=''></td>
    		            <td><span class='ft0'>{$oInstituicao->getDescricao()}</span></td>
    		          </tr>
    		          <tr> 
    		            <td><span class='ft1'>{$oInstituicao->getLogradouro()}, {$oInstituicao->getNumero()}</span></td>
    		          </tr>
    		          <tr>
    		            <td><span class='ft1'>{$oInstituicao->getMunicipio()} - {$oInstituicao->getUf()} </span></td>
    		          </tr>
    		          <tr>
    		            <td><span class='ft1'>{$oInstituicao->getTelefone()}   -    CNPJ : ".db_formatar($oInstituicao->getCNPJ(), "cnpj")."</span></td>
    		          </tr>
    		          <tr>
    		            <td></td>
    		          </tr>
    		          <tr>
    		            <td><span class='ft1'>{$oInstituicao->getSite()}</span></td>
    		          </tr>
    		          <tr>
    		            <td colspan='2'><hr></hr></td>
    		          </tr>
    		        </table>
    		        <table width='750'>
    		          <tr>
    		            <td colspan='2' align='center'><span class='ft2'>RELATÓRIO MENSAL DE ACOMPANHAMENTO DE CONTRATO</span></td>  
    		          </tr>
    		        </table>
    		        <br>";
    
    /*
     * Conteudo
     */
    $this->html .= "<table width='750' cellspacing='0' cellpadding='5'>
  			          <tr>
  			            <td width='50%' style='border: 1px solid black;'>CONTRATO Nº ".str_pad($oDados->ac16_numero, "0", "4", STR_PAD_LEFT)."/$oDados->ac16_anousu</td>
      			        <td width='50%' style='border: 1px solid black;'>PRAZO DE VIGÊNCIA DO CONTRATO: De ".db_formatar($oDados->ac16_datainicio, "d")." à ".db_formatar($oDados->ac16_datafim, "d")."</td>
      			      </tr>
      			      <tr>
      			        <td colspan='2'>&nbsp;</td>
      			      </tr>
      			      <tr>
      			        <td colspan='2' style='border: 1px solid black;'>UNIDADE DETENTORA DO CONTRATO: <br> {$oDados->descrdepto}</td>
      			      </tr>
      			      <tr><td colspan='2'>&nbsp;</td></tr>
      			      <tr><td colspan='2' style='border: 1px solid black;' align='justify'>OBJETO DO CONTRATO: <br> $oDados->ac16_objeto</td></tr>
      			      <tr>
      			        <td colspan='2'>&nbsp;</td>
      			      </tr>
      			      <tr>
      			        <td colspan='2' style='border: 1px solid black;'>EMPRESA CONTRATADA: <br> $oDados->z01_nome</td>
      			      </tr>
      			      <tr>
      			        <td colspan='2'>&nbsp;</td>
      			      </tr>
      			      <tr>
      			        <td colspan='2' style='border: 1px solid black;' align='justify'>$oDados->ac29_observacao</td>
      			      </tr>
      			      <tr>
      			        <td colspan='2'>&nbsp;</td>
      			      </tr>
                      <tr>
                         <td width='50%' style='border: 1px solid black;' align='center'>{$sCidade}/{$oInstituicao->getUf()}, ".date("d/m/Y")."</td>
                         <td width='50%' style='border: 1px solid black;' align='center'> $aFiscal[0] <br> FISCAL DO CONTRATO <br> MAT.: $aFiscal[1]</td>
                      </tr>
                    </table>
                    <br>";
    $this->html .= "</body>";
    $this->html .= "</html>";    
    
   echo $this->html;
  }

}