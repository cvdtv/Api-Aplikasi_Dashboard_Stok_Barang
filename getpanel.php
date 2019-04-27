 <?php
    require_once('koneksi.php');
   
    $TANGGAL = $_GET['TANGGAL'];

    $sql = "SELECT b.TANGGAL, b.PV_ID, b.PV_NAMA AS PRODUK, ROUND((b.KUBIK+IFNULL(d.KUBIK,0)),2) AS KUBIK, 'STOK' AS TIPE FROM (SELECT TANGGAL, PV_ID, PV_NAMA, SUM(KUBIK) AS KUBIK FROM
    (
    SELECT TANGGAL, produk_opname_temp.PV_ID, PV_NAMA, STOK*PV_KUBIK AS KUBIK, 'STOK' FROM produk_opname_temp, produk WHERE STOK<>0 AND produk_opname_temp.PV_ID=produk.PV_ID
    )a GROUP BY TANGGAL, PV_ID, PV_NAMA)b
    LEFT JOIN
    (SELECT '$TANGGAL' as TANGGAL, PV_ID, PV_NAMA, SUM(KUBIK) AS KUBIK FROM (
    SELECT apm.NOBUKTI, apm.TANGGAL, PV_ID, PV_NAMA, det_apm.KUBIK, 'STOK' AS tipe FROM apm, det_apm WHERE apm.NOBUKTI=det_apm.NOBUKTI AND MONTH(apm.TANGGAL)=MONTH(NOW()) AND YEAR(apm.TANGGAL)=YEAR(NOW()) and TANGGAL<='$TANGGAL'
    UNION
    SELECT apk.NOBUKTI, apk.TANGGAL, PV_ID, PV_NAMA, det_apk.KUBIK*-1, 'STOK' AS tipe FROM apk, det_apk WHERE apk.NOBUKTI=det_apk.NOBUKTI AND MONTH(apk.TANGGAL)=MONTH(NOW()) AND YEAR(apk.TANGGAL)=YEAR(NOW()) and TANGGAL<='$TANGGAL'
    UNION       
    SELECT suratjalan.SJ_NO, suratjalan.TGL_SJ AS TANGGAL, PV_ID, PV_NAMA, det_suratjalan.KUBIK*-1, 'STOK' AS tipe FROM suratjalan, det_suratjalan WHERE suratjalan.SJ_NO=det_suratjalan.SJ_NO AND MONTH(suratjalan.TGL_SJ)=MONTH(NOW()) AND YEAR(suratjalan.TGL_SJ)=YEAR(NOW()) and TGL_SJ<='$TANGGAL'
    )c GROUP BY PV_ID, PV_NAMA)d
    ON b.PV_ID=d.PV_ID AND b.TANGGAL=d.TANGGAL where b.PV_NAMA like 'PANEL%' AND b.TANGGAL='$TANGGAL' ORDER BY KUBIK desc;";  

    //Mendapatkan Hasil 
    $r = mysqli_query($con,$sql);
    
    //Memasukkan Hasil Kedalam Array
    $result = array();
    while($row = mysqli_fetch_array($r))
{
    array_push($result,array(
            "TANGGAL"=>$row['TANGGAL'],
            "PRODUK"=>substr($row['PRODUK'],8),
            "PROGRESS"=>$row['KUBIK']
        ));
} 
    //Menampilkan dalam format JSON
    echo json_encode(array('result'=>$result));
    
    mysqli_close($con);
?>
