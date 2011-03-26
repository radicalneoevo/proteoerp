<?php
require_once(BASEPATH.'application/controllers/validaciones.php');
require_once(APPPATH.'/controllers/finanzas/gser.php');
class Mgas extends validaciones {

	function mgas(){
		parent::Controller(); 
		$this->load->library("rapyd");
		gser::instalar();
	}

	function index(){
		$this->datasis->modulo_id(511,1);
		redirect("finanzas/mgas/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$modbus=array(
		  'tabla'   =>'grga',
		  'columnas'=>array(
					'grupo' =>'Codigo',
					'nom_grup'=>'Descripci&oacute;n'
				   ),
		  'filtro'  =>array('grupo'=>'C&oacute;digo','nom_grup'=>'Descripci&oacute;n'),
		  'retornar'=>array('grupo'=>'grupo'),
		'titulo'  =>'Buscar Grupo de Gastos');

		$boton=$this->datasis->modbus($modbus);

		$filter = new DataFilter("Filtro Maestro de Gastos", 'mgas');

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo->size=10;
		$filter->codigo->group = 'UNO';

		$filter->cuenta = new inputField('Cuenta contable', 'cuenta');
		$filter->cuenta->size=15;
		$filter->cuenta->like_side='after';
		$filter->cuenta->group = 'UNO';

		$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->size=20;
		$filter->descrip->group = 'DOS';
		
		$filter->grupo = new inputField("Grupo", "grupo");
		$filter->grupo->size=10;
		$filter->grupo->append($boton);
		$filter->grupo->group = 'DOS';

		$filter->buttons("reset","search");
		$filter->build("dataformfiltro");

		$uri = anchor('finanzas/mgas/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid("Lista de Maestro de Gastos");
		$grid->order_by("codigo","asc");
		$grid->per_page = 15;

		$grid->column_orderby("C&oacute;digo",$uri ,'codigo');
		$grid->column_orderby("Tipo","tipo","tipo");
		$grid->column_orderby("Descripci&oacute;n","descrip",'descrip');
		$grid->column_orderby("Grupo","grupo",'grupo');
		$grid->column_orderby('Nombre del Grupo','nom_grup','nom_grup');

		$grid->add("finanzas/mgas/dataedit/create");
		$grid->build();

		$data['filtro'] = $filter->output;
		$data['content'] = $grid->output;
		$data['title']   = "<h1>Maestro de Gastos</h1>";
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);	
	}

	function dataedit(){
		$this->rapyd->load("dataedit");
		$link=site_url('finanzas/mgas/ultimo');

		$script ='
		function ultimo(){
			$.ajax({
				url: "'.$link.'",
				success: function(msg){
				  alert( "El ultimo codigo ingresado fue: " + msg );
			}
		});
		}
		$(function() {
			$(".inputnum").numeric(".");
			$("#grupo").change(function(){grupo();}).change();
		});
		function grupo(){
			t=$("#grupo").val();
			a=$("#grupo :selected").text();
			$("#nom_grup").val(a);
		}';

		$mCPLA=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta'),
			'titulo'  =>'Buscar Cuenta',
		);
		$bcpla =$this->datasis->modbus($mCPLA);

		$atts = array(
				'width'     =>'800',
				'height'    =>'600',
				'scrollbars'=>'yes',
				'status'    =>'yes',
				'resizable' =>'yes',
				'screenx'   =>'5',
				'screeny'   =>'5');

		$edit = new DataEdit("Maestro de Gastos", "mgas");
		$edit->script($script, "create");
		$edit->script($script, "modify");
		$edit->back_url = site_url("finanzas/mgas/filteredgrid");

		$ultimo='<a href="javascript:ultimo();" title="Consultar ultimo codigo ingresado" onclick="">Consultar ultimo codigo</a>';
		$edit->codigo= new inputField("C&oacute;digo", "codigo");
		$edit->codigo->mode="autohide";
		$edit->codigo->size = 12;
		$edit->codigo->maxlength = 6;
		$edit->codigo->rule = "trim|required|callback_chexiste";
		$edit->codigo->append($ultimo);

		$edit->descrip= new inputField("Descripci&oacute;n", "descrip");
		$edit->descrip->size = 35;
		//$edit->descrip->readonly=true;

		$edit->tipo= new dropdownField("Tipo", "tipo");
		$edit->tipo->style ="width:100px;";
		$edit->tipo->option("G","Gasto");
		//$edit->tipo->option("G","Gasto");
		$edit->tipo->option("I","Inventario");
		$edit->tipo->option("S","Suministro");
		$edit->tipo->option("A","Activo Fijo");

		$edit->grupo= new dropdownField("Grupo", "grupo");
		$edit->grupo->options('SELECT grupo, CONCAT(grupo," - ",nom_grup) nom_grup from grga order by nom_grup');
		$edit->grupo->style ="width:250px;";
		$edit->grupo->onchange ="grupo();";

		//$edit->nom_grup  = new inputField("nom_grup", "nom_grup");

		$lcuent=anchor_popup("/contabilidad/cpla/dataedit/create","Agregar Cuenta Contable",$atts);
		$edit->cuenta    = new inputField("Cuenta Contable", "cuenta");
		$edit->cuenta->size = 12;
		$edit->cuenta->maxlength = 15;
		$edit->cuenta->rule = "trim|callback_chcuentac";
		$edit->cuenta->append($bcpla);
		$edit->cuenta->append($lcuent);
		$edit->cuenta->readonly=true;

		$edit->iva       = new inputField("Iva", "iva");
		$edit->iva->css_class='inputnum';
		$edit->iva->size =12;
		$edit->iva->maxlength =5;
		$edit->iva->rule ="trim";

		$edit->medida    = new inputField("Unidad Medida", "medida");
		$edit->medida->size = 10;  

		$edit->fraxuni   = new inputField("Cantidad por Caja", "fraxuni");
		$edit->fraxuni->css_class='inputnum';//no sirve
		$edit->fraxuni->group = 'Existencias';
		$edit->fraxuni->size = 10;

		$edit->ultimo    = new inputField("Ultimo Costo", "ultimo");
		$edit->ultimo->css_class='inputnum';//no sirve
		$edit->ultimo->size = 15;

		$edit->promedio  = new inputField("Costo Promedio", "promedio");
		$edit->promedio->css_class='inputnum';//no sirve
		$edit->promedio->size = 15;

		$edit->minimo    = new inputField("Existencia M&iacute;nima", "minimo");
		$edit->minimo->css_class='inputnum';//no sirve
		$edit->minimo->group = 'Existencias';
		$edit->minimo->size = 10;

		$edit->maximo    = new inputField("Existencia M&aacute;xima", "maximo");
		$edit->maximo->css_class='inputnum';//no sirve
		$edit->maximo->group = 'Existencias';
		$edit->maximo->size = 10;

		$edit->unidades  = new inputField("Existencia Actual en Unidades o Cajas", "unidades");
		$edit->unidades->css_class='inputnum';//no sirve
		$edit->unidades->group = 'Existencias';
		$edit->unidades->size = 5;

		$edit->fraccion  = new inputField("Existencia Actual en Fracci&oacute;nes", "fraccion");
		$edit->fraccion->css_class='inputnum';//no sirve
		$edit->fraccion->group = 'Existencias';
		$edit->fraccion->size = 5;

		$edit->rica= new dropdownField("Impuesto Ind.Com.", "rica");
		$edit->rica->options('SELECT codigo, CONCAT(codigo," - ",aplica) aplica FROM rica order by aplica');
		$edit->rica->style ="width:350px;";
		//$edit->grupo->onchange ="grupo();";

/*
		$edit->tasa1     = new inputField("Tasa1", "tasa1");
		$edit->tasa1->size = 5;

		$edit->base1     = new inputField("Base1", "base1");
		$edit->base1->size = 12;

		$edit->desde1    = new inputField("Desde1", "desde1");
		$edit->desde1->size = 12;

		$edit->tasa2     = new inputField("Tasa2", "tasa2");
		$edit->tasa2->size = 5;

		$edit->base2     = new inputField("Base2", "base2");
		$edit->base2->size = 12;

		$edit->desde2    = new inputField("Desde2", "desde2");
		$edit->desde2->size = 12;

		$edit->tasa3     = new inputField("Tasa3", "tasa3");
		$edit->tasa3->size = 5;

		$edit->base3     = new inputField("Base3", "base3");
		$edit->base3->size = 12;

		$edit->desde3    = new inputField("Desde3", "desde3");
		$edit->desde3->size = 12;

		$edit->tasa4     = new inputField("Tasa4", "tasa4");
		$edit->tasa4->size = 5;

		$edit->base4     = new inputField("Base4", "base4");
		$edit->base4->size = 12;

		$edit->desde4    = new inputField("Desde4", "desde4");
		$edit->desde4->size = 12;

		$edit->amorti    = new inputField("Amort/Dep","amorti");
		$edit->amorti->size =15;

		$edit->dacumu    = new inputField("D.Acum","dacumu");
		$edit->dacumu->size =15;
*/
		$codigo=$edit->_dataobject->get("codigo");
		$edit->almacenes = new containerField('almacenes',$this->_detalle($codigo));
		$edit->almacenes->when = array("show","modify");

		$edit->buttons("modify", "save", "undo", "back");
		$edit->build();

		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_mgas', $conten,true);
		$data["head"]    =script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		
		//$data["head"]    =script("tabber.js").script("prototype.js").script("sinvmaes.js").script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Maestro de Gasto</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function chexiste(){
		$codigo=$this->input->post('codigo');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM mgas WHERE codigo='$codigo'");
		if ($chek > 0){
			$nombre=$this->datasis->dameval("SELECT descrip FROM mgas WHERE codigo='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el gasto $nombre");
			return FALSE;
		}
	}

	function ultimo(){
		$ultimo=$this->datasis->dameval("SELECT codigo FROM mgas ORDER BY codigo DESC");
		echo $ultimo;
	}

	function _detalle($codigo){
		$salida='';
		/*
		if(!empty($codigo)){
			$this->rapyd->load('dataedit','datagrid'); 

			$grid = new DataGrid('Cantidad por almac&eacute;n');
			$grid->db->select('ubica,locali,cantidad,fraccion');
			$grid->db->from('ubic');
			$grid->db->where('codigo',$codigo);
			
			$grid->column("Almacen"          ,"ubica" );
			$grid->column("Ubicaci&oacute;n" ,"locali");
			$grid->column("Cantidad"         ,"cantidad",'align="RIGHT"');
			$grid->column("Fracci&oacute;n"  ,"fraccion",'align="RIGHT"');
			
			$grid->build();
			$salida=$grid->output;
		}*/
		return $salida;
	}
  
	function mgasconsulta(){  
		$this->rapyd->load("datagrid");
		$fields = $this->db->field_data('mgas');
		$url_pk = $this->uri->segment_array();
		$coun=0; $pk=array();
		foreach ($fields as $field){
			if($field->primary_key==1){
				$coun++;
				$pk[]=$field->name;
			}
		}
		
		$values=array_slice($url_pk,-$coun);
		$claves=array_combine (array_reverse($pk) ,$values );

		$grid = new DataGrid('Ultimos Movimientos');
		$grid->db->select( array('a.fecha', 'a.numero','a.descrip', 'a.proveed', 'b.nombre', 'a.precio', 'a.iva', 'a.importe') );
		$grid->db->from('gitser a');
		$grid->db->join('sprv b','a.proveed=b.proveed');
		$grid->db->where('a.codigo', $claves['codigo'] );
		$grid->db->orderby('fecha DESC');
		$grid->db->limit(6);
			
		$grid->column("Fecha"   ,"fecha" );
		$grid->column("Descripcion"   ,"descrip" );
		$grid->column("Proveed" ,"proveed");
		//$grid->column("Nombre"  ,"nombre");
		$grid->column("Monto"   ,"<nformat><#precio#></nformat>",'align="RIGHT"');
		$grid->build();

		$grid1 = new DataGrid('Totales por Mes');
		$grid1->db->select( array('a.fecha', 'a.descrip', 'a.proveed', 'b.nombre', 'sum(a.precio) monto', 'a.iva', 'a.importe') );
		$grid1->db->from('gitser a');
		$grid1->db->join('sprv b','a.proveed=b.proveed');
		$grid1->db->where('a.codigo', $claves['codigo'] );
		$grid1->db->groupby('fecha DESC ');
		$grid1->db->limit(6);
			
		$grid1->column("Fecha"   ,"fecha" );
		$grid1->column("Monto"   ,"<nformat><#monto#></nformat>",'align="RIGHT"');
			
		$grid1->build();

		$grid2 = new DataGrid('Totales por Proveedor');
		$grid2->db->select( array('a.fecha', 'a.proveed', 'b.nombre', 'sum(a.precio) monto') );
		$grid2->db->from('gitser a');
		$grid2->db->join('sprv b','a.proveed=b.proveed');
		$grid2->db->where('a.codigo', $claves['codigo'] );
		$grid2->db->groupby('a.proveed');
		$grid2->db->orderby('monto DESC ');
		$grid2->db->limit(6);
			
		$grid2->column("Proveed" ,"proveed");
		$grid2->column("Nombre"  ,"nombre");
		$grid2->column("Monto"   ,"<nformat><#monto#></nformat>",'align="RIGHT"');
		
		$grid2->build();

		$data['content'] = "<table width='100%'><tr><td valign='top' style='background:#DFDFEF'>".
				$grid1->output.
				"</td><td valign='top' style='background:#DFEFDF'>".
				$grid2->output."</td></tr><tr><td colspan='2'  style='background:#FFFDE9'>".
				$grid->output."</td></tr></table>";
		$data["head"]    = script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']   = '<h1>Consultas</h1>';

		$this->load->view('view_ventanas', $data);
		
	}
/*
	function sinvgrupos(){
		$this->rapyd->load("fields");  
		$where = "";  
		$line=$this->input->post('line');
		$dpto=$this->input->post('dpto'); 
		
		$grupo = new dropdownField("Grupo", "grupo");
		if ($line AND $dpto AND !(empty($line) OR empty($dpto))) {
			$where .= "WHERE depto = ".$this->db->escape($dpto);
			$where .= "AND linea = ".$this->db->escape($line);
			$sql = "SELECT grupo, nom_grup FROM grup $where";
			$grupo->option("","");
			$grupo->options($sql);
		}else{
			$grupo->option("","Seleccione una linea"); 
		} 
		$grupo->status = "modify";  
		$grupo->build();
		echo $grupo->output; 
	}*/
}
