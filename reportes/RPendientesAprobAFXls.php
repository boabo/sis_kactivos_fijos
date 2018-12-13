<?php
class RPendientesAprobAFXls
{
    private $docexcel;
    private $objWriter;
    private $nombre_archivo;
    private $hoja;
    private $columnas=array();
    private $fila;
    private $equivalencias=array();

    private $indice, $m_fila, $titulo;
    private $swEncabezado=0; //variable que define si ya se imprimi� el encabezado
    private $objParam;
    public  $url_archivo;

    var $datos_titulo;
    var $datos_detalle;
    var $ancho_hoja;
    var $gerencia;
    var $numeracion;
    var $ancho_sin_totales;
    var $cantidad_columnas_estaticas;
    var $s1;
    var $t1;
    var $tg1;
    var $total;
    var $datos_entidad;
    var $datos_periodo;
    var $ult_codigo_partida;
    var $ult_concepto;



    function __construct(CTParametro $objParam){
        $this->objParam = $objParam;
        $this->url_archivo = "../../../reportes_generados/".$this->objParam->getParametro('nombre_archivo');
        //ini_set('memory_limit','512M');
        set_time_limit(400);
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array('memoryCacheSize'  => '10MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        $this->docexcel = new PHPExcel();
        $this->docexcel->getProperties()->setCreator("PXP")
            ->setLastModifiedBy("PXP")
            ->setTitle($this->objParam->getParametro('titulo_archivo'))
            ->setSubject($this->objParam->getParametro('titulo_archivo'))
            ->setDescription('Reporte "'.$this->objParam->getParametro('titulo_archivo').'", generado por el framework PXP')
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Report File");

        $this->docexcel->setActiveSheetIndex(0);

        $this->docexcel->getActiveSheet()->setTitle($this->objParam->getParametro('titulo_archivo'));


        $this->equivalencias=array(0=>'A',1=>'B',2=>'C',3=>'D',4=>'E',5=>'F',6=>'G',7=>'H',8=>'I',
            9=>'J',10=>'K',11=>'L',12=>'M',13=>'N',14=>'O',15=>'P',16=>'Q',17=>'R',
            18=>'S',19=>'T',20=>'U',21=>'V',22=>'W',23=>'X',24=>'Y',25=>'Z',
            26=>'AA',27=>'AB',28=>'AC',29=>'AD',30=>'AE',31=>'AF',32=>'AG',33=>'AH',
            34=>'AI',35=>'AJ',36=>'AK',37=>'AL',38=>'AM',39=>'AN',40=>'AO',41=>'AP',
            42=>'AQ',43=>'AR',44=>'AS',45=>'AT',46=>'AU',47=>'AV',48=>'AW',49=>'AX',
            50=>'AY',51=>'AZ',
            52=>'BA',53=>'BB',54=>'BC',55=>'BD',56=>'BE',57=>'BF',58=>'BG',59=>'BH',
            60=>'BI',61=>'BJ',62=>'BK',63=>'BL',64=>'BM',65=>'BN',66=>'BO',67=>'BP',
            68=>'BQ',69=>'BR',70=>'BS',71=>'BT',72=>'BU',73=>'BV',74=>'BW',75=>'BX',
            76=>'BY',77=>'BZ');

    }

    function setDatos ($param) {
        $this->datos = $param;
//        $this->datos2 = $param2;
//        $this->datos3 = $param3;
    }

    function generarReporte(){

        $this->imprimeDatos();

        $this->docexcel->setActiveSheetIndex(0);
        $this->objWriter = PHPExcel_IOFactory::createWriter($this->docexcel, 'Excel5');
        $this->objWriter->save($this->url_archivo);


    }

    function imprimeDatos(){

        $datos = $this->datos;
        $columnas = 0;


        $numberFormat = '#,#0.##;[Red]-#,#0.##';

        $this->docexcel->setActiveSheetIndex(0);
        $sheet0 = $this->docexcel->getActiveSheet();

        $sheet0->setTitle('Pendientes de Aprobación');

        //$datos = $this->objParam->getParametro('datos');
        //capture datas of the view BVP
        $selected = $this->objParam->getParametro('rep_pendiente_aprobacion');
        $hiddes = explode(',', $selected);
        $paprc = '';
        $pafpr = '';
        $paglo = '';
        $panom = '';
        $padep = '';

        for ($i=0; $i <count($hiddes) ; $i++) {
            switch ($hiddes[$i]) {
                case 'pprc': $paprc = 'prc'; break;
                case 'pfpr': $pafpr = 'fpr'; break;
                case 'pglo': $paglo = 'glo'; break;
                case 'pnom': $panom = 'nom'; break;
                case 'pdep': $padep = 'dep'; break;
            }
        }
        /////BVP
        $sheet0->getColumnDimension('B')->setWidth(10);
        $sheet0->getColumnDimension('C')->setWidth(20);
        $sheet0->getColumnDimension('D')->setWidth(20);
        $sheet0->getColumnDimension('E')->setWidth(60);
        $sheet0->getColumnDimension('F')->setWidth(40);
        $sheet0->getColumnDimension('G')->setWidth(40);




        //$this->docexcel->getActiveSheet()->mergeCells('A1:A3');
        $sheet0->mergeCells('B1:G1');
        $sheet0->setCellValue('B1', 'DEPARTAMENTO ACTIVOS FIJOS');
        $sheet0->mergeCells('B2:G2');
        $sheet0->setCellValue('B2', 'ACTIVOS FIJOS PENDIENTES DE APROBACIÓN');
        $sheet0->mergeCells('B3:G3');
        $sheet0->setCellValue('B3', 'Del: '.$this->objParam->getParametro('fecha_ini').' Al '.$this->objParam->getParametro('fecha_fin').' Estado: PENDIENTE');

        $styleExtras=array(
            'font' => array(
                'bold' => true,
                'size' => 8,
                'name' => 'Arial'
            )
        );


        $styleTitulos = array(
            'font' => array(
                'bold' => true,
                'size' => 8,
                'name' => 'Arial'
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => '768290'
                )
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ));

        $styleActivos = array(
            'font' => array(
                'bold' => false,
                'size' => 8,
                'name' => 'Arial'
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );


        $styleCabeza = array(
            'font' => array(
                'bold' => true,
                'size' => 10,
                'name' => 'Arial'
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE
                )
            )
        );


        $sheet0->getStyle('B1:G3')->applyFromArray($styleCabeza);

        $styleTitulos['fill']['color']['rgb'] = '808080';
        $styleTitulos['fill']['color']['rgb'] = 'd4d4d4';

        $sheet0->getRowDimension('4')->setRowHeight(35);
        $sheet0->getStyle('B5:G5')->applyFromArray($styleTitulos);
        $sheet0->getStyle('C5:G5')->getAlignment()->setWrapText(true);


        //*************************************Cabecera*****************************************

        $sheet0->setCellValue('B5', 'Nº');

        $sheet0->setCellValue('C5', 'Nº PROCESO COMPRA');

        $sheet0->setCellValue('D5', 'FECHA DE PROCESO');

        $sheet0->setCellValue('E5', 'GLOSA');

        $sheet0->setCellValue('F5', 'NOMBRE USUARIO');

        $sheet0->setCellValue('G5', 'DEPARTAMENTO');




        //*************************************Fin Cabecera*****************************************

        $fila = 6;

        $contador = 1;

        //************************************************Detalle***********************************************
        //delete columns selected BVP
        ($paprc=='prc')?'':$this->docexcel->getActiveSheet()->getColumnDimension('C')->setVisible(0);
        ($pafpr=='fpr')?'':$this->docexcel->getActiveSheet()->getColumnDimension('D')->setVisible(0);
        ($paglo=='glo')?'':$this->docexcel->getActiveSheet()->getColumnDimension('E')->setVisible(0);
        ($panom=='nom')?'':$this->docexcel->getActiveSheet()->getColumnDimension('F')->setVisible(0);
        ($padep=='dep')?'':$this->docexcel->getActiveSheet()->getColumnDimension('G')->setVisible(0);
        ///

        $tipo = $this->objParam->getParametro('tipo_reporte');
        $sheet0->getRowDimension('5')->setRowHeight(35);

        foreach($datos as $value) {

//                    $styleTitulos['fill']['color']['rgb'] = 'e6e8f4';
                    $sheet0->getStyle('B' . $fila . ':G' . $fila)->applyFromArray($styleActivos);
                    $sheet0->getStyle('B' . $fila . ':G' . $fila)->getAlignment()->setWrapText(true);



                    $this->docexcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, $fila, $contador);
//                    $sheet0->getStyle('C'.$fila)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $this->docexcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2, $fila, $value['nro_tramite']);
                    $this->docexcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, $fila, date("d/m/Y", strtotime($value['fecha_ini'])));
                    $this->docexcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, $fila, $value['glosa']);
                    $this->docexcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, $fila, $value['funcionario']);
                    $this->docexcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, $fila, $value['depto']);

                    $contador++;
                    $fila++;

        }

    }
}

?>