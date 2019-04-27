<?php 
	require_once('koneksi.php');

		 $etanggal = "";
    if (isset($_POST['etanggal'])) $etanggal = $_POST['etanggal'];

    $filterbln = "";
    if ($etanggal=="")
    {
    	$filterbulan = "MONTH(NOW())";
		$filtertahun = "YEAR(NOW())";
    }
	else
    {
    	$filterbulan = "'".substr($etanggal,5,2)."'";
		$filtertahun = "'".substr($etanggal,0,4)."'";
    }
	
	$sql = "SELECT TANGGAL, PRODUK, SUM(kolcasting) as GETCASTING, SUM(kolntbj) as GETNTBJ, SUM(kolsj) as GETSJ, SUM(kolstok) as GETSTOK from
(
SELECT TANGGAL, PRODUK, IF(tipe='CASTING',PROGRESS,0) as kolcasting, IF(tipe='NTBJ',PROGRESS,0) as kolntbj, IF(tipe='SJ',PROGRESS,0) as kolsj, IF(tipe='STOK',PROGRESS,0) as kolstok from
(
SELECT TANGGAL, ROUND(SUM(KUBIK),2) AS PROGRESS, PRODUK, tipe FROM
(
SELECT TANGGAL, IF(PV_NAMA LIKE 'BLOK%','BLOK',IF(PV_NAMA LIKE 'PANEL%','PANEL',IF(PV_NAMA LIKE 'GE-%','MORTAR',IF(PV_NAMA like 'WIRE MESH%', 'WIREMESH', '')))) AS PRODUK, KUBIK, tipe FROM
(SELECT casting.NOBUKTI, casting.TANGGAL, det_casting.PV_ID, PV_NAMA, det_casting.KUBIK, 'CASTING' AS tipe FROM casting, det_casting WHERE casting.NOBUKTI=det_casting.NOBUKTI AND MONTH(casting.TANGGAL)=$filterbulan AND YEAR(casting.TANGGAL)=$filtertahun 
UNION
SELECT apm.NOBUKTI, apm.TANGGAL, det_apm.PV_ID, PV_NAMA, det_apm.KUBIK, 'NTBJ' AS tipe FROM apm, det_apm WHERE apm.NOBUKTI=det_apm.NOBUKTI AND (apm.NOBUKTI LIKE 'TB%' OR apm.NOBUKTI LIKE 'TM%' OR apm.NOBUKTI LIKE 'TW%') AND MONTH(apm.TANGGAL)=$filterbulan AND YEAR(apm.TANGGAL)=$filtertahun 
UNION
SELECT suratjalan.SJ_NO, suratjalan.TGL_SJ, det_suratjalan.PV_ID, PV_NAMA, det_suratjalan.KUBIK, 'SJ' AS tipe FROM suratjalan, det_suratjalan WHERE suratjalan.SJ_NO=det_suratjalan.SJ_NO AND MONTH(suratjalan.TGL_SJ)=$filterbulan AND YEAR(suratjalan.TGL_SJ)=$filtertahun
UNION



SELECT b.TANGGAL, b.TANGGAL, b.PV_ID, b.PV_NAMA AS PRODUK, ROUND((b.KUBIK+IFNULL(d.KUBIK,0)),2) AS KUBIK, 'STOK' AS TIPE FROM (SELECT TANGGAL, PV_ID, PV_NAMA, SUM(KUBIK) AS KUBIK FROM
(
SELECT TANGGAL, produk_opname_temp.PV_ID, PV_NAMA, STOK*PV_KUBIK AS KUBIK, 'STOK' FROM produk_opname_temp, produk WHERE STOK<>0 AND produk_opname_temp.PV_ID=produk.PV_ID AND MONTH(produk_opname_temp.TANGGAL)=$filterbulan AND YEAR(produk_opname_temp.TANGGAL)=$filtertahun
)a GROUP BY TANGGAL, PV_ID, PV_NAMA)b
LEFT JOIN
(SELECT TANGGAL, PV_ID, PV_NAMA, SUM(KUBIK) AS KUBIK FROM (
SELECT apm.NOBUKTI, apm.TANGGAL, PV_ID, PV_NAMA, det_apm.KUBIK, 'STOK' AS tipe FROM apm, det_apm WHERE apm.NOBUKTI=det_apm.NOBUKTI AND (apm.NOBUKTI LIKE 'TB%' OR apm.NOBUKTI LIKE 'TM%' or apm.NOBUKTI like 'TW%') AND MONTH(apm.TANGGAL)=$filterbulan AND YEAR(apm.TANGGAL)=$filtertahun
UNION
SELECT apk.NOBUKTI, apk.TANGGAL, PV_ID, PV_NAMA, det_apk.KUBIK*-1, 'STOK' AS tipe FROM apk, det_apk WHERE apk.NOBUKTI=det_apk.NOBUKTI AND MONTH(apk.TANGGAL)=$filterbulan AND YEAR(apk.TANGGAL)=$filtertahun
UNION       
SELECT suratjalan.SJ_NO, suratjalan.TGL_SJ AS TANGGAL, PV_ID, PV_NAMA, det_suratjalan.KUBIK*-1, 'STOK' AS tipe FROM suratjalan, det_suratjalan WHERE suratjalan.SJ_NO=det_suratjalan.SJ_NO AND MONTH(suratjalan.TGL_SJ)=$filterbulan AND YEAR(suratjalan.TGL_SJ)=$filtertahun
)c GROUP BY TANGGAL, PV_ID, PV_NAMA)d
ON b.PV_ID=d.PV_ID AND b.TANGGAL=d.TANGGAL 


)b
)c GROUP BY TANGGAL, PRODUK, TIPE
)d
)e WHERE PRODUK='MORTAR' GROUP BY TANGGAL, PRODUK;";
	
	$r = mysqli_query($con,$sql);
	
	$result = array();
	
	while($row = mysqli_fetch_array($r)){
		
array_push($result,array(
			"TANGGAL"=>$row['TANGGAL'],
			"PRODUK"=>$row['PRODUK'],
			"CASTING"=>"Casting: ".$row['GETCASTING']."   ",
			"NTBJ"=>"NTBJ: ".$row['GETNTBJ']."   ",
			"SJ"=>"SJ: ".$row['GETSJ']."   ",
			"STOK"=>$row['GETSTOK']
		));
	}
	
	echo json_encode(array('result'=>$result));
	
	mysqli_close($con);
?>
