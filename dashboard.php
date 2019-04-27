<?php 
    require_once('koneksi.php');
    
    $sql = "SELECT SUM(tntbjblok) AS NTBJBLOK, SUM(tntbjpanel) AS NTBJPANEL, SUM(tntbjmortar) AS NTBJMORTAR, SUM(tntbjwiremesh) AS NTBJWIREMESH,  
SUM(tsjblok) AS SJBLOK, SUM(tsjpanel) AS SJPANEL, SUM(tsjmortar) AS SJMORTAR, SUM(tsjwiremesh) AS SJWIREMESH FROM
(
SELECT IF(jenis='NTBJBLOK',tot,0) AS tntbjblok, IF(jenis='NTBJPANEL',tot,0) AS tntbjpanel, IF(jenis='NTBJMORTAR',tot,0) AS tntbjmortar, IF(jenis='NTBJWIREMESH',tot,0) AS tntbjwiremesh,
IF(jenis='SJBLOK',tot,0) AS tsjblok, IF(jenis='SJPANEL',tot,0) AS tsjpanel, IF(jenis='SJMORTAR',tot,0) AS tsjmortar, IF(jenis='SJWIREMESH',tot,0) AS tsjwiremesh
FROM
(
SELECT CONCAT(tipe,PRODUK) AS jenis, ROUND(SUM(KUBIK),0) AS tot FROM
(
SELECT TANGGAL, IF(PV_NAMA LIKE 'BLOK%','BLOK',IF(PV_NAMA LIKE 'PANEL%','PANEL',IF(PV_NAMA LIKE 'GE-%','MORTAR',IF(PV_NAMA LIKE 'WIRE%MESH%', 'WIREMESH', '')))) AS PRODUK, KUBIK, tipe FROM 
(
SELECT apm.NOBUKTI, apm.TANGGAL, det_apm.PV_ID, PV_NAMA, det_apm.KUBIK, 'NTBJ' AS tipe FROM apm, det_apm WHERE apm.NOBUKTI=det_apm.NOBUKTI AND (apm.NOBUKTI LIKE 'TB%' OR apm.NOBUKTI LIKE 'TM%' OR apm.NOBUKTI LIKE 'TW%') AND MONTH(apm.TANGGAL)=MONTH(NOW()) AND YEAR(apm.TANGGAL)=YEAR(NOW())
UNION
SELECT suratjalan.SJ_NO, suratjalan.TGL_SJ, det_suratjalan.PV_ID, PV_NAMA, det_suratjalan.KUBIK, 'SJ' AS tipe FROM suratjalan, det_suratjalan WHERE suratjalan.SJ_NO=det_suratjalan.SJ_NO AND MONTH(suratjalan.TGL_SJ)=MONTH(NOW()) AND YEAR(suratjalan.TGL_SJ)=YEAR(NOW())
)a
)b WHERE PRODUK<>'' GROUP BY CONCAT(tipe,PRODUK)
)c
)d;
";
    
    $r = mysqli_query($con,$sql);
    
    $result = array();
    
    while($row = mysqli_fetch_array($r)){
        
        array_push($result,array(
            "NTBJBLOK"=>$row['NTBJBLOK'],
            "NTBJPANEL"=>$row['NTBJPANEL'],
            "NTBJMORTAR"=>$row['NTBJMORTAR'],
            "NTBJWIREMESH"=>$row['NTBJWIREMESH'],
            
            "SJBLOK"=>$row['SJBLOK'],
            "SJPANEL"=>$row['SJPANEL'],
            "SJMORTAR"=>$row['SJMORTAR'],
            "SJWIREMESH"=>$row['SJWIREMESH']
        ));
    }
    
    echo json_encode(array('result'=>$result));
    
    mysqli_close($con);
?>
