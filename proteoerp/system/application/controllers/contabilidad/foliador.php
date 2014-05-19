<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
class Foliador extends Controller {

	function Foliador(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(601,1);
	}

	function index() {
		$this->rapyd->load('datagrid','dataform');


		$form = new DataForm('contabilidad/foliador/index/process');
		$form->title('Fecha para la ejecuci&oacute;n');

		$size=15;

		$form->desde = new inputField('Desde', 'desde');
		$form->desde->rule='required|numeric';
		$form->desde->group='Numeraci&oacute;n';
		$form->desde->size=20;
		$form->desde->autocomplete=false;

		$form->hasta = new inputField('Hasta', 'hasta');
		$form->hasta->rule='required|numeric|callback_chmayor';
		$form->hasta->group='Numeraci&oacute;n';
		$form->hasta->size=20;
		$form->hasta->autocomplete=false;

		$form->encab = new containerField('encab','');
		$form->encab->group='Encabezado de p&aacute;gina';

		$form->ei = new inputField('ei', 'ei');
		$form->ei->size=$size;
		$form->ei->in='encab';

		$form->ec = new inputField('ec', 'ed');
		$form->ec->size=$size;
		$form->ec->in='encab';

		$form->ed = new inputField('ed', 'ed');
		$form->ed->size=$size;
		$form->ed->in='encab';

		$form->pie = new containerField('pie','');
		$form->pie->group='Pie de p&aacute;gina';

		$form->pi = new inputField('pi', 'pi');
		$form->pi->size=$size;
		$form->pi->in='pie';

		$form->pc = new inputField('pc', 'pd');
		$form->pc->size=$size;
		$form->pc->in='pie';

		$form->pd = new inputField('pd', 'pd');
		$form->pd->size=$size;
		$form->pd->in='pie';

		$form->formato = new dropdownField('Formato', 'formato');
		$form->formato->option('pdf','PDF');
		$form->formato->option('prn','PRN');
		$form->formato->style='width:80px;';
		$form->formato->rule  = 'required|enum[pdf,prn]';
		$form->formato->group = 'Opciones';

		$form->submit = new submitField('Generar','btn_submit');
		$form->submit->in='formato';
		$form->submit->group = 'Opciones';

		$form->build_form();


		if ($form->on_success()){
			$desde= intval($form->desde->newValue);
			$hasta= intval($form->hasta->newValue);
			$diff = $hasta-$desde;

			if($diff>0){
				$esc= '"\'';
				$ei = (!empty($form->ei->newValue))? addcslashes($form->ei->newValue,$esc): null;
				$ec = (!empty($form->ec->newValue))? addcslashes($form->ec->newValue,$esc): null;
				$ed = (!empty($form->ed->newValue))? addcslashes($form->ed->newValue,$esc): null;
				$pi = (!empty($form->pi->newValue))? addcslashes($form->pi->newValue,$esc): null;
				$pc = (!empty($form->pc->newValue))? addcslashes($form->pc->newValue,$esc): null;
				$pd = (!empty($form->pd->newValue))? addcslashes($form->pd->newValue,$esc): null;

				$encab = '"'.implode('","',array($ei,$ec,$ed)).'"';
				$piso  = '"'.implode('","',array($pi,$pc,$pd)).'"';

				if($form->formato->newValue=='pdf'){

					$this->load->library('dompdf/cidompdf');
					$cont='';
					for($i=0;$i<=$diff;$i++){
						$cont.='<div style="page-break-before: always;"></div>';
					}

					$html='<html>
					<style>
						.move-ahead { counter-increment: page 2; position: absolute; visibility: hidden; }
						.pagenum:after { content:\' \' counter(page); }
					</style>

					<body>
					<script type="text/php">
						if(isset($pdf)){


							$texto = array();
							$font  = Font_Metrics::get_font("verdana");
							$size  = 6;
							$color = array(0,0,0);
							$text_height = Font_Metrics::get_font_height($font, $size);
							$w     = $pdf->get_width();
							$h     = $pdf->get_height();
							$y     = $h - $text_height - 24;

							//***Inicio cuadro
							//**************VARIABLES MODIFICABLES***************
							$texto = array('.$encab.');

							$cuadros = 0;   //Cantidad de cuadros (en caso de ser 0 calcula la cantidad)
							$margenh = 40;  //Distancia desde el borde derecho e izquierdo
							$margenv = $h;  //Distancia desde el borde inferior
							$alto    = 50;  //Altura de los cuadros
							$size    = 9;   //Tamanio del texto en los cuadros
							$color   = array(0,0,0); //Color del marco
							$lcolor  = array(0,0,0); //Color de la letra
							////**************************************************


							$cuadros = ($cuadros>0) ? $cuadros : count($texto);
							$cuadro  = $pdf->open_object();
							$margenl = $margenv-$alto+$text_height+5;    //Margen de la letra desde el borde inferior
							$ancho   = intval(($w-2*$margenh)/$cuadros); //Ancho de cada cuadro
							for($i=0;$i<$cuadros;$i++){
								if(isset($texto[$i])){
									$width = Font_Metrics::get_text_width($texto[$i],$font,$size);
									$pdf->page_text($margenh+$i*$ancho+intval($ancho/2)-intval($width/2), $h-$margenl, $texto[$i], $font, $size, $lcolor);
								}
							}
							//***Fin del cuadro

							$pdf->close_object();
							$pdf->add_object($cuadro,"add");


							$text = "PP {PAGE_NUM} de {PAGE_COUNT}";

							// Center the text
							$width = Font_Metrics::get_text_width("PP 1 de 2", $font, $size);
							$pdf->page_text($w / 2 - $width / 2, $y, $text, $font, $size, $color);

						}
					</script>'.$cont.'</body>
					</html>';

					$this->cidompdf->html2pdf($html,'foliador.pdf');

				}elseif($form->formato->newValue=='prn'){


				}
			}
		}

		$data['script']="<script type='text/javascript'></script>";

		$data['content'] = $form->output;
		$data['head']    = $this->rapyd->get_head();
		$data['title']   = heading('Foliador');
		$this->load->view('view_ventanas', $data);
	}

	function chmayor($hasta){

		$desde=intval($this->input->post('desde'));
		$hasta=intval($hasta);
		if($hasta>$desde){
			return true;
		}else{
			$this->validation->set_message('chmayor', 'El campo "%s" debe ser mayor');
			return false;
		}
	}

}
