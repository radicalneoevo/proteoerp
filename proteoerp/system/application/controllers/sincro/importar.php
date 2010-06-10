<?php
class Importar extends Controller {

	function Importar(){
		parent::Controller();
		$this->load->helper('string');
		$this->load->library("rapyd");
		$this->load->library('encrypt');
		$this->sucu = $this->datasis->traevalor('NROSUCU');
		$this->clave=sha1($this->config->item('encryption_key'));

		$this->dir=reduce_double_slashes($this->config->item('uploads_dir').'/traspasos');
		//$this->dir='./uploads/traspasos/';
		//if(!file_exists('./uploads/traspasos'))	mkdir('./uploads/traspasos');
		
		if(empty($this->sucu)){
			redirect('supervisor/valores/dataedit/show/NROSUCU');
		}
	}

	function index(){
		$data['content'] = 'hola';
		$data['title']   = '<h1>Importar</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

//***********************
// Interfaces graficas
//***********************
	function uitraeg(){
		$this->rapyd->load('dataform');
		$sucu=$this->db->escape($this->sucu);
		$this->datasis->modulo_id('91E',1);

		$form = new DataForm("sincro/importar/uitraeg/process");
		
		$form->sucu = new dropdownField("Sucursal", "sucu");
		$form->sucu->rule ='required';
		$form->sucu->option("","Selecionar");
		$form->sucu->options("SELECT codigo, sucursal  FROM sucu WHERE codigo <> $sucu AND CHAR_LENGTH(url)>0");
		
		$form->qtrae = new dropdownField("Que traer?", "qtrae");
		$form->qtrae->rule ='required';
		$form->qtrae->option("","Selecionar");
		$form->qtrae->option("scli"  ,"Clientes");
		$form->qtrae->option("sinv"  ,"Inventario");
		$form->qtrae->option("maes"  ,"Inventario Supermercado");
		$form->qtrae->option("smov"  ,"Movimientos de clientes");
		$form->qtrae->option("transa","Facturas y transferencias");
		$form->qtrae->option("supermaes"  ,"Inventario Supermercado");
		
		$form->fecha = new dateonlyField("Fecha","fecha");
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->rule ="required|chfecha";
		$form->fecha->size =12;
		$form->submit("btnsubmit","Descargar");
		$form->build_form();

		$exito='';
		if ($form->on_success()){
			$fecha=$form->fecha->newValue;
			$sucu =$form->sucu->newValue;
			$obj='_'.str_replace('_','',$form->qtrae->newValue);
			if(method_exists($this,$obj))
				$rt=$this->$obj($sucu,$fecha);
			else
				$rt='Metodo no definido ('.$form->qtrae->newValue.')';
			if(strlen($rt)>0){
				$form->error_string=$rt;
				$form->build_form();
			}else{
				$exito='Transferencia &Eacute;xitosa';
			}
		}

		$data['content'] = $form->output.$exito;
		$data['title']   = '<h1>Importar data de Sucursal</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);

	}

	function uitrae($metodo=null){
		$obj='_'.str_replace('_','',$metodo); if(!method_exists($this,$obj)) show_404('page');
		$this->rapyd->load('dataform');
		$sucu=$this->db->escape($this->sucu);

		$form = new DataForm("sincro/importar/uitrae/$metodo/process");
		
		$form->sucu = new dropdownField("Sucursal", "sucu");
		$form->sucu->rule ='required';
		$form->sucu->option("","Selecionar");
		$form->sucu->options("SELECT codigo, sucursal  FROM sucu WHERE codigo <> $sucu");

		$form->fecha = new dateonlyField("Fecha","fecha");
		$form->fecha->insertValue = date("Y-m-d");
		$form->fecha->rule ="required|chfecha";
		$form->fecha->size =12;
		$form->submit("btnsubmit","Descargar");
		$form->build_form();

		$exito='';
		if ($form->on_success()){
			$fecha=$form->fecha->newValue;
			$sucu =$form->sucu->newValue;
			
			$rt=$this->$obj($sucu,$fecha);
			if(strlen($rt)>0){
				$form->error_string=$rt;
				$form->build_form();
			}else{
				$exito='Transferencia &Eacute;xitosa';
			}
		}

		$data['content'] = $form->output.$exito;
		$data['title']   = '<h1>Importar data de Sucursal ('.$metodo.')</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function uicarga(){
		set_time_limit(600);
		$this->rapyd->load('dataform');
		$this->load->library('Sqlinex');

		$form = new DataForm("sincro/importar/uicarga/process");

		$form->upl = new uploadField("Archivo Zip", "arch");
		$form->upl->upload_path = $this->dir;
		$form->upl->max_size     =6000;
		$form->upl->allowed_types = "zip";

		$form->submit("btnsubmit","Cargar");
		$form->build_form();

		$msg='';
		if ($form->on_success()){

			$nombre=$form->upl->upload_data['file_name'];
			$rt=$this->__cargazip($nombre);
			if(!empty($rt)){
				$form->error_string=$rt;
				$form->build_form();
			}else{
				$msg='<p>Carga completada</p>';
			}
		}

		$data['content'] = $form->output.$msg;
		$data['title']   = '<h1>Cargas de Zip</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function vendambul($sucu=null){
		set_time_limit(600);
		if(empty($sucu)) $sucu='01';
		$ssucu=$this->db->escape($sucu);
		
		$cant=$this->datasis->dameval("SELECT * FROM sucu WHERE codigo=$ssucu");
		if($cant>0){
			$rt=$this->__traerzip($sucu,'finanzas/exportar/vendambul');
			if ($rt)
				$msg='Hubo un error en la trasnferencia, se genero un ticket '.anchor('supervisor/tiket','ir a tickets');
			else
				$msg='Tranferencia &eacute;xitosa';
				
		}else{
			$msg='Error, la sucursal '.$sucu.' no existe, revise la configuracion aqui: '.anchor('supervisor/sucu','sucursales');
		}
		$data['content'] = $msg.'<p>'.anchor('inventario/fotos/traerfotos/'.$sucu,'Traer fotos de invetario').'</p>';
		$data['title']   = '<h1>Descarga de informaci&oacute;n para vendedores ambulantes</h1>';
		$data['script']  = '';
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

//**************************
// Correr desde Shell
//**************************
	function traetodosucu($principal,$fecha=null){
		if(isset($_SERVER['argv']) && !isset($_SERVER['SERVER_NAME'])){ //asegura que se ejecute desde shell

			if(empty($fecha)) $fecha = date('Ymd');

			$rt=$this->_scli($principal,null);
			$rt.=$this->_sinv($principal,null);
			echo $rt;
		}
	}
	
	function traetodoprin($fecha=null){
		if(isset($_SERVER['argv']) && !isset($_SERVER['SERVER_NAME'])){ //asegura que se ejecute desde shell

			$sucu=$this->sucu;
			$query = $this->db->query("SELECT * FROM sucu WHERE codigo<>$sucu");
			if(empty($fecha)) $fecha = date('Ymd');

			if ($query->num_rows() > 0){
				$rt='';
				foreach ($query->result() as $row){
					$rt.=$this->_smov($row->codigo,$fecha);
					$rt.=$this->_transa($row->codigo,$fecha);
					$rt.=$this->_fiscalz($row->codigo,$fecha);
				}
				echo $rt;
			}
		}
	}

	function traesclisucu($fecha=null){
		if(isset($_SERVER['argv']) && !isset($_SERVER['SERVER_NAME'])){ //asegura que se ejecute desde shell
			
			$sucu=$this->sucu;
			$query = $this->db->query("SELECT * FROM sucu WHERE codigo<>$sucu");
			if(empty($fecha)) $fecha = date('Ymd');

			if ($query->num_rows() > 0){
				$rt='';
				foreach ($query->result() as $row){
					$rt.=$this->_scli($row->codigo,$fecha);
				}
				echo $rt;
			}
		}
	}

	function traemaesalma($fecha=null,$sucu,$alma){
		if(isset($_SERVER['argv']) && !isset($_SERVER['SERVER_NAME'])){ //asegura que se ejecute desde shell
			if(empty($fecha)) $fecha = date('Ymd');
			$rt=$this->_maesalma($sucu,$fecha,$alma);
			echo $rt;
		}
	}

	function traetranalma($fecha=null,$sucu,$alma){
		if(isset($_SERVER['argv']) && !isset($_SERVER['SERVER_NAME'])){ //asegura que se ejecute desde shell
			if(empty($fecha)) $fecha = date('Ymd');
			$rt=$this->_tranalma($sucu,$fecha,$alma);
			echo $rt;
		}
	}



//**************************
//Metodos para traer data
//**************************

	function _scli($sucu,$fecha=null){
		set_time_limit(600);
		$rt=$this->__traerzip($sucu,'sincro/exportar/uri/'.$this->clave.'/scli/'.$fecha,'scli');
		return $rt;
	}

	function _sinv($sucu,$fecha=null){
		set_time_limit(600);
		$rt=$this->__traerzip($sucu,'sincro/exportar/uri/'.$this->clave.'/sinv','sinv');
		return $rt;
	}
	
	function _smov($sucu,$fecha=null){
		set_time_limit(600);
		$rt=$this->__traerzip($sucu,'sincro/exportar/uri/'.$this->clave.'/smov/'.$fecha,'smov');
		return $rt;
	}
	
	function _fiscalz($sucu,$fecha=null){
		set_time_limit(600);
		$rt=$this->__traerzip($sucu,'sincro/exportar/uri/'.$this->clave.'/fiscalz/'.$fecha,'fiscalz');
		return $rt;
	}
	
	function _transa($sucu,$fecha=null){
		set_time_limit(600);
		$rt=$this->__traerzip($sucu,'sincro/exportar/uri/'.$this->clave.'/transacciones/'.$fecha,'transacciones');
		return $rt;
	}
	
	function _supertransa($sucu,$fecha=null){
		set_time_limit(600);
		$rt=$this->__traerzip($sucu,'sincro/exportar/uri/'.$this->clave.'/supertransa/'.$fecha,'supertransa');
		return $rt;
	}

	function _maes($sucu,$fecha=null){
		set_time_limit(600);
		$rt=$this->__traerzip($sucu,'sincro/exportar/uri/'.$this->clave.'/maes/'.$fecha,'maes');
		return $rt;
	}

	function _tranalma($sucu,$fecha=null,$alma=null){
		set_time_limit(600);
		$rt=$this->__traerzip($sucu,'sincro/exportar/uri/'.$this->clave.'/tranalma/'.$fecha.'/'.$alma,'maesalma');
		return $rt;
	}

	function _maesalma($sucu,$fecha=null,$alma=null){
		set_time_limit(600);
		$rt=$this->__traerzip($sucu,'sincro/exportar/uri/'.$this->clave.'/maesalma/'.$fecha.'/'.$alma,'maesalma');
		return $rt;
	}

//***********************
//  Metodos de Chequeo
//***********************
	function __chekfecha($fecha){
		if(is_numeric($fecha) AND $fecha>10000000){
			$anio=substr($fecha,0,4);
			$mes =substr($fecha,4,2);
			$dia =substr($fecha,6);
			if(checkdate($mes,$dia,$anio))
				return TRUE;
		}
		return FALSE;
	}

//***********************
//  Metodos Generales
//***********************
	function __traerzip($sucu,$dir_url,$iden=null){
		$ssucu = $this->db->escape($sucu);
		$cc    = $this->datasis->dameval("SELECT COUNT(*) FROM sucu WHERE codigo=$ssucu");
		if($cc==0) return "Surursal no existe ($sucu)";
		set_time_limit(600);
		$this->load->library('Sqlinex');
		$sucu  = $this->db->escape($sucu);
		
		$query = $this->db->query("SELECT * FROM sucu WHERE codigo=$sucu");
		$fecha = date('Ymd');
		$error='';
		$dir   ='./uploads/traspasos/';

		if ($query->num_rows() > 0){
			$row = $query->row();
			$url=$row->url;
			$url=$row->url.'/'.$row->proteo.'/'.$dir_url;
			$url=reduce_double_slashes($url);
//echo 'http://'.$url;
			$ch = curl_init('http://'.$url);
			$tmpfname = tempnam($dir, "cargagen");

			$fp = fopen($tmpfname, "w");
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			$nombre=basename($tmpfname);
			$error=$this->__cargazip($nombre);

			if(!empty($error)){
				$atts = array(
				    'width'      => '800',
				    'height'     => '600',
				    'scrollbars' => 'yes',
				    'status'     => 'yes',
				    'resizable'  => 'yes',
				    'screenx'    => '0',
				    'screeny'    => '0'
				);
				
				$link=anchor_popup('sincro/importar/uitrae/'.$iden,'traer manual',$atts);
				$data['padre']      ='S';
				$data['prioridad']  ='5';
				$data['usuario']    ='TRANF';
				$data['contenido']  ="Error en transferencia: Sucursal: $this->sucu, Proceso: $iden, Fecha: $fecha, Mensaje: $error, ".$link;
				$data['estado']     ='N';
				$mSQL = $this->db->insert_string('tiket', $data);

				$this->db->simple_query($mSQL);
			}
		}
		return $error;
	}

	function __cargazip($nombre=null){
		set_time_limit(600);
		if(empty($nombre)) return 'Nombre vacio';

		//$dir = $this->dir.'/';
		$dir   ='./uploads/traspasos/';
		$zip = new ZipArchive;

		$ban=false;
		$res = $zip->open($dir.$nombre);

		if ($res === TRUE) {
			if($zip->numFiles==2){
				for($i=0;$i<2;$i++){
					if($zip->getNameIndex($i)!='firma.txt'){
						$exp_nombre=$zip->getNameIndex($i);
					}else{
						$ban=true;
					}
				}
				if(!$ban){
					unlink($dir.$nombre);
					return 'Archivo zip no firmado';
				}
				$sucu = $this->sucu;
				$dir_nom=$dir.str_replace('.','_',$nombre).'tmp';

				mkdir($dir_nom,0777);
				$zip->extractTo($dir_nom);
				$zip->close();
				$firma=file_get_contents($dir_nom.'/firma.txt');
				$firma=$this->encrypt->decode($firma);
				$datas=explode($this->sqlinex->separador,$firma);

				if(count($datas)==2){
					$sucursal=$datas[0];
					if($sucu==$sucursal){
						unlink($dir_nom.'/firma.txt');
						unlink($dir_nom.'/'.$exp_nombre);
						unlink($dir.$nombre);
						rmdir($dir_nom);
						return 'No se puede cargar el archivo en la misma sucursal que fue generada';
					}
					$firma = $datas[1];
				}else{
					$firma = $datas[0];
				}
				$firma2=md5_file($dir_nom.'/'.$exp_nombre);
				if($firma!=$firma2){
					unlink($dir_nom.'/firma.txt');
					unlink($dir_nom.'/'.$exp_nombre);
					unlink($dir.$nombre);
					rmdir($dir_nom);
					return 'Firmas no concuerdan';
				}

				$this->sqlinex->import($dir_nom.'/'.$exp_nombre);

				unlink($dir_nom.'/firma.txt');
				unlink($dir_nom.'/'.$exp_nombre);
				unlink($dir.$nombre);
				rmdir($dir_nom);
			}else{
				unlink($dir.$nombre);
				return 'El archivo zip no parece ser de importacion';
			}
		} else {
			unlink($dir.$nombre);
			return 'Error con el archivo zip';
		}
		return '';
	}
}
?>
