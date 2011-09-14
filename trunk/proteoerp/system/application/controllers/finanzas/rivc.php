<?php require_once(APPPATH.'/controllers/finanzas/gser.php');
class rivc extends Controller {
	var $titp='Retenciones de Clientes';
	var $tits='Retenciones de Clientes';
	var $url ='finanzas/rivc/';

	function rivc(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('511',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');

		$filter = new DataFilter($this->titp, 'rivc');

		$filter->nrocomp = new inputField('Comprobante','nrocomp');
		$filter->nrocomp->rule      ='max_length[8]';
		$filter->nrocomp->size      =10;
		$filter->nrocomp->maxlength =8;

		$filter->emision = new dateField('Emisi&oacute;n','emision');
		$filter->emision->rule      ='chfecha';
		$filter->emision->size      =10;
		$filter->emision->maxlength =8;

		$filter->periodo = new inputField('Per&iacute;odo','periodo');
		$filter->periodo->rule      ='max_length[8]';
		$filter->periodo->size      =10;
		$filter->periodo->maxlength =8;

		$filter->fecha = new dateField('Fecha','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;

		$filter->rif = new inputField('RIF','rif');
		$filter->rif->rule      ='max_length[14]';
		$filter->rif->size      =16;
		$filter->rif->maxlength =14;

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<raencode><#nrocomp#></raencode>','<#nrocomp#>');

		$grid = new DataGrid('');
		$grid->order_by('nrocomp','desc');
		$grid->per_page = 40;

		$grid->column_orderby('Comprobante'   ,$uri,'nrocomp','align="left"');
		$grid->column_orderby('Emisi&oacute;n','<dbdate_to_human><#emision#></dbdate_to_human>','emision','align="center"');
		$grid->column_orderby('fecha'         ,'<dbdate_to_human><#fecha#></dbdate_to_human>'  ,'fecha','align="center"');
		$grid->column_orderby('Cliente'       ,'clipro','clipro','align="left"');
		$grid->column_orderby('Nombre'        ,'nombre','nombre','align="left"');
		$grid->column_orderby('RIF'           ,'rif'   ,'rif'   ,'align="left"');
		$grid->column_orderby('Impuesto'      ,'<nformat><#impuesto#></nformat>','impuesto','align="right"');
		$grid->column_orderby('Total'         ,'<nformat><#gtotal#></nformat>'  ,'gtotal','align="right"');
		$grid->column_orderby('Monto Ret.'    ,'<nformat><#reiva#></nformat>'   ,'reiva','align="right"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('datadetails','dataobject');

		$do = new DataObject('rivc');
		//$do->pointer('scli' ,'scli.cliente=rivc.cod_cli','sprv.tipo AS sprvtipo, sprv.reteiva AS sprvreteiva','left');
		$do->rel_one_to_many('itrivc' ,'itrivc' ,array('id'=>'idrivc'));

		$edit = new DataDetails($this->tits, $do);

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');


		$edit->nrocomp = new inputField('Comprobante','nrocomp');
		$edit->nrocomp->rule='max_length[8]|required';
		$edit->nrocomp->size =10;
		$edit->nrocomp->maxlength =8;
		$edit->nrocomp->autocomplete = false;

		$edit->emision = new dateField('Fecha de Emisi&oacute;n','emision');
		$edit->emision->rule='chfecha|required';
		$edit->emision->size =10;
		$edit->emision->maxlength =8;

		$edit->periodo = new inputField('Per&iacute;odo','periodo');
		$edit->periodo->rule='max_length[8]';
		$edit->periodo->size =10;
		$edit->periodo->maxlength =8;

		$edit->fecha = new dateField('Fecha de Recepci&oacute;n','fecha');
		$edit->fecha->rule='chfecha|required';
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->cod_cli = new hiddenField('Cliente','cod_cli');
		$edit->cod_cli->rule='max_length[5]|required';
		$edit->cod_cli->size =7;
		$edit->cod_cli->maxlength =5;
		$edit->cod_cli->readonly=true;

		$edit->nombre = new hiddenField('Nombre','nombre');
		$edit->nombre->rule='max_length[40]';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =40;

		$edit->rif = new inputField('RIF','rif');
		$edit->rif->rule='max_length[14]';
		$edit->rif->size =16;
		$edit->rif->maxlength =14;
		$edit->rif->autocomplete = false;

		$edit->exento = new inputField('Monto Exento','exento');
		$edit->exento->rule='max_length[15]|numeric';
		$edit->exento->css_class='inputnum';
		$edit->exento->size =17;
		$edit->exento->maxlength =15;

		$edit->tasa = new inputField('tasa','tasa');
		$edit->tasa->rule='max_length[5]|numeric';
		$edit->tasa->css_class='inputnum';
		$edit->tasa->size =7;
		$edit->tasa->maxlength =5;

		$edit->general = new inputField('general','general');
		$edit->general->rule='max_length[15]|numeric';
		$edit->general->css_class='inputnum';
		$edit->general->size =17;
		$edit->general->maxlength =15;

		$edit->geneimpu = new inputField('geneimpu','geneimpu');
		$edit->geneimpu->rule='max_length[15]|numeric';
		$edit->geneimpu->css_class='inputnum';
		$edit->geneimpu->size =17;
		$edit->geneimpu->maxlength =15;

		$edit->tasaadic = new inputField('tasaadic','tasaadic');
		$edit->tasaadic->rule='max_length[5]|numeric';
		$edit->tasaadic->css_class='inputnum';
		$edit->tasaadic->size =7;
		$edit->tasaadic->maxlength =5;

		$edit->adicional = new inputField('adicional','adicional');
		$edit->adicional->rule='max_length[15]|numeric';
		$edit->adicional->css_class='inputnum';
		$edit->adicional->size =17;
		$edit->adicional->maxlength =15;

		$edit->adicimpu = new inputField('adicimpu','adicimpu');
		$edit->adicimpu->rule='max_length[15]|numeric';
		$edit->adicimpu->css_class='inputnum';
		$edit->adicimpu->size =17;
		$edit->adicimpu->maxlength =15;

		$edit->tasaredu = new inputField('tasaredu','tasaredu');
		$edit->tasaredu->rule='max_length[5]|numeric';
		$edit->tasaredu->css_class='inputnum';
		$edit->tasaredu->size =7;
		$edit->tasaredu->maxlength =5;

		$edit->reducida = new inputField('reducida','reducida');
		$edit->reducida->rule='max_length[15]|numeric';
		$edit->reducida->css_class='inputnum';
		$edit->reducida->size =17;
		$edit->reducida->maxlength =15;

		$edit->reduimpu = new inputField('reduimpu','reduimpu');
		$edit->reduimpu->rule='max_length[15]|numeric';
		$edit->reduimpu->css_class='inputnum';
		$edit->reduimpu->size =17;
		$edit->reduimpu->maxlength =15;

		$edit->stotal = new hiddenField('Sub-total','stotal');
		$edit->stotal->rule='max_length[15]|numeric';
		$edit->stotal->css_class='inputnum';
		$edit->stotal->size =17;
		$edit->stotal->maxlength =15;

		$edit->impuesto = new hiddenField('Impuesto','impuesto');
		$edit->impuesto->rule='max_length[15]|numeric';
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->size =17;
		$edit->impuesto->maxlength =15;

		$edit->gtotal = new hiddenField('Total','gtotal');
		$edit->gtotal->rule='max_length[15]|numeric';
		$edit->gtotal->css_class='inputnum';
		$edit->gtotal->size =17;
		$edit->gtotal->maxlength =15;

		$edit->reiva = new hiddenField('Monto Retenido','reiva');
		$edit->reiva->rule='max_length[15]|numeric';
		$edit->reiva->css_class='inputnum';
		$edit->reiva->size =17;
		$edit->reiva->maxlength =15;

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->modificado = new inputField('modificado','modificado');
		$edit->modificado->rule='max_length[8]';
		$edit->modificado->size =10;
		$edit->modificado->maxlength =8;

		//****************************
		//Inicio del Detalle
		//****************************
		$edit->it_tipo_doc = new hiddenField('tipo_doc','tipo_doc_<#i#>');
		$edit->it_tipo_doc->db_name='tipo_doc';
		$edit->it_tipo_doc->rule='max_length[2]|required';
		$edit->it_tipo_doc->size =4;
		$edit->it_tipo_doc->maxlength =1;
		$edit->it_tipo_doc->rel_id ='itrivc';

		$edit->it_numero = new inputField('numero','numero_<#i#>');
		$edit->it_numero->db_name='numero';
		$edit->it_numero->rule='max_length[12]|required';
		$edit->it_numero->size =14;
		$edit->it_numero->maxlength =12;
		$edit->it_numero->rel_id ='itrivc';
		$edit->it_numero->autocomplete = false;

		$edit->it_exento = new inputField('exento','exento_<#i#>');
		$edit->it_exento->db_name='exento';
		$edit->it_exento->rule='max_length[15]|numeric';
		$edit->it_exento->css_class='inputnum';
		$edit->it_exento->size =17;
		$edit->it_exento->maxlength =15;
		$edit->it_excento->rel_id ='itrivc';

		$edit->it_tasa = new inputField('tasa','tasa_<#i#>');
		$edit->it_tasa->db_name='tasa';
		$edit->it_tasa->rule='max_length[5]|numeric';
		$edit->it_tasa->css_class='inputnum';
		$edit->it_tasa->size =7;
		$edit->it_tasa->maxlength =5;
		$edit->it_tasa->rel_id ='itrivc';

		$edit->it_general = new inputField('general','general_<#i#>');
		$edit->it_general->db_name='general';
		$edit->it_general->rule='max_length[15]|numeric';
		$edit->it_general->css_class='inputnum';
		$edit->it_general->size =17;
		$edit->it_general->maxlength =15;
		$edit->it_general->rel_id ='itrivc';

		$edit->it_geneimpu = new inputField('geneimpu','geneimpu_<#i#>');
		$edit->it_geneimpu->db_name='geneimpu';
		$edit->it_geneimpu->rule='max_length[15]|numeric';
		$edit->it_geneimpu->css_class='inputnum';
		$edit->it_geneimpu->size =17;
		$edit->it_geneimpu->maxlength =15;
		$edit->it_geneimpu->rel_id ='itrivc';

		$edit->it_tasaadic = new inputField('tasaadic','tasaadic_<#i#>');
		$edit->it_tasaadic->db_name='tasaadic';
		$edit->it_tasaadic->rule='max_length[5]|numeric';
		$edit->it_tasaadic->css_class='inputnum';
		$edit->it_tasaadic->size =7;
		$edit->it_tasaadic->maxlength =5;
		$edit->it_tasaasic->rel_id ='itrivc';

		$edit->it_adicional = new inputField('adicional','adicional_<#i#>');
		$edit->it_adicional->db_name='adicional';
		$edit->it_adicional->rule='max_length[15]|numeric';
		$edit->it_adicional->css_class='inputnum';
		$edit->it_adicional->size =17;
		$edit->it_adicional->maxlength =15;
		$edit->it_adicional->rel_id ='itrivc';

		$edit->it_adicimpu = new inputField('adicimpu','adicimpu_<#i#>');
		$edit->it_adicimpu->db_name='adicimpu';
		$edit->it_adicimpu->rule='max_length[15]|numeric';
		$edit->it_adicimpu->css_class='inputnum';
		$edit->it_adicimpu->size =17;
		$edit->it_adicimpu->maxlength =15;
		$edit->it_adicimpu->rel_id ='itrivc';

		$edit->it_tasaredu = new inputField('tasaredu','tasaredu_<#i#>');
		$edit->it_tasaredu->db_name='tasaredu';
		$edit->it_tasaredu->rule='max_length[5]|numeric';
		$edit->it_tasaredu->css_class='inputnum';
		$edit->it_tasaredu->size =7;
		$edit->it_tasaredu->maxlength =5;
		$edit->it_tasaredu->rel_id ='itrivc';

		$edit->it_reducida = new inputField('reducida','reducida_<#i#>');
		$edit->it_reducida->db_name='reducida';
		$edit->it_reducida->rule='max_length[15]|numeric';
		$edit->it_reducida->css_class='inputnum';
		$edit->it_reducida->size =17;
		$edit->it_reducida->maxlength =15;
		$edit->it_reducida->rel_id ='itrivc';

		$edit->it_reduimpu = new inputField('reduimpu','reduimpu_<#i#>');
		$edit->it_reduimpu->db_name='reduimpu';
		$edit->it_reduimpu->rule='max_length[15]|numeric';
		$edit->it_reduimpu->css_class='inputnum';
		$edit->it_reduimpu->size =17;
		$edit->it_reduimpu->maxlength =15;
		$edit->it_reduimpu->rel_id ='itrivc';

		$edit->it_stotal = new inputField('stotal','stotal_<#i#>');
		$edit->it_stotal->db_name='stotal';
		$edit->it_stotal->rule='max_length[15]|numeric';
		$edit->it_stotal->css_class='inputnum';
		$edit->it_stotal->size =17;
		$edit->it_stotal->maxlength =15;
		$edit->it_stotal->rel_id ='itrivc';

		$edit->it_impuesto = new hiddenField('impuesto','impuesto_<#i#>');
		$edit->it_impuesto->db_name='impuesto';
		$edit->it_impuesto->rule='max_length[15]|numeric';
		$edit->it_impuesto->css_class='inputnum';
		$edit->it_impuesto->size =17;
		$edit->it_impuesto->maxlength =15;
		$edit->it_impuesto->rel_id ='itrivc';

		$edit->it_gtotal = new hiddenField('gtotal','gtotal_<#i#>');
		$edit->it_gtotal->db_name='gtotal';
		$edit->it_gtotal->rule='max_length[15]|numeric';
		$edit->it_gtotal->css_class='inputnum';
		$edit->it_gtotal->size =17;
		$edit->it_gtotal->maxlength =15;
		$edit->it_gtotal->rel_id ='itrivc';
		$edit->it_gtotal->autocomplete = false;

		$edit->it_reiva = new inputField('reiva','reiva_<#i#>');
		$edit->it_reiva->db_name='reiva';
		$edit->it_reiva->rule='max_length[15]|numeric';
		$edit->it_reiva->css_class='inputnum';
		$edit->it_reiva->size =17;
		$edit->it_reiva->maxlength =15;
		$edit->it_reiva->rel_id ='itrivc';
		$edit->it_reiva->onkeyup ='totalizar()';
		$edit->it_reiva->autocomplete = false;
		//****************************
		//Fin del Detalle
		//****************************

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back','add_rel');
		$edit->build();

		//$data['content'] = $edit->output;
		$conten['form'] =& $edit;
		$data['content'] = $this->load->view('view_rivc', $conten,true);
		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= script('jquery.js');
		$data['head']   .= script('jquery-ui.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js');
		$data['head']   .= script('plugins/jquery.floatnumber.js');
		$data['head']   .= phpscript('nformat.js');
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);
	}

	function buscasfac(){
		$mid   = $this->input->post('q');
		$scli  = $this->input->post('scli');
		$qdb   = $this->db->escape('%'.$mid.'%');
		$sclidb= $this->db->escape($scli);
		
		$rete=0.75;
		$data = '{[ ]}';
		if(empty($scli)){
			$retArray[0]['label']   = 'Debe seleccionar un cliente primero';
			$retArray[0]['value']   = '';
			$data = json_encode($retArray);
			echo $data;
			return;
		}
		if($mid !== false){
			$retArray = $retorno = array();

			$mSQL="SELECT a.tipo_doc, a.numero, a.totalg, a.iva, a.iva*$rete AS reiva
				FROM sfac AS a
				LEFT JOIN itrivc AS b ON a.tipo_doc=b.tipo_doc AND a.numero=b.numero
				WHERE a.cod_cli=$sclidb AND a.numero LIKE $qdb AND b.numero IS NULL
				ORDER BY numero LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = $row['tipo_doc'].'-'.$row['numero'].' '.$row['totalg'].' Bs.';
					$retArray['value']   = $row['numero'];
					$retArray['gtotal']  = $row['totalg'];
					$retArray['reiva']   = round($row['reiva'],2);
					$retArray['impuesto']= $row['iva'];
					$retArray['tipo_doc']= $row['tipo_doc'];
					
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }else{
				$retArray[0]['label']   = 'No se consiguieron facturas para aplicar';
				$retArray[0]['value']   = '';
				$data = json_encode($retArray);
			}
		}
		echo $data;
	}

	function buscascli(){
		$mid  = $this->input->post('q');
		$qdb  = $this->db->escape('%'.$mid.'%');

		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente, tipo
				FROM scli WHERE cliente LIKE ${qdb} OR rifci LIKE ${qdb}
				ORDER BY rifci LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['rifci'];
					$retArray['label']   = '('.$row['rifci'].') '.$row['nombre'];
					$retArray['nombre']  = $row['nombre'];
					$retArray['cod_cli'] = $row['cliente'];
					$retArray['tipo']    = $row['tipo'];
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}
		}
		echo $data;
		return true;
	}

	function _pre_insert($do){
		$transac = $this->datasis->fprox_numero('ntransa');
		$do->set('transac', $transac);
		$estampa = $do->get('estampa');
		$hora    = $do->get('hora');
		$usuario = $do->get('usuario');

		$rel='itrivc';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$dbitnumero   = $this->db->escape($do->get_rel($rel, 'numero'  , $i));
			$dbittipo_doc = $this->db->escape($do->get_rel($rel, 'tipo_doc', $i));

			$sql="SELECT exento,tasa,reducida,sobretasa,montasa,monredu,monadic FROM sfac WHERE numero=$dbitnumero AND tipo_doc=$dbittipo_doc";
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0){
				$row = $query->row();

				$do->set_rel($rel, 'exento'   , $row->exento , $i);

				$do->set_rel($rel, 'tasa'     , ($row->montasa>0)? round($row->tasa *100/$row->montasa,2) : 0, $i);
				$do->set_rel($rel, 'general'  , $row->montasa, $i);
				$do->set_rel($rel, 'geneimpu' , $row->tasa   , $i);

				$do->set_rel($rel, 'tasaadic' , ($row->monadic>0)? round($row->sobretasa*100/$row->monadic,2) : 0, $i);
				$do->set_rel($rel, 'adicional', $row->monadic  , $i);
				$do->set_rel($rel, 'adicimpu' , $row->sobretasa, $i);

				$do->set_rel($rel, 'tasaredu' , ($row->monredu>0)? round($row->reducida*100/ $row->monredu,2) : 0, $i);
				$do->set_rel($rel, 'reducida' , $row->monredu , $i);
				$do->set_rel($rel, 'reduimpu' , $row->reducida, $i);

			}

			$do->set_rel($rel, 'estampa', $estampa, $i);
			$do->set_rel($rel, 'hora'   , $hora   , $i);
			$do->set_rel($rel, 'usuario', $usuario, $i);
			$do->set_rel($rel, 'transac', $transac, $i);
		}

		return true;
	}

	function _pre_update($do){
		return true;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);

		$transac = $do->get('ntransa');
		$estampa = $do->get('estampa');
		$hora    = $do->get('hora');
		$usuario = $do->get('usuario');
		$cod_cli = $do->get('cod_cli');
		$nombre  = $do->get('nombre');
		$estampa = $do->get('estampa');
		$usuario = $do->get('usuario');
		$hora    = $do->get('hora');

		//$reinte  = $this->uri->segment($this->uri->total_segments());
		$efecha  = $do->get('emision');
		$fecha   = $do->get('fecha');
		$numero  = $do->get('numero');


		$rel='itrivc';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$ittipo_doc  = $do->get_rel($rel, 'tipo_doc', $i);
			$itnumero    = $do->get_rel($rel, 'numero'  , $i);
			$itmonto     = $do->get_rel($rel, 'reiva'  , $i);

			$dbitnumero   = $this->db->escape($itnumero);
			$dbittipo_doc = $this->db->escape($ittipo_doc);

			$sql="SELECT referen,reiva,factura,cod_cli,nombre FROM sfac WHERE numero=$dbitnumero AND tipo_doc=$dbittipo_doc";
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0){
				$row = $query->row();

				$anterior    = $row['reiva'];
				$itreferen   = $row['referen'];
				$itfactura   = $row['factura'];
			}

			if($anterior == 0) {
				$mSQL = "UPDATE sfac SET reiva=$itmonto, creiva='$numero', freiva='$fecha', ereiva='$efecha' WHERE numero=$dbitnumero AND tipo_doc=$dbittipo_doc";
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }
			}

			//Chequea si es credito y si tiene saldo
			if($itreferen=='C'){
				$saldo =  $this->datasis->dameval("SELECT monto-abonos FROM smov WHERE tipo_doc='FC' AND numero='$itnumero'");
			}else{
				$saldo = 0;
			}

			//Si es una factura
			if($ittipo_doc == 'F'){
				//Si el saldo es 0  o menor que el monto retenido genera un anticipo
				if($saldo==0 || $itmonto>$saldo){
					$mnumant = $this->datasis->fprox_sql('nancli');

					$data=array();
					$data['cod_cli']    = $cod_cli;
					$data['nombre']     = $nombre;
					$data['tipo_doc']   = 'AN';
					$data['numero']     = $mnumant;
					$data['fecha']      = $fecha;
					$data['monto']      = $itmonto;
					$data['impuesto']   = 0;
					$data['vence']      = $fecha;
					$data['tipo_ref']   = ($ittipo_doc='F')? 'FC' : 'DV';
					$data['num_ref']    = $itnumero;
					$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A DOC. '.$ittipo_doc.$itnumero;
					$data['usuario']    = $usuario;
					$data['estampa']    = $estampa;
					$data['hora']       = $hora;
					$data['transac']    = $transac;
					$data['nroriva']    = $numero;
					$data['emiriva']    = $efecha;

					$mSQL = $this->db->insert_string('smov', $data); 
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'RIVC'); }
				}else{
				//Si tiene saldo
					//Chequea que el monto de la retencion sea menor al saldo en caso tal crea una NC
					$mnumnc = $this->datasis->fprox_sql('nccli');
					$data=array();
					$data['cod_cli']    = $cod_cli;
					$data['nombre']     = $nombre;
					$data['tipo_doc']   = 'NC';
					$data['numero']     = $mnumnc;
					$data['fecha']      = $fecha;
					$data['monto']      = $itmonto;
					$data['impuesto']   = 0;
					$data['abonos']     = $itmonto;
					$data['vence']      = $fecha;
					$data['tipo_ref']   = ($ittipo_doc='F')? 'FC' : 'DV';
					$data['num_ref']    = $itnumero;
					$data['observa1']   = 'APLICACION DE RETENCION A DOC. '.$ittipo_doc.$itnumero;
					$data['estampa']    = $estampa;
					$data['hora']       = $hora;
					$data['transac']    = $transac;
					$data['usuario']    = $usuario;
					$data['codigo']     = 'NOCON';
					$data['descrip']    = 'NOTA DE CONTABILIDAD';
					$data['nroriva']    = $numero;
					$data['emiriva']    = $efecha;

					$mSQL = $this->db->insert_string('smov', $data); 
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'RIVC'); }

					// Abona la factura
					$tiposfac = ($ittipo_doc=='D')? $tiposfac = 'NC':'FC';
					$mSQL = "UPDATE smov SET abonos=abonos+$itmonto WHERE numero='$itnumero' AND cod_cli='$cod_cli' AND tipo_doc='$tiposfac'";
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'RIVC'); }
				}

				$mnumnd = $this->datasis->fprox_sql('ndcli');
				$data=array();
				$data['cod_cli']    = 'REIVA';
				$data['nombre']     = 'RETENCION DE I.V.A. POR COMPENSAR';
				$data['tipo_doc']   = 'ND';
				$data['numero']     = $mnumnd;
				$data['fecha']      = $fecha;
				$data['monto']      = $itmonto;
				$data['impuesto']   = 0;
				$data['abonos']     = 0;
				$data['vence']      = $fecha;
				$data['tipo_ref']   = ($ittipo_doc='F')? 'FC' : 'DV';
				$data['num_ref']    = $itnumero;
				$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A '.$ittipo_doc.$itnumero;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['transac']    = $transac;
				$data['usuario']    = $usuario;
				$data['codigo']     = 'NOCON';
				$data['descrip']    = 'NOTA DE CONTABILIDAD';
				$data['nroriva']    = $numero;
				$data['emiriva']    = $efecha;
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }
			}else{
			//Si es una devolucion
				// Devoluciones genera un ND al cliente
				$mnumnd = $this->datasis->fprox_sql('ndcli');
				$data=array();
				$data['cod_cli']    = $cod_cli;
				$data['nombre']     = $nombre;
				$data['tipo_doc']   = 'ND';
				$data['numero']     = $mnumnd;
				$data['fecha']      = $fecha;
				$data['monto']      = $itmonto;
				$data['impuesto']   = 0;
				$data['vence']      = $fecha;
				$data['tipo_ref']   = ($ittipo_doc='F')? 'FC' : 'DV';
				$data['num_ref']    = $itnumero;
				$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A '.$ittipo_doc.$itnumero;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['transac']    = $transac;
				$data['usuario']    = $usuario;
				$data['nroriva']    = $numero;
				$data['emiriva']    = $efecha;

				$mSQL = $this->db->insert_string('smov', $data); 
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }

				//Devoluciones debe crear un NC si esta en el periodo
				$mnumnc = $this->datasis->fprox_sql("nccli");
				$data=array();
				$data['cod_cli']    = 'REIVA';
				$data['nombre']     = 'RETENCION DE I.V.A. POR COMPENSAR';
				$data['tipo_doc']   = 'NC';
				$data['numero']     = $mnumnc;
				$data['fecha']      = $fecha;
				$data['monto']      = $itmonto;
				$data['impuesto']   = 0;
				$data['abonos']     = 0;
				$data['vence']      = $fecha;
				$data['tipo_ref']   = ($ittipo_doc='F')? 'FC' : 'DV';
				$data['num_ref']    = $itnumero;
				$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A '.$ittipo_doc.$itnumero;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['transac']    = $transac;
				$data['usuario']    = $usuario;
				$data['codigo']     = 'NOCON';
				$data['descrip']    = 'NOTA DE CONTABILIDAD';
				$data['nroriva']    = $numero;
				$data['emiriva']    = $efecha;

				$mSQL = $this->db->insert_string('smov', $data); 
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }
			}
		}

		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}

	function instalar(){
		if (!$this->db->table_exists('rivc')) {
			$mSQL="CREATE TABLE `rivc` (
			  `id` int(6) NOT NULL AUTO_INCREMENT,
			  `nrocomp` char(8) NOT NULL DEFAULT '',
			  `emision` date DEFAULT NULL,
			  `periodo` char(8) DEFAULT NULL,
			  `fecha` date DEFAULT NULL,
			  `cod_cli` char(5) DEFAULT NULL,
 			  `nombre` char(40) DEFAULT NULL,
 			  `rif` char(14) DEFAULT NULL,
			  `exento` decimal(15,2) DEFAULT NULL,
			  `tasa` decimal(5,2) DEFAULT NULL,
			  `general` decimal(15,2) DEFAULT NULL,
			  `geneimpu` decimal(15,2) DEFAULT NULL,
 			  `tasaadic` decimal(5,2) DEFAULT NULL,
			  `adicional` decimal(15,2) DEFAULT NULL,
			  `adicimpu` decimal(15,2) DEFAULT NULL,
			  `tasaredu` decimal(5,2) DEFAULT NULL,
			  `reducida` decimal(15,2) DEFAULT NULL,
			  `reduimpu` decimal(15,2) DEFAULT NULL,
			  `stotal` decimal(15,2) DEFAULT NULL,
			  `impuesto` decimal(15,2) DEFAULT NULL,
			  `gtotal` decimal(15,2) DEFAULT NULL,
			  `reiva` decimal(15,2) DEFAULT NULL,
			  `estampa` date DEFAULT NULL,
			  `hora` char(8) DEFAULT NULL,
			  `usuario` char(12) DEFAULT NULL,
			  `modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `transac` varchar(8) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `nrocomp_clipro` (`nrocomp`,`cod_cli`),
			  KEY `modificado` (`modificado`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('itrivc')) {
			$mSQL="CREATE TABLE `itrivc` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`idrivc` int(6) DEFAULT NULL,
			`tipo_doc` char(2) DEFAULT NULL,
			`fecha` date DEFAULT NULL,
			`numero` varchar(8) DEFAULT NULL,
			`nfiscal` char(12) DEFAULT NULL,
			`exento` decimal(15,2) DEFAULT NULL,
			`tasa` decimal(5,2) DEFAULT NULL,
			`general` decimal(15,2) DEFAULT NULL,
			`geneimpu` decimal(15,2) DEFAULT NULL,
			`tasaadic` decimal(5,2) DEFAULT NULL,
			`adicional` decimal(15,2) DEFAULT NULL,
			`adicimpu` decimal(15,2) DEFAULT NULL,
			`tasaredu` decimal(5,2) DEFAULT NULL,
			`reducida` decimal(15,2) DEFAULT NULL,
			`reduimpu` decimal(15,2) DEFAULT NULL,
			`stotal` decimal(15,2) DEFAULT NULL,
			`impuesto` decimal(15,2) DEFAULT NULL,
			`gtotal` decimal(15,2) DEFAULT NULL,
			`reiva` decimal(15,2) DEFAULT NULL,
			`transac` char(8) DEFAULT NULL,
			`estampa` date DEFAULT NULL,
			`hora` char(8) DEFAULT NULL,
			`usuario` char(12) DEFAULT NULL,
			`ffactura` date DEFAULT '0000-00-00',
			`modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `rivatra` (`transac`),
			UNIQUE KEY `tipo_doc_numero` (`tipo_doc`,`numero`),
			KEY `Numero` (`numero`),
			KEY `modificado` (`modificado`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED";
			$this->db->simple_query($mSQL);
		}
	}
}
