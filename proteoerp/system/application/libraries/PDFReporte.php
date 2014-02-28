<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
require_once(RAPYD_PATH."helpers/datehelper.php");
require_once('Fpdf.php');

class PDFReporte extends Fpdf {
	var $bits=0;
	var $ppnum=true;  //Activa o desactiva el numero de las paginas
	var $ProcessingTable=false;
	var $TableWidth;
	var $aCols=array();
	var $fCols=array();
	var $fcount=0;
	var $TableX;
	var $HeaderColor;
	var $RowColors;
	var $ColorIndex;
	var $Titulo;
	var $Acumulador=array();
	var $SobreTabla;
	var $SubTitulo;
	var $Logo;
	var $rows=array();
	var $grupo='';
	var $grupoHead;
	var $grupoField;
	var $grupoLabel;
	var $cgrupo=False;
	var $DBquery;
	var $DBfieldsName;
	var $DBfieldsType;
	var $DBfieldsMax_lengt;
	var $DBieldsNum;
	var $totalizar=array();
	var $ctotalizar=false;
	var $propiedades=array("HeaderColor"=>array(174,174,174),
			     "color1"=>array(255,255,255),
			     "color2"=>array(239,239,239),
			     "padding"=>2);
	var $Columna=array("color1"=>array(255,255,255),"color2"=>array(239,239,239),"padding"=>2);
	var $tituHeader='';
	var $tituSubHeader='';
	var $sistema='Sistema Proteoerp';
	var $view=array('TituSize'     =>14,
									'TituFont'     =>'Arial',
									'TituType'     =>'U',
									'SubTituSize'  =>10,
									'SubTituFont'  =>'Arial',
									'SubTituType'  =>'',
									'TableTituSize'=>8,
									'TableTituFont'=>'Arial',
									'TableRowSize' =>4,
									'TableRowFont' =>'Arial',
									'HeadSize'     =>10,
									'HeadFont'     =>'Arial',
									'SubHeadSize'  =>4,
									'SubHeadFont'  =>'Arial',
									'GroupHeadSize'=>8,
									'GroupHeadFont'=>'Arial',
									'GroupHeadType'=>'',
									'StablaTituFont'=>'Arial',
									'StablaTituSize'=>8
									);

/*##########################################################################################################
# orientation: Orientaci?n de p?gina por defecto. Los posibles valores son (case insensitive)              #
#         * P o Portrait (DEFECTO)                                                                         #
#         * L o Landscape (apaisado)                                                                       #
# format" El formato usado por las p?ginas. Es puede ser uno de los siguientes valores (case insensitive)  #
#         A3,A4,A5,Letter,Legal                                                                            #
# unit: pt: punto ; mm: milimetro ; cm: centimetro ; in: pulgada                                           #
##########################################################################################################*/


	function PDFReporte($mSQL='',$orientation='P',$format='Letter',$unit='mm'){
		if(!empty($mSQL)){
			$CI = & get_instance();
			$this->DBquery  = $CI->db->query($mSQL);
			$data=$this->DBquery->field_data();
			foreach ($data as $field){
				$this->DBfieldsName[]=$field->name;
				$this->DBfieldsType[$field->name]     =$field->type;
				$this->DBfieldsMax_lengt[$field->name]=$field->max_length;
			}
			$this->DBieldsNum=count($this->DBfieldsName);
			$this->Fpdf($orientation,$unit,$format);
			$this->Open();
			$this->AliasNbPages();
			$this->sistema = $CI->datasis->traevalor('SISTEMA');
		}
	}

	function setType($campo,$tipo){
		$this->DBfieldsType[$campo]=$tipo;
	}

	function setTitulo($tit='Listado',$size='',$font=''){
		if(!empty($size)) $this->view['TituSize'] =$size;
		if(!empty($font)) $this->view['TituFont'] =$font;
		//$this->Titulo =utf8_decode($tit);
		$this->Titulo =$tit;
	}

	function setSubTitulo($tit='',$size='',$font=''){
		if(!empty($size)) $this->view['SubTituSize'] =$size;
		if(!empty($font)) $this->view['SubTituFont'] =$font;
		if(!empty($tit) ) $this->SubTitulo =$tit;
		//if(!empty($tit) ) $this->SubTitulo =utf8_decode($tit);
	}

	function setTableTitu($size='',$font=''){
		if(!empty($size)) $this->view['TableTituSize'] =$size;
		if(!empty($font)) $this->view['TableTituFont'] =$font;
	}

	function setRow($size='',$font=''){
		if(!empty($size)) $this->view['TableRowSize'] =$size;
		if(!empty($font)) $this->view['TableRowFont'] =$font;
	}

	function setHead($tituHeader='',$size='',$font=''){
		if(!empty($tituHeader)) $this->tituHeader=$tituHeader;
		if(!empty($size)) $this->view['HeadSize'] =$size;
		if(!empty($font)) $this->view['HeadFont'] =$font;
	}

	function setSubHead($tituSubHeader='',$size='',$font=''){
		if(!empty($tituSubHeader)) $this->tituSubHeader[]=$tituSubHeader;
		if(!empty($size)) $this->view['SubHeadSize'] =$size;
		if(!empty($font)) $this->view['SubHeadFont'] =$font;
	}

	function setHeadValores($param){
		$CI =& get_instance();
		$data= func_get_args();
		foreach($data as $sale)
			$this->tituHeader[]=$CI->datasis->traevalor($sale);
	}

	function setSubHeadValores($param){
		$CI =& get_instance();
		$data= func_get_args();
		foreach($data as $sale)
			$this->tituSubHeader[]=$CI->datasis->traevalor($sale);
	}

	function setTotalizar($param){
		$data= func_get_args();
		foreach($data as $sale){
			if (in_array($sale, $this->DBfieldsName) OR array_key_exists($sale,$this->fCols)){
				$this->totalizar[]=$sale;
				$this->ctotalizar=true;
			}
		}
	}

	function setAcumulador($param){
		$data= func_get_args();
		foreach($data as $sale){
			if (in_array($sale, $this->DBfieldsName) OR array_key_exists($sale,$this->fCols)){
				$this->Acumulador[]=$sale;
			}
			if (!in_array($sale, $this->totalizar)){
				$this->totalizar[]=$sale;
				$this->ctotalizar=true;
			}
		}
	}

	function setGrupo($param){
		if(is_array($param))
			$data=$param;
		else
			$data= func_get_args();
		foreach($data as $sale){
			if (in_array($sale, $this->DBfieldsName)){
				$this->grupo[]=$sale;
				$this->cgrupo=True;
			}
		}
	}

	function setSobreTabla($param,$size=8,$font='Arial'){
		$this->view['StablaTituFont']=$font;
		$this->view['StablaTituSize']=$size;
		$this->SobreTabla=$param;
	}

	function setHeadGrupo($label='',$campo='',$font='',$size='',$type=''){
		//$salecoment= substr($template,strpos($template,"<#")+2,strpos($template,"#>")-strpos($template,"<#")-2);
		$this->grupoHead=$label;
		if (in_array($campo, $this->DBfieldsName)){
			$this->grupoField=$campo;
		}
		if(!empty($size)) $this->GroupHeadSize=$size;
		if(!empty($font)) $this->GroupHeadFont=$font;
		if(!empty($type)) $this->GroupHeadType=$type;
	}

	function setGrupoLabel($label){
		if(is_array($label))
			$data=$label;
		else
			$data= func_get_args();

		foreach($data as $sale){
			$correcto=true;
			$sal=$this->_parsePattern($sale);
			//print_r($sal);
			if (count($sal)>0){
				foreach($sal as $pasa){
					if (!in_array($pasa, $this->DBfieldsName)){
						$correcto=false;
					}
				}
			}else{
				if (!in_array($sale, $this->DBfieldsName)) $correcto=false;
			}
			if($correcto)
				$this->grupoLabel[]=$sale;
			else
				$this->grupoLabel[]=NULL;
		}
	}

	function Header(){
		if (!empty($this->Logo))
			$this->image($this->Logo,12,7,30);

		$this->SetFont('Times','I',6);
		//$this->Cell(0,0,'Página '.$this->PageNo().'/{nb}',0,1,'R');
		if($this->ppnum) $this->Cell(0,0,utf8_decode('Página ').$this->PageNo().'/{nb}',0,1,'R');

		//Head
		$this->SetFont($this->view['HeadFont'],'',$this->view['HeadSize']);
		if( is_array($this->tituHeader) ){
			foreach($this->tituHeader as $headtitu)
				$this->Cell(0,$this->view['HeadSize']/2,$headtitu,0,1,'L');
		}elseif (!empty($this->tituHeader))
			$this->Cell(0,$this->view['HeadSize']/2,$this->tituHeader,0,1,'L');

		//SubHead
		$this->SetFont($this->view['SubHeadFont'],'',$this->view['SubHeadSize']);
		if( is_array($this->tituSubHeader) ){
			foreach($this->tituSubHeader as $headtitu)
				$this->Cell(0,$this->view['SubHeadSize']/2,$headtitu,0,1,'L');
		}elseif (!empty($this->tituSubHeader))
			$this->Cell(0,$this->view['SubHeadSize']/2,$this->tituSubHeader,0,1,'L');

		//Titulo
		$this->SetFont($this->view['TituFont'],'',$this->view['TituSize']);
		$this->Cell(0,$this->view['TituSize']/2,$this->Titulo ,0,1,'C');

		//SubTitulo
		if(!empty($this->SubTitulo)){
			$this->SetFont($this->view['SubTituFont'],'',$this->view['SubTituSize']);
			$this->Cell(0,$this->view['SubTituSize']/2,$this->SubTitulo ,0,1,'C');
		}
		$this->Ln(5);

		//Sobre Tabla
		if (!empty($this->SobreTabla)){
			$this->SetFont($this->view['StablaTituFont'],'',$this->view['StablaTituSize']);
			$this->Cell(0,$this->view['TableTituSize']/2,$this->SobreTabla,0,1,'L');
		}
		//Ensure table header is output
		if($this->ProcessingTable)
			$this->TableHeader();
	}

	function Footer(){
		$this->Ln();
		$this->SetFont('Arial','B',6);
		$this->Cell(0,6,$this->Titulo.'::'.$this->sistema,'T',1,'C');
		$this->Ln();
		//Ensure table header is output
		//parent::Footer();
	}

	function TableHeader() {
		$this->SetFont($this->view['TableTituFont'],'B',$this->view['TableTituSize']);
		$this->SetX($this->TableX);
		$fill=!empty($this->HeaderColor);
		$mAncho = $this->view['TableTituSize']/2;
		if($fill)
			$this->SetFillColor($this->HeaderColor[0],$this->HeaderColor[1],$this->HeaderColor[2]);
		foreach($this->aCols as $col)
			if (is_array($col['c'])) {
				$m = 1;
				$mX = $this->GetX();
				$mY = $this->GetY();
				foreach ( $col['c'] as $mPar )
				{
					if ( $m > 1 ){
						$this->SetXY( $mX, $mY+$mAncho*($m-1) );
					}
					//$this->Cell($col['w'],$mAncho, utf8_decode($mPar),'',0,'C',$fill);
					$this->Cell($col['w'],$mAncho, $mPar,'',0,'C',$fill);
					if ( $m == 1 ){
						$mX1 = $this->GetX();
						$mY1 = $this->GetY();
					}
					$m++;
				}
				$this->SetXY($mX1, $mY1);
			} else {
				$this->Cell($col['w'], $mAncho, $col['c'],'TB',0,'C',$fill);
				//$this->Cell($col['w'], $mAncho, utf8_decode($col['c']),'TB',0,'C',$fill);
				$m = 2;
			}
		for ( $i=1; $i<$m; $i++) $this->Ln();
	}

	function TableWidth(){
		$TableWidth=0;
		foreach($this->aCols as $i=>$col){
			$w=$col['w'];
			if($w==-1)
				$w=$width/count($this->aCols);
			elseif(substr($w,-1)=='%')
				$w=$w/100*$width;
			$this->aCols[$i]['w']=$w;
			$TableWidth+=$w;
		}
		$this->TableWidth=$TableWidth;
		return $TableWidth;
	}

	function GroupTableHeader($row,$n=0){
		$this->SetFont($this->view['GroupHeadFont'],'',$this->view['GroupHeadSize']);
		if (empty($this->TableWidth)) $TableWidth = $this->TableWidth(); else $TableWidth = $this->TableWidth;
		for($i=$n-1;$i<count($this->grupo);$i++){
			if (!empty($this->grupoLabel[$i])){
				$sal=$this->_parsePattern($this->grupoLabel[$i]);
				if(count($sal)>0){
					$label=$this->grupoLabel[$i];
					foreach($sal as $pasa){
						if($this->DBfieldsType[$pasa]=='date') $row[$pasa]=dbdate_to_human($row[$pasa]);
						$label=str_replace('<#'.$pasa.'#>',$row[$pasa],$label);
					}
				}else
					$label=$this->grupoLabel[$i];
			}else{
				$label=$this->grupo[$i].' '.$row[$this->grupo[$i]];
			}

			$this->SetX($this->TableX);
			$this->SetFont($this->view['GroupHeadFont'],'',$this->view['GroupHeadSize']-$i);
			$this->Cell($TableWidth,$this->view['GroupHeadSize']/2,$label,'B',1,'',0);
			$this->line();
		}
	}

	function Row($data,$linea=0,$pinta=1) {
		$this->SetX($this->TableX);
		$ci=$this->ColorIndex;
		$fill=!empty($this->RowColors[$ci]);
		if($fill)
			$this->SetFillColor($this->RowColors[$ci][0],$this->RowColors[$ci][1],$this->RowColors[$ci][2]);
		if($pinta==0) $fill=0;
		$ancho = 1;

		foreach($this->aCols as $col) {
			if ( $col['s'] > $ancho ) $ancho=$col['s'];
			$this->SetFont("Arial",'',$col['s']);

			$this->Cell($col['w'],$ancho/2,$data[$col['f']],$linea,0,$col['a'],$fill);

		}
		$this->Ln();
		$this->ColorIndex=1-$ci;
	}

	function line(){
		$this->SetX($this->TableX);
		if (empty($this->TableWidth)) $TableWidth = $this->TableWidth(); else $TableWidth = $this->TableWidth;
		$this->Cell($TableWidth,0.35,'','T',1);
	}

	function CalcWidths($width,$align){
		//Compute the widths of the columns
		if (empty($this->TableWidth)) $TableWidth = $this->TableWidth(); else $TableWidth = $this->TableWidth;
		//Compute the abscissa of the table
		if($align=='C')
			$this->TableX=max(($this->w-$TableWidth)/2,0);
		elseif($align=='R')
			$this->TableX=max($this->w-$this->rMargin-$TableWidth,0);
		else
			$this->TableX=$this->lMargin;
	}

	function AddCol($field=-1,$width=-1,$caption='',$align='L', $fontsize=11){
		//Add a column to the table
		if($field!=-1){
			if (in_array($field, $this->DBfieldsName)){
				$this->aCols[]=array( 'f'=>$field, 'c'=>$caption,'w'=>$width, 'a'=>$align,'s'=>$fontsize );
				$this->rows[]=$field;
			}
		}
	}

	function AddCof($field=-1,$width=-1,$caption='',$align='L', $fontsize=11){
		//Add a column to the table
		if($field!=-1){
			$correcto=false;
			$sal=$this->_parsePattern($field);

			if (count($sal)>0){
				$correcto=true;
				foreach($sal as $pasa){
					if (!in_array($pasa, $this->DBfieldsName)){
						$correcto=false;
					}
				}
			}
			if ($correcto){
				$nname='__cC'.$this->fcount;
				$this->aCols[]=array( 'f'=>$nname, 'c'=>$caption,'w'=>$width, 'a'=>$align,'s'=>$fontsize );
				$this->rows[]=$nname;
				$this->fCols[$nname]=$field;
				$this->fcount++;
				$this->setType($nname,'real');
			}
		}
	}

	function grupoCambio($bache,$row){
		$i=0;
		foreach($this->grupo as $fila) {
			$i++;
			if ($bache[$fila]!=$row[$fila]) return $i;
		}
		return false;
	}

	function Table(){
		$prop = $this->propiedades;
		$res  = $this->DBquery;

		//Add all columns if none was specified
		if(count($this->aCols)==0){
			for( $i=0; $i<$this->DBieldsNum; $i++ )
				$this->AddCol();
		}
		//Retrieve column names when not specified
		foreach($this->aCols as $i=>$col){
			if($col['c']==''){
				if(is_string($col['f']))
					$this->aCols[$i]['c']=ucfirst($col['f']);
				else {
					$nombre = $this->DBfieldsName[$i];
					$this->aCols[$i]['c'] = ucfirst($nombre);
				}
			}
		}
		//Handle properties
		if(!isset($prop['width']))  $prop['width']=0;
		if($prop['width']==0)       $prop['width']=$this->w-$this->lMargin-$this->rMargin;
		if(!isset($prop['align']))  $prop['align']='C';
		if(!isset($prop['padding']))$prop['padding']=$this->cMargin;

		$cMargin=$this->cMargin;
		$this->cMargin=$prop['padding'];

		if(!isset($prop['HeaderColor'])) $prop['HeaderColor']=array();
		$this->HeaderColor=$prop['HeaderColor'];
		if(!isset($prop['color1'])) $prop['color1']=array();
		if(!isset($prop['color2'])) $prop['color2']=array();

		if(!isset($prop['logo'])) $this->Logo =""; else $this->Logo = $prop['logo'];
		$this->RowColors=array($prop['color1'],$prop['color2']);

		//Compute column widths
		$this->CalcWidths($prop['width'],$prop['align']);

		//Print header
		$this->TableHeader();
		//Print rows
		$this->SetFont('Arial','',11);
		$this->ColorIndex=0;
		$this->ProcessingTable=true;

		if($this->ctotalizar){
			foreach( $this->aCols  as $i=>$fila ){
				$gtotal[$fila['f']]= 0 ;
			}
			$rgtotal=$gtotal;
		}

		$cambio=false;
		if($this->cgrupo){
			foreach($this->grupo as $fila){
				if($this->ctotalizar) $stotal[]=$rstotal[]=$gtotal;
				$bache[$fila] =NULL;
			}
		}
		$one=$this->cgrupo;
		foreach( $res->result_array() as $row ){

			if($one){ $one=false;
				foreach($this->grupo as $fila) $bache[$fila]=$row[$fila];
				$this->GroupTableHeader($row,1);
			}
			if($this->cgrupo) $cambio=$this->grupoCambio($bache,$row);

			if($cambio){
				foreach($this->grupo as $fila) $bache[$fila]=$row[$fila];
				if ($this->ctotalizar){
					for($u=0;$u<count($this->grupo)-($cambio-1);$u++){
						$this->Row($rstotal[$u],'T',0);
						foreach( $this->aCols  as $i=>$fila ) $stotal[$u][$fila['f']] = 0;
					}
				}else{
					$this->line();
				}
				$this->ln(3);
				$this->GroupTableHeader($row,$cambio);
				$cambio=false;
			}
			$nf=$row;
			foreach( $this->aCols  as $i=>$fila ){
				$key=$fila['f'];
				if (preg_match("/^__cC[0-9]+$/", $key)>0){
					$sal=$this->_parsePattern($this->fCols[$key]);
					$val=$this->fCols[$key];
					if (count($sal)>0){

						foreach($sal as $pasa){
							//if(!is_numeric($nf[$pasa])) $nf[$pasa]=0;
							$val=str_replace('<#'.$pasa.'#>',$nf[$pasa],$val);
						}

						$col='$val='.$val.';';
						eval($col);
						$row[$key]=$val;

					}
				}

				if ($this->ctotalizar){
					if (in_array($key, $this->totalizar)){
						$gtotal[$key] +=$row[$key];
						if($this->cgrupo){
							$cangrup=count($this->grupo);
							for($u=0;$u<$cangrup;$u++){
								$stotal[$u][$key]+=$row[$key];
								$rstotal[$u][$key] =nformat($stotal[$u][$key]);
							}
						}
						$rgtotal[$key] =nformat($gtotal[$key]);
						if (in_array($key, $this->Acumulador)){
							if($this->cgrupo){
								$row[$key]=$stotal[0][$key];
							}else{
								$row[$key]=$gtotal[$key];
							}
						}
					}else{
						$total[$key]=$gtotal[$key]=$rtotal[$key]=$rgtotal[$key]=' ';
						$cangrup=count($this->grupo);
						for($u=0;$u<$cangrup;$u++){
							$stotal[$u][$key]=$rstotal[$u][$key]=' ';
						}
					}
				}

				if    (in_array($this->DBfieldsType[$key], array('real',4,5,246))  ) $row[$key]=nformat($row[$key]);
				elseif(in_array($this->DBfieldsType[$key], array('date','datetime',10,12,7))  ) $row[$key]=dbdate_to_human($row[$key]);
				elseif(in_array($this->DBfieldsType[$key], array('int' ,1,2,9,3,8))) $row[$key]=intval($row[$key]);
			}
			$this->Row($row);
		}

		if ($this->ctotalizar){
				if ($this->cgrupo){
					for($u=0;$u<count($this->grupo);$u++){
						$this->Row($rstotal[$u],'T',0);
						foreach( $this->aCols  as $i=>$fila ) $stotal[$u][$fila['f']] = 0;
					}
				}
			$this->ln();
			$this->Row($rgtotal,'T',0);
		}else
			$this->ln(5);

		$this->ProcessingTable=false;
		$this->cMargin=$cMargin;
		//$this->aCols=array();
	}

	function _parsePattern($pattern){
		$template = $pattern;
		$parsedcount = 0;
		$salida=array();
		while (strpos($template,"#>")>0) {
			$parsedcount++;
			$parsedfield = substr($template,strpos($template,"<#")+2,strpos($template,"#>")-strpos($template,"<#")-2);
			$salida[]=$parsedfield;
			$template = str_replace("<#".$parsedfield ."#>","",$template);
		}
		return $salida;
	}

	function add_fila($param){
		$data= func_get_args();
		$fila= array();
		foreach( $this->rows  as $i=>$key ){
			if(array_key_exists($i, $data  ))
				$fila[$key]=$data[$i];
			else
				$fila[$key]=' ';
		}
		$this->Row($fila,'T',0);
	}
}
