<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
class Monitoreo extends Controller{
	var $ser;

	function Monitoreo(){
		parent::Controller();
		$this->config->load('monitoreo');
		$this->datasis->modulo_id('922',1);
		$this->ser=$this->config->item('m_server');
	}

	function index(){
		$out='<table aling=\'center\' cellpadding=\'10\'><tr>';

		foreach($this->ser as $ind=>$val){
			$image_properties = array(
				'src'   => 'images/server.png',
				'alt'   => $val['titu'],
				'border'=> 0,
				'title' => $val['titu'],
				);
			$out.='<td><a href="'.site_url('supervisor/monitoreo/monitor/'.$ind).'">'.img($image_properties).'</a>'.br().$val['titu'].'</td>';
		}
		$out.='</tr></table>';

		$data['content'] = $out;
		$data['head']    = script('jquery.pack.js').script('jquery.treeview.pack.js').style('jquery.treeview.css');
		$data['title']   = '<h1>Equipos monitoreables</h1>';
		$this->load->view('view_ventanas', $data);
	}

	function monitor($id){
		$data['id'] = $id;
		$data['regresar'] = anchor('supervisor/monitoreo/index','Regresar');
		$this->load->view('view_monitor', $data);
	}

	function xml($ind=0){
		$host=$this->ser[$ind]['host'];
		header('content-type: text/xml');
		$pag = @file_get_contents($host.'/xml.php');
		if($pag == false){
			echo '<?xml version=\'1.0\'?>
<phpsysinfo>
  <Error>
    <Function>checkForExtensions</Function>
    <Message>Equipo no disponible.</Message>
  </Error>
</phpsysinfo>';
		}else{
			echo $pag;
		}
	}

	function wol(){
		if (!extension_loaded('sockets')) show_error('La extension "sockets" no esta cargada, debe cargarla para poder usar estas opciones');
		$this->load->library('rapyd');
		$this->rapyd->load('dataform');
		$form = new DataForm('supervisor/monitoreo/wol/process');

		$form->mac = new inputField('Direcci&oacute;n MAC', 'mac');
		$form->mac->append('Ejemplo: 00:01:02:03:04:05');
		$form->mac->rule = 'required|mac';
		$form->mac->maxlength =17;
		$form->mac->size =20;

		$form->submit('btnsubmit','Enviar');
		$form->build_form();

		if ($form->on_success()){
			$mac=$form->mac->newValue;

			$rt=$this->_wol($mac);
			if(!$rt){
				$form->error_string=$this->error;
				$form->build_form();
				$salida=$form->output.br();
			}else{
				$salida=$form->output.br().'Se&ntilde;al enviada satisfactoriamente';
			}
		}else{
			$salida=$form->output;
		}

		$this->rapyd->jquery[]='$(".inputnum").numeric(".");';
		$data['content'] = $salida;
		$data['title']   = heading('Envio de se&ntilde;al de encendido por LAN');
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _wol($mac, $host='255.255.255.255',$socket_number=7){
		$addr_byte = explode(':', $mac);
		$hw_addr   = $msg = '';

		for ($a=0; $a<6; $a++){
			$hw_addr .= chr(hexdec($addr_byte[$a]));
			$msg     .= chr(255);
		}

		for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;

		if (!$s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)){
			$this->error = 'No se pudo crear el socket';
			return false;
		}

		if (socket_set_option($s, 1, 6, TRUE) < 0){
			$this->error = 'setsockopt_fail';
			return false;
		}

		if (socket_sendto($s, $msg, strlen($msg), 0, $host, $socket_number)){
			$this->error = 'OK';
			socket_close($s);
			return true;
		}else{
			$this->error = 'send_fail';
			return false;
		}
	}

	function language(){
		header('content-type: text/xml');
		echo '<?xml version="1.0" encoding="utf-8"?>';
		?>
<tns:translation language="spanish" charset="utf-8"
 xmlns:tns="http://phpsysinfo.sourceforge.net/translation" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:schemaLocation="http://phpsysinfo.sourceforge.net/translation translation.xsd">
  <expression id="001" name="title"><exp>Información del Servidor</exp></expression>
  <expression id="002" name="vitals"><exp>Vitales</exp></expression>
  <expression id="003" name="hostname"><exp>Nombre del Servidor</exp></expression>
  <expression id="004" name="ip"><exp>Direcciones IP</exp></expression>
  <expression id="005" name="kversion"><exp>Versión del Kernel</exp></expression>
  <expression id="006" name="dversion"><exp>Nombre Distribución</exp></expression>
  <expression id="007" name="uptime"><exp>Último arranque</exp></expression>
  <expression id="008" name="users"><exp>Usuarios actuales</exp></expression>
  <expression id="009" name="loadavg"><exp>Promedio de uso</exp></expression>
  <expression id="010" name="hardware"><exp>Información del Hardware </exp></expression>
  <expression id="011" name="numcpu"><exp>Procesadores</exp></expression>
  <expression id="012" name="cpumodel"><exp>Modelo</exp></expression>
  <expression id="013" name="cpuspeed"><exp>Velocidad CPU</exp></expression>
  <expression id="014" name="busspeed"><exp>Velocidad BUS</exp></expression>
  <expression id="015" name="cache"><exp>Tamaño Cache</exp></expression>
  <expression id="016" name="bogomips"><exp>Sistema Bogomips</exp></expression>
  <expression id="017" name="pci"><exp>Dispositivos PCI</exp></expression>
  <expression id="018" name="ide"><exp>Dispositivos IDE</exp></expression>
  <expression id="019" name="scsi"><exp>Dispositivos SCSI</exp></expression>
  <expression id="020" name="usb"> <exp>Dispositivos USB</exp></expression>
  <expression id="021" name="netusage"><exp>Uso de la red</exp></expression>
  <expression id="022" name="device"><exp>Dispositivo</exp></expression>
  <expression id="023" name="received"><exp>Recibidos</exp></expression>
  <expression id="024" name="sent"><exp>Enviados</exp></expression>
  <expression id="025" name="errors"><exp>Errores/Perdidos</exp></expression>
  <expression id="026" name="connections"><exp>Conexiones de Red Establecidas</exp></expression>
  <expression id="027" name="memusage"><exp>Uso de la memoria</exp></expression>
  <expression id="028" name="phymem"><exp>Memoria Física</exp></expression>
  <expression id="029" name="swap"><exp>Disco Swap</exp></expression>
  <expression id="030" name="fs"><exp>Sistema archivos Montados</exp></expression>
  <expression id="031" name="mount"><exp>Punto de montaje</exp></expression>
  <expression id="032" name="partition"><exp>Partición</exp></expression>
  <expression id="033" name="percent"><exp>Uso</exp></expression>
  <expression id="034" name="type"><exp>Tipo</exp></expression>
  <expression id="035" name="free"><exp>Libre</exp></expression>
  <expression id="036" name="used"><exp>Usada</exp></expression>
  <expression id="037" name="size"><exp>Tamaño</exp></expression>
  <expression id="038" name="totals">    <exp>Total</exp></expression>
  <expression id="039" name="kb">    <exp>KB</exp>  </expression>
  <expression id="040" name="mb"><exp>MB</exp></expression>
  <expression id="041" name="gb"><exp>GB</exp></expression>
  <expression id="042" name="none"><exp>ninguno</exp></expression>
  <expression id="043" name="capacity"><exp>Tamaño</exp></expression>
  <expression id="044" name="template"><exp>Template</exp></expression>
  <expression id="045" name="language"><exp>Lenguaje</exp></expression>
  <expression id="046" name="submit"><exp>Enviado</exp></expression>
  <expression id="047" name="created"><exp>Creado por</exp></expression>
  <expression id="048" name="days"><exp>días</exp></expression>
  <expression id="049" name="hours"><exp>horas</exp></expression>
  <expression id="050" name="minutes"><exp>minutos</exp></expression>
  <expression id="051" name="temperature"><exp>Temperatura</exp></expression>
  <expression id="052" name="voltage"><exp>Voltaje</exp></expression>
  <expression id="053" name="fans"><exp>Ventiladores</exp></expression>
  <expression id="054" name="s_value"><exp>Valor</exp></expression>
  <expression id="055" name="s_min"><exp>Min</exp></expression>
  <expression id="056" name="s_max"><exp>Max</exp></expression>
  <expression id="057" name="hysteresis"><exp>Histéresis</exp></expression>
  <expression id="058" name="s_limit"><exp>Límite</exp></expression>
  <expression id="059" name="s_label"><exp>Etiqueta</exp></expression>
  <expression id="060" name="degreec"><exp>C</exp></expression>
  <expression id="061" name="degreef"><exp>F</exp></expression>
  <expression id="062" name="voltage_mark"><exp>V</exp></expression>
  <expression id="063" name="rpm_mark"><exp>RPM</exp></expression>
  <expression id="064" name="app"><exp>Kernel + aplicaciones</exp></expression>
  <expression id="065" name="buffers"><exp>Buffers</exp></expression>
  <expression id="066" name="cached"><exp>Cache;</exp></expression>
  <expression id="067" name="jumpto"><exp>Bifurcación</exp></expression>
  <expression id="068" name="ups_title"><exp>Información UPS</exp></expression>
  <expression id="069" name="ups_name"><exp>Nombre</exp></expression>
  <expression id="070" name="ups_model"><exp>Modelo</exp></expression>
  <expression id="071" name="ups_mode"><exp>Modo</exp></expression>
  <expression id="072" name="ups_start_time"><exp>Iniciado</exp></expression>
  <expression id="073" name="ups_status"><exp>Estado</exp></expression>
  <expression id="074" name="ups_outages_count"><exp>Interrupciones</exp></expression>
  <expression id="075" name="ups_last_outage"><exp>Causa última interrupción</exp></expression>
  <expression id="076" name="ups_last_outage_finish"><exp>Tiempo última interrupción</exp></expression>
  <expression id="077" name="ups_line_voltage"><exp>Voltaje de la línea</exp></expression>
  <expression id="078" name="ups_load_percent"><exp>Porcentaje cargado</exp></expression>
  <expression id="079" name="ups_battery_voltage"><exp>Voltaje batería</exp></expression>
  <expression id="080" name="ups_battery_charge_percent"><exp>Carga de la batería</exp></expression>
  <expression id="081" name="ups_time_left_minutes"><exp>Vida restante de las baterías</exp></expression>
  <expression id="082" name="ups_voltage_mark"><exp>V</exp></expression>
  <expression id="083" name="ups_minutes_mark"><exp>minutos</exp></expression>
  <expression id="084" name="ups_temperature"><exp>Temperatura</exp></expression>
  <expression id="085" name="tb"><exp>TB</exp></expression>
  <expression id="086" name="tib"><exp>TiB</exp></expression>
  <expression id="087" name="gib"><exp>GiB</exp></expression>
  <expression id="088" name="mib"><exp>MiB</exp></expression>
  <expression id="089" name="kib"><exp>KiB</exp></expression>
  <expression id="090" name="pib"><exp>PiB</exp></expression>
  <expression id="091" name="pb"><exp>PB</exp></expression>
  <expression id="092" name="mhz"><exp>MHz</exp></expression>
  <expression id="093" name="ghz"><exp>GHz</exp></expression>
</tns:translation>
<?php }
}
