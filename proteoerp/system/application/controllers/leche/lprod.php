<?php
class Lprod extends Controller {
	var $mModulo = 'LPROD';
	var $titp    = 'Control de producci&oacute;n';
	var $tits    = 'Control de producci&oacute;n';
	var $url     = 'leche/lprod/';

	function Lprod(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'LPROD', $ventana=0 );
	}

	function index(){
		$this->instalar();
		$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );
		$this->datasis->creaintramenu( $data = array('modulo'=>'223','titulo'=>'Control de Produccion','mensaje'=>'Control de Produccion','panel'=>'LECHE','ejecutar'=>'leche/lprod','target'=>'popu','visible'=>'S','pertenece'=>'2','ancho'=>900,'alto'=>600));
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$grid->setHeight('150');
		$param['grids'][] = $grid->deploy();

		$grid1   = $this->defgridit();
		$grid1->setHeight('180');
		$param['grids'][] = $grid1->deploy();

		// Configura los Paneles
		$readyLayout = $grid->readyLayout2( 212, 210, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"imprime", "img"=>"assets/default/images/print.png","alt" => 'Reimprimir',"label"=>"Reimprimir Documento"));
		$grid->wbotonadd(array("id"=>"bcierre", "img"=>"images/candado.png"             ,"alt" => 'Cierre'    ,"label"=>"Cierre"));
		$WestPanel = $grid->deploywestp();

		//Panel Central
		$centerpanel = $grid->centerpanel( $id = "radicional", $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		$adic = array(
			array("id"=>"fedita" , "title"=>"Agregar/Editar Pedido"),
			array("id"=>"fshow"  , "title"=>"Mostrar registro")
		);

		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']    = $WestPanel;
		$param['script']       = script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('LPROD', 'JQ');
		$param['otros']        = $this->datasis->otros('LPROD', 'JQ');
		$param['centerpanel']  = $centerpanel;
		$param['temas']        = array('proteo','darkness','anexos1');
		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;
		$param['tamano']       = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);

	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0, $grid1 ){
		$bodyscript = '<script type="text/javascript">';

		$bodyscript .= '
		function lprodadd() {
			$.post("'.site_url($this->url.'dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			});
		};';


		$bodyscript .= '
		function lproddel() {
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				if(confirm(" Seguro desea eliminar el registro?")){
					var ret    = $("#newapi'.$grid0.'").getRowData(id);
					mId = id;
					$.post("'.site_url($this->url.'dataedit/do_delete').'/"+id, function(data){
						$("#fborra").html(data);
						$("#fborra").dialog( "open" );
					});
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';


		$bodyscript .= '
		function lprodshow() {
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				$.post("'.site_url($this->url.'dataedit/show').'/"+id,
					function(data){
						$("#fshow").html(data);
						$("#fshow").dialog( "open" );
					});
			} else {
				$.prompt("<h1>Por favor Seleccione un registro</h1>");
			}
		};';

		$bodyscript .= '
		function lprodedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url($this->url.'dataedit/modify').'/"+id, function(data){
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				});
			} else { $.prompt("<h1>Por favor Seleccione un Registro</h1>");}
		};';

		//Wraper de javascript
		$bodyscript .= '
		$(function() {
			$("#dialog:ui-dialog").dialog( "destroy" );
			var mId = 0;
			var montotal = 0;
			var ffecha = $("#ffecha");
			var grid = jQuery("#newapi'.$grid0.'");
			var s;
			var allFields = $( [] ).add( ffecha );
			var tips = $( ".validateTips" );
			s = grid.getGridParam(\'selarrrow\');
			';

		$bodyscript .= '
		jQuery("#bcierre").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret   = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				$.post("'.site_url($this->url.'dataeditcierre/').'/"+ret.fecha+"/create",
				function(data){
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				});

			} else {
				$.prompt("<h1>Por favor Seleccione un Movimiento</h1>");
			}
		});';

		$bodyscript .= '
		$("#fshow").dialog({
			autoOpen: false, height: 500, width: 700, modal: true,
			buttons: {
				"Aceptar": function() {
					$( this ).dialog( "close" );
				},
			},
			close: function() {
				$("#fshow").html("");
				allFields.val( "" ).removeClass( "ui-state-error" );
			}
		});';


		$bodyscript .= '
		$("#fedita").dialog({
			autoOpen: false, height: 500, width: 700, modal: true,
			buttons: {
			"Guardar": function() {
				var bValid = true;
				var murl = $("#df1").attr("action");
				allFields.removeClass( "ui-state-error" );
				$.ajax({
					type: "POST", dataType: "html", async: false,
					url: murl,
					data: $("#df1").serialize(),
					success: function(r,s,x){
						try{
							var json = JSON.parse(r);
							if (json.status == "A"){
								apprise("Registro Guardado");
								$( "#fedita" ).dialog( "close" );
								grid.trigger("reloadGrid");
								//'.$this->datasis->jwinopen(site_url('formatos/ver/LRECE').'/\'+res.id+\'/id\'').';
								return true;
							} else {
								apprise(json.mensaje);
							}
						}catch(e){
							$("#fedita").html(r);
						}
					}
			})},
			"Cancelar": function() { $("#fedita").html(""); $( this ).dialog( "close" ); }
			},
			close: function() { $("#fedita").html(""); allFields.val( "" ).removeClass( "ui-state-error" );}
		});';
		$bodyscript .= '});'."\n";

		$bodyscript .= "\n</script>\n";
		$bodyscript .= "";
		return $bodyscript;
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('id');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 50,
			'editable'      => 'false',
			'search'        => 'false'
		));


		$grid->addField('codigo');
		$grid->label('C&oacute;digo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'left'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			//'editoptions'   => '{ size:5, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',

		));


		$grid->addField('descrip');
		$grid->label('Descripci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'left'",
			'edittype'      => "'text'",
			'width'         => 200,
			'editrules'     => '{ required:true }',
			//'editoptions'   => '{ size:20, maxlength: 20, dataInit: function (elem) { $(elem).numeric(); }  }',
		));


		$grid->addField('litros');
		$grid->label('Litros');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			//'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));

		$grid->addField('inventario');
		$grid->label('Inventario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));

		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){
				if (id){
					jQuery(gridId2).jqGrid("setGridParam",{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
					jQuery(gridId2).trigger("reloadGrid");
				}
			}'
		);
		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('LPROD','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('LPROD','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('LPROD','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('LPROD','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("addfunc: lprodadd, editfunc: lprodedit,delfunc: lproddel, viewfunc: lprodshow");

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdata(){
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('lprod');

		$response   = $grid->getData('lprod', array(array()), array(), false, $mWHERE, 'id','desc' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/****************************
	* Guarda la Informacion
	*/
	function setData(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$mcodp  = "??????";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM lprod WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('lprod', $data);
					echo "Registro Agregado";

					logusu('LPROD',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM lprod WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM lprod WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE lprod SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("lprod", $data);
				logusu('LPROD',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('lprod', $data);
				logusu('LPROD',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM lprod WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM lprod WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM lprod WHERE id=$id ");
				logusu('LPROD',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgridit( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		/*$grid->addField('id');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));


		$grid->addField('id_lprod');
		$grid->label('Id_lprod');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 0 }'
		));*/


		$grid->addField('codrut');
		$grid->label('Ruta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
		));


		$grid->addField('litros');
		$grid->label('Litros');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
		));


		$grid->setShrinkToFit('false');
		#Set url
		$grid->setUrlput(site_url($this->url.'setdatait/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdatait/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdatait( $id = 0 ){
		if ($id === 0 ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM lprod");
		}
		if(empty($id)) return "";

		$dbid  = $this->db->escape($id);
		$grid  = $this->jqdatagrid;
		$mSQL  = "SELECT * FROM itlprod WHERE id_lprod=$dbid";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setDatait(){
	}

	//***********************************
	// DataEdit
	//***********************************

	function dataeditcierre($urlfecha){
		$semana=array('DOMINGO','LUNES','MARTES','MIERCOLES','JUEVES','VIERNES');


		if(preg_match('/(?P<anio>\d{4})\-(?P<mes>\d{2})\-(?P<dia>\d{2})/', $urlfecha, $matches)>0){
			$fecha = date('Y-m-d', mktime(0, 0, 0, $matches['mes'], $matches['dia'], $matches['anio']));
			$dia   = $semana[date('w', mktime(0, 0, 0, $matches['mes'], $matches['dia'], $matches['anio']))];
		}else{
			$fecha= '';
			$dia  = '';
		}

		$this->rapyd->load('datadetails','dataobject');

		$do = new DataObject('lcierre');
		//$do->pointer('scli' ,'scli.cliente=rivc.cod_cli','sprv.tipo AS sprvtipo, sprv.reteiva AS sprvreteiva','left');
		$do->rel_one_to_many('itlcierre' ,'itlcierre' ,array('id'=>'id_lcierre'));

		$edit = new DataDetails($this->tits, $do);
		$edit->on_save_redirect=false;

		//$edit->post_process('insert','_post_insert');
		//$edit->post_process('update','_post_update');
		//$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert' ,'_pre_insert_lcierre');
		//$edit->pre_process('update' ,'_pre_update_lcierre');
		//$edit->pre_process('delete' ,'_pre_delete_lcierre');

		$edit->requeson = new inputField('Requeson','requeson');
		$edit->requeson->rule='required';
		$edit->requeson->size =12;
		$edit->requeson->maxlength =10;

		$edit->dia = new inputField('D&iacute;a','dia');
		$edit->dia->size =12;
		$edit->dia->maxlength =10;
		$edit->dia->type='inputhidden';
		$edit->dia->insertValue=$dia;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha|required';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;
		$edit->fecha->type='inputhidden';
		$edit->fecha->insertValue=$fecha;
		$edit->fecha->calendar=false;

		$edit->usuario = new autoUpdateField('usuario', $this->secu->usuario(), $this->secu->usuario());

		//Inicio del detalle
		$i  =0;
		$rel='itlcierre';
		$sel=array('a.codigo','a.descrip');

		$this->db->select($sel);
		$this->db->from('lprod AS a');

		$id = $edit->get_from_dataobjetct('id');
		if($id){
			$dbid=$this->db->escape($id);
			$this->db->join('itlcierre AS b','b.id_lcierre='.$dbid,'left');
		}
		$this->db->where('a.fecha',$fecha);
		$this->db->group_by('a.codigo');
		$this->db->order_by('a.codigo');

		$query = $this->db->get();
		$edit->detail_expand_except($rel);
		foreach ($query->result() as $row){
			$obj='itcodigo_'.$i;
			$edit->$obj = new inputField('Codigo',$obj);
			$edit->$obj->db_name = 'codigo';
			$edit->$obj->rule='max_length[15]|required';
			$edit->$obj->size =7;
			$edit->$obj->insertValue=$row->codigo;
			$edit->$obj->type='inputhidden';
			$edit->$obj->maxlength =4;
			$edit->$obj->rel_id = $rel;
			$edit->$obj->ind    = $i;

			$obj='itdescrip_'.$i;
			$edit->$obj = new inputField('',$obj);
			$edit->$obj->db_name = 'descrip';
			$edit->$obj->type='inputhidden';
			$edit->$obj->insertValue=$row->descrip;
			$edit->$obj->rel_id = $rel;
			$edit->$obj->ind    = $i;

			$obj='itcestas_'.$i;
			$edit->$obj = new inputField('Cestas',$obj);
			$edit->$obj->db_name = 'cestas';
			$edit->$obj->rule='max_length[12]|numeric|required';
			$edit->$obj->css_class='inputnum';
			$edit->$obj->size =14;
			$edit->$obj->maxlength =12;
			$edit->$obj->onkeyup='totalizar();';
			$edit->$obj->rel_id = $rel;
			$edit->$obj->ind    = $i;

			$obj='itunidades_'.$i;
			$edit->$obj = new inputField('Unidades',$obj);
			$edit->$obj->db_name = 'unidades';
			$edit->$obj->rule='max_length[12]|numeric|required';
			$edit->$obj->css_class='inputnum';
			$edit->$obj->size =14;
			$edit->$obj->maxlength =12;
			$edit->$obj->onkeyup='totalizar();';
			$edit->$obj->rel_id = $rel;
			$edit->$obj->ind    = $i;

			$i++;
		}
		$max_rel_count = $i;
		//Fin del detalle

		$edit->buttons('add_rel');
		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			//echo $edit->output;
			$conten['max_rel_count']=$max_rel_count;
			$conten['form']  =& $edit;
			$this->load->view('view_lcierre', $conten);
		}
	}

	function dataedit(){
		$this->rapyd->load('datadetails','dataobject');

		$do = new DataObject('lprod');
		//$do->pointer('scli' ,'scli.cliente=rivc.cod_cli','sprv.tipo AS sprvtipo, sprv.reteiva AS sprvreteiva','left');
		$do->rel_one_to_many('itlprod' ,'itlprod' ,array('id'=>'id_lprod'));

		$edit = new DataDetails($this->tits, $do);
		$edit->on_save_redirect=false;

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->codigo = new inputField('Producto','codigo');
		$edit->codigo->rule='required';
		//$edit->codigo->css_class='inputonlynum';
		$edit->codigo->size =12;
		$edit->codigo->maxlength =10;

		$edit->descrip = new inputField('Descripci&oacute;n','descrip');
		$edit->descrip->type='inputhidden';
		$edit->descrip->size =12;
		$edit->descrip->maxlength =10;

		//$edit->fecha = new dateField('Fecha','fecha');
		//$edit->fecha->rule='chfecha|required';
		//$edit->fecha->size =10;
		//$edit->fecha->maxlength =8;
		//$edit->fecha->calendar=false;

		$edit->inventario = new inputField('Leche de inventario','inventario');
		$edit->inventario->rule='max_length[12]|numeric|required';
		$edit->inventario->css_class='inputnum';
		$edit->inventario->size =12;
		$edit->inventario->onkeyup='totalizar();';
		$edit->inventario->maxlength =12;

		$edit->litros = new inputField('Litros totales','litros');
		$edit->litros->rule='max_length[12]|numeric';
		$edit->litros->css_class='inputnum';
		$edit->litros->type='inputhidden';
		$edit->litros->size =14;
		$edit->litros->maxlength =12;

		$edit->peso = new inputField('Peso','peso');
		$edit->peso->rule='max_length[12]|numeric';
		$edit->peso->css_class='inputnum';
		$edit->peso->type='inputhidden';
		$edit->peso->size =14;
		$edit->peso->maxlength =12;

		//Inicio del detalle
		$edit->itcodrut = new inputField('ruta','codrut_<#i#>');
		$edit->itcodrut->db_name = 'codrut';
		$edit->itcodrut->rule='max_length[4]|required';
		$edit->itcodrut->size =7;
		$edit->itcodrut->maxlength =4;
		$edit->itcodrut->rel_id   ='itlprod';

		$edit->itnombre = new inputField('ruta','itnombre_<#i#>');
		$edit->itnombre->db_name = 'nombre';
		$edit->itnombre->type='inputhidden';
		$edit->itnombre->size =14;
		$edit->itnombre->maxlength =12;
		$edit->itnombre->rel_id   ='itlprod';

		$edit->itlitros = new inputField('litros','itlitros_<#i#>');
		$edit->itlitros->db_name = 'litros';
		$edit->itlitros->rule='max_length[12]|numeric|required|mayorcero';
		$edit->itlitros->css_class='inputnum';
		$edit->itlitros->size =14;
		$edit->itlitros->maxlength =12;
		$edit->itlitros->onkeyup='totalizar();';
		$edit->itlitros->rel_id   ='itlprod';
		//Fin del detalle

		$edit->usuario = new autoUpdateField('usuario', $this->secu->usuario(), $this->secu->usuario());

		$edit->buttons('add_rel');
		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			//echo $edit->output;
			$conten['form']  =& $edit;
			$this->load->view('view_lprod', $conten);
		}
	}

	function _pre_insert_lcierre($do){
		$fecha  = $do->get('fecha');
		$dbfecha= $this->db->escape($fecha);
		$cana   = $this->datasis->dameval("SELECT COUNT(*) FROM lcierre WHERE fecha=".$dbfecha);

		if($cana>0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert'] = 'Ya existe un cierre para el d&iacute;a '.dbdate_to_human($fecha).' no puede realizar otro.';
			return false;
		}
		return true;
	}

	function _pre_insert($do){
		$do->set('fecha',date('Y-m-d'));
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
		if(!$this->db->table_exists('lprod')){
			$mSQL = "
			CREATE TABLE `lprod` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`descrip` VARCHAR(45) NULL DEFAULT NULL,
				`fecha` DATE NULL DEFAULT NULL,
				`peso` DECIMAL(12,2) NULL DEFAULT NULL,
				`litros` DECIMAL(12,2) NULL DEFAULT NULL,
				`inventario` DECIMAL(12,2) NULL DEFAULT NULL,
				`estampa` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				`usuario` VARCHAR(15) NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COMMENT='Control de produccion de lacteos'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM;";
			$this->db->simple_query($mSQL);
		}


		if(!$this->db->table_exists('itlprod')){
			$mSQL = "
			CREATE TABLE `itlprod` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`id_lprod` INT(10) NOT NULL DEFAULT '0',
				`codrut` CHAR(4) NOT NULL DEFAULT '0',
				`nombre` VARCHAR(50) NOT NULL DEFAULT '0',
				`litros` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
				PRIMARY KEY (`id`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			AUTO_INCREMENT=0;";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('lcierre')){
			$mSQL = "CREATE TABLE `lcierre` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`fecha` DATE NULL DEFAULT NULL,
				`dia` VARCHAR(50) NULL DEFAULT NULL,
				`recepcion` DECIMAL(12,2) NULL DEFAULT NULL,
				`enfriamiento` DECIMAL(12,2) NULL DEFAULT NULL,
				`requeson` DECIMAL(12,2) NULL DEFAULT NULL,
				`requesonteorico` DECIMAL(12,2) NULL DEFAULT NULL,
				`requesonreal` DECIMAL(12,2) NULL DEFAULT NULL,
				`usuario` VARCHAR(50) NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COMMENT='Cierre de produccion de lacteos'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM;";
			$this->db->simple_query($mSQL);
		}

		if(!$this->db->table_exists('itlcierre')){
			$mSQL = "CREATE TABLE `itlcierre` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`id_lcierre` INT(10) NULL DEFAULT NULL,
				`codigo` VARCHAR(15) NULL DEFAULT NULL,
				`descrip` VARCHAR(45) NULL DEFAULT NULL,
				`unidades` DECIMAL(10,2) NULL DEFAULT NULL,
				`cestas` DECIMAL(10,2) NULL DEFAULT NULL,
				`peso` DECIMAL(10,2) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				INDEX `id_lcierre` (`id_lcierre`)
			)
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM;";
			$this->db->simple_query($mSQL);
		}

	}

}
