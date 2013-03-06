<?php
$maxlin=39; //Maximo de lineas de items.

if(count($parametros)==0) show_error('Faltan parametros');
$id   = $parametros[0];
$dbid = $this->db->escape($id);
//Para esconder o no los precios
if(isset($parametros[1])){
	if($parametros[1]=='S'){
		$mprec=false;
	}else{
		$mprec=true;
	}
}else{
	$mprec=true;
}

$mSQL = "
SELECT a.numero,a.cod_cli,c.nombre,TRIM(c.nomfis) AS nomfis,c.rifci,CONCAT_WS('',TRIM(c.dire11), c.dire12) AS direccion,a.fecha,a.vende AS vd,
	a.impuesto AS iva,a.stotal AS totals,a.gtotal AS totalg, b.nombre AS nomvend,
	a.peso,c.telefono, a.observa
FROM snte AS a
JOIN scli AS c ON a.cod_cli=c.cliente
LEFT JOIN vend AS b ON a.vende=b.vendedor
WHERE a.id=${dbid}";

$mSQL_1 = $this->db->query($mSQL);
if($mSQL_1->num_rows()==0) show_error('Registro no encontrado');
$row = $mSQL_1->row();

$fecha    = dbdate_to_human($row->fecha);
$numero   = $row->numero;
$cod_cli  = htmlspecialchars(trim($row->cod_cli));
$rifci    = htmlspecialchars(trim($row->rifci));
$nombre   = htmlspecialchars(trim($row->nombre));
$stotal   = nformat($row->totals);
$gtotal   = nformat($row->totalg);
$iva      = nformat($row->iva);
$observa  = htmlspecialchars(trim($row->observa));

$peso     = nformat($row->peso);
$impuesto = nformat($row->iva);
$direc    = htmlspecialchars(trim($row->direccion));
$telefono = htmlspecialchars(trim($row->telefono));
$nomvend  = htmlspecialchars(trim($row->nomvend));

$dbnumero   = $this->db->escape($numero);

$lineas = 0;
$uline  = array();

$mSQL="SELECT a.codigo,b.descrip AS desca,a.cana,a.precio AS preca,a.importe,a.iva
FROM itsnte AS a
JOIN sinv AS b ON a.codigo=b.codigo
WHERE a.numero=$dbnumero";

$mSQL_2 = $this->db->query($mSQL);
$detalle  = $mSQL_2->result();
?><html>
<head>
<title>NOTA DE ENTREGA <?php echo $numero ?></title>
<link rel="stylesheet" href="<?php echo $this->_direccion ?>/assets/default/css/formatos.css" type="text/css" />
</head>
<body style="margin-left: 30px; margin-right: 30px;">
<?php
//************************
//     Encabezado
//
//************************
$encabezado = "
	<table style='width:100%;font-size: 9pt;' class='header' cellpadding='0' cellspacing='0'>
		<tr>
			<td><h1 style='text-align:left;border-bottom:1px solid;font-size:12pt;'>NOTA DE ENTREGA Nro. ${numero}</h1></td>
			<td><h1 style='text-align:right;border-bottom:1px solid;font-size:12pt;'>FECHA: ${fecha}</h1></td>
		</tr><tr>
			<td>Cliente: <b>(${cod_cli}) ${nombre}</b></td>
			<td>RIF/CI: <b>${rifci}</b></td>
		</tr><tr>
			<td>Direcci&oacute;n: <b>${direc}</b></td>
			<td>Tel&eacute;fono:  <b>${telefono}</b></td>
		</tr><tr>
			<td colspan='2'>Observaci&oacute;n: <b>${observa}</bb></td>
		</tr>
	</table>
";
// Fin  Encabezado

//************************
//   Encabezado Tabla
//************************
$estilo  = "style='color: #111111;background: #EEEEEE;border: 1px solid black;font-size: 8pt;";
$encabezado_tabla="
	<table class=\"change_order_items\" style=\"padding-top:0; \">
		<thead>
			<tr>
				<th ${estilo}' >C&oacute;digo</th>
				<th ${estilo}' >Descripci&oacute;n</th>
				<th ${estilo}' >Cant.</th>";
if($mprec){
	$encabezado_tabla.="<th ${estilo}' >Precio U.</th>
				<th ${estilo}' >Monto</th>
				<th ${estilo}' >IVA%</th>";
}
$encabezado_tabla.="</tr>
		</thead>
		<tbody>
";
//Fin Encabezado Tabla

//************************
//     Pie Pagina
//************************
if($mprec){
	$col_span=6;
	$pie_final=<<<piefinal
			</tbody>
			<tfoot style='border:1px solid;background:#EEEEEE;'>
				<tr>
					<td  style="text-align: right;"></td>
					<td colspan="2" style="text-align: right;"><b>Sub-Total:</b></td>
					<td colspan="3" style="text-align: right;font-size:16px;font-weight:bold;" >${stotal}</td>
				</tr>
				<tr>
					<td style="text-align: right;"></td>
					<td colspan="2" style="text-align: right;"><b>Impuesto</b></td>
					<td colspan="3" style="text-align: right;font-size:16px;font-weight:bold;">${iva}</td>
				</tr>
				<tr style='border-top: 1px solid;background:#AAAAAA;'>
					<td style="text-align: right;"></td>
					<td colspan="2" style="text-align: right;"><b>MONTO TOTAL:</b></td>
					<td colspan="3" style="text-align: right;font-size:20px;font-weight:bold;">${gtotal}</td>
				</tr>
			</tfoot>

		</table>
piefinal;
}else{
		$col_span=3;
		$pie_final=<<<piefinal
			</tbody>
		</table>
piefinal;
}

$pie_continuo=<<<piecontinuo
		</tbody>
		<tfoot>
			<tr>
				<td colspan="${col_span}" style="text-align: right;">CONTINUA...</td>
			</tr>
		</tfoot>
	</table>
<div style="page-break-before: always;"></div>
piecontinuo;
//Fin Pie Pagina

$mod     = $clinea = false;
$npagina = true;
$i       = 0;

foreach ($detalle AS $items){ $i++;
	do {
		if($npagina){
			$this->incluir('X_CINTILLO');
			echo $encabezado;
			echo $encabezado_tabla;
			$npagina=false;
		}
?>
			<tr class="<?php if(!$mod) echo 'even_row'; else  echo 'odd_row'; ?>">
				<td style="text-align: center;"><?php echo trim($items->codigo); ?></td>
				<td>
					<?php
					if(!$clinea){
						$descrip = trim($items->desca);
						$descrip = str_replace("\r",'',$descrip);
						$descrip = str_replace(array("\t"),' ',$descrip);
						$descrip = wordwrap($descrip,40,"\n");
						$arr_des = explode("\n",$descrip);
					}

					while(count($arr_des)>0){
						$uline   = array_shift($arr_des);
						echo htmlspecialchars($uline).'<br />';
						$lineas++;
						if($lineas >= $maxlin){
							$lineas =0;
							$npagina=true;
							if(count($arr_des)>0){
								$clinea = true;
							}else{
								$clinea = false;
							}
							break;
						}
					}
					if(count($arr_des)==0 && $clinea) $clinea=false;
					?>
				</td>
				<td style="text-align: right;"><?php     echo ($clinea)? '': nformat($items->cana); ?></td>
				<?php if($mprec){ ?>
					<td style="text-align: right;"><?php     echo ($clinea)? '':nformat($items->preca); ?></td>
					<td class="change_order_total_col"><?php echo ($clinea)? '':nformat($items->preca*$items->cana); ?></td>
					<td style="text-align: right;" ><?php    echo ($clinea)? '': nformat($items->iva); ?></td>
				<?php }?>
			</tr>
<?php
		if($npagina){
			echo $pie_continuo;
		}else{
			$mod = ! $mod;
		}
	} while ($clinea);
}

for(1;$lineas<$maxlin;$lineas++){ ?>
			<tr class="<?php if(!$mod) echo 'even_row'; else  echo 'odd_row'; ?>">
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<?php if($mprec){ ?>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<?php }?>
			</tr>
<?php
	$mod = ! $mod;
}
echo $pie_final;
?>
<script type="text/php">
	if (isset($pdf)) {
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

		$texto[]="ELABORADO POR:";
		$texto[]="APROBADO:";
		$texto[]="RECIBIDO POR:";

		$cuadros = 0;   //Cantidad de cuadros (en caso de ser 0 calcula la cantidad)
		$margenh = 40;  //Distancia desde el borde derecho e izquierdo
		$margenv = 80;  //Distancia desde el borde inferior
		$alto    = 50;  //Altura de los cuadros
		$size    = 9;   //Tamanio del texto en los cuadros
		$color   = array(0,0,0); //Color del marco
		$lcolor  = array(0,0,0); //Color de la letra
		//**************************************************

		$cuadros = ($cuadros>0) ? $cuadros : count($texto);
		$cuadro  = $pdf->open_object();
		$margenl = $margenv-$alto+$text_height+5;    //Margen de la letra desde el borde inferior
		$ancho   = intval(($w-2*$margenh)/$cuadros); //Ancho de cada cuadro
		for($i=0;$i<$cuadros;$i++){
			$pdf->rectangle($margenh+$i*$ancho, $h-$margenv, $ancho, $alto,$color, 1);
			if(isset($texto[$i])){
				$width = Font_Metrics::get_text_width($texto[$i],$font,$size);
				$pdf->text($margenh+$i*$ancho+intval($ancho/2)-intval($width/2), $h-$margenl, $texto[$i], $font, $size, $lcolor);
			}
		}
		//***Fin del cuadro

		$pdf->close_object();
		$pdf->add_object($cuadro,'add');

		$text = "PP {PAGE_NUM} de {PAGE_COUNT}";

		// Center the text
		$width = Font_Metrics::get_text_width('PP 1 de 2', $font, $size);
		$pdf->page_text($w / 2 - $width / 2, $y, $text, $font, $size, $color);
	}
</script>
</body>
</html>
