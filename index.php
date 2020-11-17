<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>YOMART DCG</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=100">
  <meta http-equiv="refresh" content="40; url=" <?php echo $_SERVER['PHP_SELF']; ?>">
  <!-- Font Awesome -->
  <link rel="stylesheet" type="text/css" href="vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css">
  <link rel="stylesheet" type="text/css" href="vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css">
  <script type="text/javascript" src="vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
  <link rel="stylesheet" type="text/css" href="vendors/bootstrap-datetimepicker/build/css/bootstrap-datepicker3.css">

  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" type="text/css" href="vendors/bootstrap-iso-master/bootstrap-iso.css">

  <!-- Google Font: Source Sans Pro -->
  <link rel="shortcut icon" href="OneDrive.ico" />

  <script>
    $(document).ready(function() {
      var date_input = $('input[name="date"]'); //our date input has the name "date"
      var container = $('.bootstrap-iso form').length > 0 ? $('.bootstrap-iso form').parent() : "body";
      var options = {
        format: 'mm/dd/yyyy',
        container: container,
        todayHighlight: true,
        autoclose: true,
      };
      date_input.datepicker(options);
    })
  </script>


</head>

<body data-spy="scroll" data-target=".navbar" data-offset="50">


  <?php
  $username = "picktolight";
  $password = "pick_to_light";
  $db = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP) (HOST=192.168.156.5) (PORT=1521)
          )
          (CONNECT_DATA=(SERVER=dedicated)  (SERVICE_NAME=epsgdg)
          )
        )";

  $conn = ocilogon($username, $password, $db);
  $stid = oci_parse($conn, "begin dashboard; end;");
  oci_execute($stid);
  ?>
  <!-- Site wrapper -->
  <div class="wrapper">
    <!-- Navbar -->

    <div class="pos-f-t">
      <div class="collapse" id="navbarToggleExternalContent">
        <div class="bg-info p-3">
          <h3><span class="text-unmuted bg-info">Monitoring </span></h3>
          <form method="get">
            <div class="form-row align-items-center">
               <div class="col-sm-3 my-1">        
                  <input type="submit" formaction="url_when_press_enter" style="visibility: hidden; display: none;">
                   Tgl Proses : <input type="date" name="tgl">
                   <input type="submit"  class="btn btn-primary my-1"  formaction="short_pick2.php" value="ShortPick">
                </div>              
               </div>
            </form>
            <br>
  
          
          
            <a href="req_store.php"> <button type='button' class='btn btn-outline-light'>
              <h5> Item Order</h5>
            </button></a>
          <a href="prod_pick.php"> <button type='button' class='btn btn-outline-light'>
              <h5> PickTime </h5>
            </button></a>
            <a href="weekly2.php"> <button type='button' class='btn btn-outline-light'>
              <h5> 30 Days</h5>
            </button></a>
            <a href="pick_empty_xls.php"> <button type='button' class='btn btn-outline-light'>
              <h5>Empty Location</h5>
            </button></a>

        </div>
      </div>

      <div class="wrapper">
        <!-- Navbar -->

        <?php
        $sqlCont =  "select count(store) TOTAL FROM (
          SELECT distinct store FROM GOLD_HEADER_PICKING_SCALE AAAA, PTL_PICK_HEADER BBBB, PTL_PICK_DETAIL CCCC 
          WHERE TRUNC(TGL_PROSES) = TRUNC(SYSDATE)
          AND BBBB.CONTAINER_ID = CCCC.CONTAINER_ID
          AND AAAA.TGL_PROSES = TRUNC(BBBB.CREATED_AT)
          AND SUBSTR(BBBB.SHIPMENT_ID,1,2) = 'CR'       )";

        $stmt = ociparse($conn, $sqlCont);
        ociexecute($stmt);
        while (ocifetch($stmt)) {
          $conttoko       =  OCIResult($stmt, 'TOTAL');
        }
        ?>

        <div class="pos-f-t">
          <div class="collapse" id="navbarToggleExternalContent">
            <div class="bg-light p-3">
              <h4> <span class="text-unmuted light">Data Collection </span></h5>
              </h4>
              <a href="proses.php"> <button type='button' class='btn btn-warning'>
                  <h5>Process,
                    <?php
                    echo " STORE : " . $conttoko;
                    ?>
                </button></a>
              </h4>
              <form method="get">
                <h4>
                  <input type="submit" formaction="url_when_press_enter" style="visibility: hidden; display: none;">
                </h4>
                Tgl Proses : <input type="date" name="tgl">
                <input type="submit"  class="btn btn-outline-info mb-2" formaction="sum_store_pocar_xls.php" value="Rekap PO CAR Per Toko">
                <input type="submit" class="btn btn-outline-info mb-2" formaction="sum_detail_pocar_xls.php" value="Item Order">
                <input type="submit" class="btn btn-outline-info mb-2" formaction="sum_detail_store_pocar_xls.php" value="PO CAR Detail Per Toko">
                <input type="submit" class="btn btn-outline-info mb-2" formaction="zone_xls.php" value="Cost by Aisle">
              </form>
            </div>
          </div>

          <nav class="navbar navbar-dark bg-info">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>


            <?php
      $_tgl = date('d-m-Y H:i:s');

      $sqlCont =  " select sum(cost_po_car) TPOCAR, sum(cost_pick) TPICKING, round((sum(cost_pick)/sum(cost_po_car))*100,2) PCT  
      from REPORT_SUMMARY_ALL_STORE_DAY
      where tgl_proses = trunc(sysdate)";
      
      $tpocar = 0;
      $tpick = 0;
      $tanda ="";
      $stmt = ociparse($conn, $sqlCont);
      ociexecute($stmt);
            while (ocifetch($stmt)) {
        $tpocar    =  OCIResult($stmt, 'TPOCAR');
        $tpick    =  OCIResult($stmt, 'TPICKING');
        $pct    =  OCIResult($stmt, 'PCT');

       }
       
  
?>


            <section class="content-header">

              <div>
                <h2>LIST PROGRESS PICKING - EPS.
                  <?php
                  If ($tpocar > 0) {  
              echo      "<span >";
              echo      "<H5>";
              echo      " PO CAR &nbsp; : &nbsp; ".number_format("$tpocar"); 
              echo "&nbsp;|&nbsp;PICKING  :  &nbsp;".number_format("$tpick"); 
              echo "&nbsp;|&nbsp;SERV LVL  :  &nbsp;".number_format("$pct")."%"; 
            
              echo "</span>&nbsp";
                  }
     ?>
     </H5>
    
                </h2>
              </div>
            </section>

            <div class="card text-white bg-dark mb-3">
              <div>


              </div>
            </div>



        </div><!-- /.container-fluid -->
        <!-- /.content-wrapper -->
        <div>

          <!-- /.card-header -->
          <div class="table-responsive-sm">
            <table class="table table-bordered">

              <thead>

                <tr>
                  <h4>
                    <th style="width: 25%">
                      DESCRIPTION

                    </th>
                    <th style="width: 55%">
                      PROGRESS

                    </th>
                    <th style="width: 3%">
                      <div align="right">
                        TOTAL CONTAINER
                      </div>
                    </th>
                    <th style="width: 9%">
                      ACTION?
                    </th>
                    <h4>
                </tr>
                <h4>
              </thead>
              <tbody>

                <?php





                //      $conn = ocilogon($username,$password,$db);
                $stid = oci_parse($conn, "begin dashboard; end;");
                oci_execute($stid);


                //   $conn = ocilogon($username,$password,$db);
                $sqlCont = "select count(container_id) as total
                    FROM    (
                                  SELECT    TRIM (container_id) container_id
                                             FROM   CSV_ODL_TAB
                                            WHERE   TRUNC (tanggal) = TRUNC (SYSDATE)
                                        MINUS
                                           SELECT    TRIM (container_id) container_id
                                             FROM   PTL_PICK_HEADER
                                            WHERE   TRUNC (created_at) = TRUNC (SYSDATE)
                                            ) ";

                $stmt = ociparse($conn, $sqlCont);
                ociexecute($stmt);
                while (ocifetch($stmt)) {
                  $cont       =  OCIResult($stmt, 'TOTAL');
                }

                //   $conn = ocilogon($username,$password,$db);
                $sqlErr = "SELECT   
                    count(aaaa.container_id) as total                            
                    FROM PTL_PICK_HEADER AAAA, PTL_PICK_DETAIL BBBB --, ITEM_MST CCCC
                   WHERE AAAA.CONTAINER_ID = BBBB.CONTAINER_ID
                    -- AND CCCC.ITEM = BBBB.ITEM
                    and  AAAA.CONTAINER_ID in (  SELECT   CONTAINER_ID FROM ERR_LOCATION_PICK)";

                $stmt = ociparse($conn, $sqlErr);
                ociexecute($stmt);
                while (ocifetch($stmt)) {
                  $contErr       =  OCIResult($stmt, 'TOTAL');
                }




                $sql = " SELECT CASE 
                                    WHEN DESCRIPTION = 'ODL' THEN
                                    0
                                    WHEN   DESCRIPTION = 'DOWNLOAD' THEN
                                    1
                                    WHEN DESCRIPTION = 'CREATE FILE TO EPS' THEN
                                    2
                                    WHEN DESCRIPTION = 'DEFAULT MASUK DARI SCALE' THEN     
                                    3
                                    WHEN DESCRIPTION = 'PROSES PICKING MASUK EPS' THEN
                                    4
                                    WHEN DESCRIPTION = 'PUTAWAY BERHASIL KIRIM KE SCALE' THEN
                                    5
                                    WHEN DESCRIPTION = 'ERROR SAAT PENGAMBILAN XML PUTAWAY DARI TABEL' THEN 
                                    6
                                    ELSE
                                    7
                                    END NOMOR,
                                    CASE WHEN SUBSTR(DESCRIPTION,1,7) = 'CONFIRM' THEN  
                                         'ERR'
                                       ELSE  
                                       Substr(DESCRIPTION,1,3)
                                       END STATUS, 
                                    CASE  
                                    WHEN DESCRIPTION = 'DEFAULT MASUK DARI SCALE' THEN     
                                      'Container Id belum Picking EPS'
                                    WHEN DESCRIPTION = 'CREATE FILE TO EPS' THEN
                                     ' Buat File Container Id Untuk EPS'
                                     WHEN DESCRIPTION = 'ODL' THEN
                                     ' Container Id yang disiapkan ODL' 
                                    WHEN   DESCRIPTION = 'DOWNLOAD' THEN
                                       ' Container Id berhasil masuk SERVER EPS' 
                                    WHEN DESCRIPTION = 'PROSES PICKING MASUK EPS' THEN
                                      'Container Id siap masuk SCALE'
                                    WHEN DESCRIPTION = 'PUTAWAY BERHASIL KIRIM KE SCALE' THEN
                                      'Container Id berhasil masuk SCALE'
                                    WHEN DESCRIPTION = 'ERR MULTI JALUR' THEN
                                       'Error - Container Id multi Jalur'
                                   WHEN DESCRIPTION = 'CONTAINER ID NON EPS' THEN
                                       'Container Id NON EPS'
                                    WHEN DESCRIPTION = 'ERR LOCATION_PICK' THEN
                                       'Non EPS / Quick Alloc' 
                                    ELSE
                                      DESCRIPTION
                                    END DESCRIPTION,TTL, REC, PCT, FLAG, FLAG_PUTAWAY_SEND, CREATED_AT
                               From progress_tmp
                         --      where DESCRIPTION <> 'TOTAL CONFIRM PICK QTY = 0'
                               ORDER BY NOMOR                        ";
                $_row = 1;
                // where KD_STORE = '$kd_toko' AND created_at = to_date('$tgl','yyyy-mm-dd'))";
                $stmt = ociparse($conn, $sql);
                ociexecute($stmt);
                while (ocifetch($stmt)) {
                  $s_flag         =  OCIResult($stmt, 'FLAG');
                  $s_flagsending  =  OCIResult($stmt, 'FLAG_PUTAWAY_SEND');
                  $s_createat     =  OCIResult($stmt, 'CREATED_AT');

                  echo    "    <td>";
                  echo    "    <label>";
                  echo    "        <a>" . OCIResult($stmt, 'DESCRIPTION') . "</a>";
                  echo    "    </label>";
                  echo    "        <br/>";
                  echo    "    </td>";
                  echo    "  <td class='project_progress style='height: 30px;'>";
                  echo    "         <div class='progress'>";
                  echo    "             <div class='progress-bar progress-bar-striped bg-info progress-bar-animated' role='progressbar' aria-volumenow='" . OCIResult($stmt, 'PCT') . "' aria-volumemin='0' aria-volumemax='100' style='width: " . OCIResult($stmt, 'PCT') . "%'>";
                  echo    "             </div>";
                  echo    "        </div>";
                  echo    "    <label>";
                  //if ( OCIResult($stmt,'STATUS') <> 'ERR' AND OCIResult($stmt,'STATUS') <> 'PUT'  AND OCIResult($stmt,'STATUS') <> 'PRO' )
                  // {
                  //echo         OCIResult($stmt,'PCT')."% Complete";
                  // }
                  echo    "    </label>";
                  echo    "    </td>";
                  echo    "    <td> <div align='right'>  <label> " . number_format(OCIResult($stmt, 'REC'), 0, ",", ".") . "</label></div></td>";
                  echo "</td>";
                  echo "<td>";

                  if (OCIResult($stmt, 'DESCRIPTION') == 'Aisle 36,37, Quick Alloc') {
                ?>
                    <a href="err_lokpick.php"> <button type='button' align='center' class='btn btn-info'> <?php echo "$contErr"; ?> &nbsp;Lines</button></a>
                  <?php
                  } else if (OCIResult($stmt, 'DESCRIPTION') == 'Error - Container Id multi Jalur') {

                  ?>
                    <a href="err_multi.php"> <button type='button' align='center' class='btn btn-danger'>Informasi</button></a>
                  <?php

                  } else if (OCIResult($stmt, 'DESCRIPTION') == 'Container Id NON EPS') {

                  ?>
                    <a href="non_eps.php"> <button type='button' align='center' class='btn btn-primary'>Informasi</button></a>
                  <?php

                  } else if ((OCIResult($stmt, 'DESCRIPTION') == 'CONFIRM QTY PICK = 0')) {

                  ?>
                    <a href="confirm_qtynol.php"> <button type='button' align='center' class='btn btn-success'>Informasi</button></a>
                  <?php

                  } else
                            if (OCIResult($stmt, 'STATUS') == 'DOW') {


                  ?>
                    <a href="odl_pending.php"> <button type='button' align='center' class='btn btn-info'> <?php echo "$cont"; ?> &nbsp;Cont.Id</button></a>
                <?php



                  }
                  echo "</td>";
                  echo    "    </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>

        <section class="content-header">
          <div class="card text-white bg-info mb-3">
            <div class="card-header">
              <h1 class="card-title">


                <?php

                //     $conn = ocilogon($username,$password,$db);
                $sqlhold = " select count(store) TOTAL
                             from WOH_HDR_LOG
                             where trunc(created_at) = trunc(sysdate) ";

                $stmt = ociparse($conn, $sqlhold);
                ociexecute($stmt);
                while (ocifetch($stmt)) {
                  $hold        =  OCIResult($stmt, 'TOTAL');
                }


                $play1 = 'N';
                $play2 = 'N';
                $play3 = 'N';
                $play4 = 'N';
                $play5 = 'N';
                $play6 = 'N';
                $play7 = 'N';
                $play8 = 'N';
                $play9 = 'N';
                $play10 = 'N';


                $odl1 = 0;
                $odl2 = 0;
                $odl3 = 0;
                $odl4 = 0;
                $odl5 = 0;
                $odl6 = 0;
                $odl7 = 0;
                $odl8 = 0;
                $odl9 = 0;
                $odl10 = 0;
                $lodl = 0;
                $totalodl = 0;



                $toko1 = 0;
                $toko2 = 0;
                $toko3 = 0;
                $toko4 = 0;
                $toko5 = 0;
                $toko6 = 0;
                $toko7 = 0;
                $toko8 = 0;
                $toko9 = 0;
                $toko10 = 0;
                $totaltoko = 0;




                $stt1 = " ";
                $stt2 = " ";
                $stt3 = " ";
                $stt4 = " ";
                $stt5 = " ";
                $stt6 = " ";
                $stt7 = " ";
                $stt8 = " ";
                $stt9 = " ";
                $stt10 = " ";



                $Sqlplay1 = " select PLAY, ODL as ODL, SEQ, TOKO from CEKPLAY_TAB
       where trunc(time1) = trunc(sysdate)
       and play = 'Y'
       order by seq";

                $stmt = ociparse($conn, $Sqlplay1);
                ociexecute($stmt);
                while (ocifetch($stmt)) {

                  if (OCIResult($stmt, 'SEQ') == 1) {
                    $odl1 =   (OCIResult($stmt, 'ODL'));
                    $toko1 =   (OCIResult($stmt, 'TOKO'));
                    $play1 = 'Y';
                  } else if (OCIResult($stmt, 'SEQ') == 2) {
                    $toko2 =   (OCIResult($stmt, 'TOKO'));
                    $odl2 =    OCIResult($stmt, 'ODL');
                    $play2 = 'Y';
                  } else if (OCIResult($stmt, 'SEQ') == 3) {
                    $toko3 =   (OCIResult($stmt, 'TOKO'));
                    $odl3 =    OCIResult($stmt, 'ODL');
                    $play3 = 'Y';
                  } else if (OCIResult($stmt, 'SEQ') == 4) {
                    $toko4 =   (OCIResult($stmt, 'TOKO'));
                    $odl4 =    OCIResult($stmt, 'ODL');
                    $play4 = 'Y';
                  } else if (OCIResult($stmt, 'SEQ') == 5) {
                    $toko5 =   (OCIResult($stmt, 'TOKO'));
                    $odl5 =    OCIResult($stmt, 'ODL');
                    $play5 = 'Y';
                  } else if (OCIResult($stmt, 'SEQ') == 6) {
                    $toko6 =   (OCIResult($stmt, 'TOKO'));
                    $odl6 =    OCIResult($stmt, 'ODL');
                    $play6 = 'Y';
                  } else if (OCIResult($stmt, 'SEQ') == 7) {
                    $toko7 =   (OCIResult($stmt, 'TOKO'));
                    $odl7 =    OCIResult($stmt, 'ODL');
                    $play7 = 'Y';
                  } else if (OCIResult($stmt, 'SEQ') == 8) {
                    $toko8 =   (OCIResult($stmt, 'TOKO'));
                    $odl8 =    OCIResult($stmt, 'ODL');
                    $play8 = 'Y';
                  } else if (OCIResult($stmt, 'SEQ') == 9) {
                    $toko9 =   (OCIResult($stmt, 'TOKO'));
                    $odl9 =    OCIResult($stmt, 'ODL');
                    $play9 = 'Y';
                  } else if (OCIResult($stmt, 'SEQ') == 10) {
                    $toko10 =   (OCIResult($stmt, 'TOKO'));
                    $odl10 =    OCIResult($stmt, 'ODL');
                    $play10 = 'Y';
                  }


                  $totalodl  =  $odl1 + $odl2 + $odl3 + $odl4 + $odl5 + $odl6 + $odl7;
                  $totaltoko =  $toko1 + $toko2 + $toko3 + $toko4 + $toko5 + $toko6 + $toko7;

                  if ($play1 == 'Y') {
                    $stt1 = "PLAY";
                  }
                }

                if ($play2 == 'Y') {
                  $stt2 = "PLAY";
                }

                if ($play3 == 'Y') {
                  $stt3 = "PLAY";
                }


                if ($play4 == 'Y') {
                  $stt4 = "PLAY";
                }


                if ($play5 == 'Y') {
                  $stt5 = "PLAY";
                }

                if ($play6 == 'Y') {
                  $stt6 = "PLAY";
                }

                if ($play7 == 'Y') {
                  $stt7 = "PLAY";
                }


                if ($play8 == 'Y') {
                  $stt8 = "PLAY";
                }

                if ($play9 == 'Y') {
                  $stt8 = "PLAY";
                }


                if ($play10 == 'Y') {
                  $stt10 = "PLAY";
                }
                ?> &nbsp; &nbsp;

              </h1>




              <?php
              if ($stt1 == "PLAY") {
              ?>

                <button type="button" class="btn btn-outline-light btn-lg "">
            <span class=" badge badge-dark">
                  &nbsp;&nbsp;ODL 1 &nbsp;&nbsp;
                  &nbsp;<?php echo "$stt1"; ?> &nbsp;
                  </span>
                  <br>
                  <span class="badge badge-light">
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$toko1"); ?>&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$odl1"); ?> &nbsp;&nbsp;&nbsp;
                  </span>&nbsp;
                </button>
              <?php
              }
              ?>

              <?php
              if ($stt2 == "PLAY") {
              ?>

                &nbsp;
                <button type="button" class="btn btn-outline-light btn-lg">
                  <span class=" badge badge-dark">
                    &nbsp;&nbsp;ODL 2 &nbsp;&nbsp;
                    &nbsp;<?php echo "$stt2"; ?> &nbsp;</span>
                  <br>
                  <span class="badge badge-light">
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$toko2"); ?>&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$odl2"); ?> &nbsp;&nbsp;&nbsp;
                  </span>&nbsp;
                </button>
              <?php
              }
              ?>


              <?php
              if ($stt3 == "PLAY") {
              ?>

                &nbsp;
                <button type="button" class="btn btn-outline-light btn-lg "">
            <span class=" badge badge-dark">
                  &nbsp;&nbsp;ODL 3 &nbsp;&nbsp;
                  &nbsp;<?php echo "$stt3"; ?> &nbsp;</span>
                  <br>
                  <span class="badge badge-light">
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$toko3"); ?>&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$odl3"); ?> &nbsp;&nbsp;&nbsp;
                  </span>&nbsp;
                </button>
              <?php
              }
              ?>

              <?php
              if ($stt4 == "PLAY") {
              ?>

                &nbsp;
                <button type="button" class="btn btn-outline-light btn-lg">
                  <span class=" badge badge-dark">
                    &nbsp;&nbsp;ODL 4 &nbsp;&nbsp;
                    &nbsp;<?php echo "$stt4"; ?> &nbsp;</span>
                  <br>
                  <span class="badge badge-light">
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$toko4"); ?>&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$odl4"); ?> &nbsp;&nbsp;&nbsp;
                  </span>&nbsp;
                </button>
              <?php
              }
              ?>


              <?php
              if ($stt5 == "PLAY") {
              ?>

                &nbsp;
                <button type="button" class="btn btn-outline-light btn-lg">
                  <span class=" badge badge-dark">
                    &nbsp;&nbsp;ODL 5 &nbsp;&nbsp;
                    &nbsp;<?php echo "$stt5"; ?> &nbsp;</span>
                  <br>
                  <span class="badge badge-light">
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$toko5"); ?>&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$odl5"); ?> &nbsp;&nbsp;&nbsp;
                  </span>&nbsp;
                </button>
              <?php
              }
              ?>



              <?php
              if ($stt6 == "PLAY") {
              ?>

                &nbsp;
                <button type="button" class="btn btn-outline-light btn-lg "">
            <span class=" badge badge-dark">
                  &nbsp;&nbsp;ODL 6 &nbsp;&nbsp;
                  &nbsp;<?php echo "$stt6"; ?> &nbsp;</span>
                  <br>
                  <span class="badge badge-light">
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$toko6"); ?>&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;<?php echo number_format("$odl6"); ?> &nbsp;&nbsp;&nbsp;
                  </span>&nbsp;
                </button>
              <?php
              }
              ?>


              <?php
              if ($stt7 == "PLAY") {
              ?>

                &nbsp;
                <button type="button" class="btn btn-outline-light btn-lg "">
            <span class=" badge badge-dark">
                  &nbsp;&nbsp;ODL 7 &nbsp;&nbsp;
                  &nbsp;<?php echo "$stt7"; ?> &nbsp;</span>
                  <br>
                  <span class="badge badge-light">
                    &nbsp;&nbsp;&nbsp;<?php echo ("$toko7"); ?>&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;<?php echo ("$odl7"); ?> &nbsp;&nbsp;&nbsp;
                  </span>&nbsp;
                </button>
              <?php
              }
              ?>

              &nbsp;
              <button type="button" class="btn btn-outline-light btn-lg "">
            <span class=" badge badge-dark">
                &nbsp; &nbsp;
                &nbsp; &nbsp;&nbsp; <?php echo "TOTAL" ?> &nbsp;&nbsp; &nbsp; &nbsp; </span>
                <br>
                <span class="badge badge-light">
                  &nbsp;&nbsp;<?php echo number_format("$totaltoko"); ?>&nbsp;&nbsp;&nbsp;
                  &nbsp;&nbsp;<?php echo number_format("$totalodl"); ?> &nbsp;&nbsp;&nbsp;
                </span>&nbsp;
              </button>


              <!--
        &nbsp;
        <button type="button" class="btn btn-outline-light btn-lg "">
        <h5>ODL 8 &nbsp;   <span class="badge badge-warning">&nbsp; <?php echo "$stt8" . " - " . "$odl8"; ?> &nbsp; </span><h5>
        </button>


        &nbsp;
        <button type="button" class="btn btn-outline-light btn-lg "">
        <h5>ODL 9 &nbsp;   <span class="badge badge-warning">&nbsp; <?php echo "$stt9" . " - " . "$odl9"; ?> &nbsp; </span><h5>
        </button>


        &nbsp;
        <button type="button" class="btn btn-outline-light btn-lg "">
        <h5>ODL 10 &nbsp;  <span class="badge badge-warning">&nbsp; <?php echo "$stt10" . " - " . "$odl10"; ?> &nbsp; </span><h5>
     </button>
    -->

            </div>

          </div>

          <?php


          $sql = "select urutan URUTAN, JALUR, total total,tanda TANDA,lastpick  LASTPICK from monitoring_vw where urutan < 40 order by urutan ";



          $tampilan2 = "";
          $tampilan1 = "";
          $nomor = -1;


          echo    "    <table class='table table-bordered'>";
          echo  "    <div class='card-header'> ";
          echo  "    <div> ";
          echo  "           <h1>LIST PROGRESS TOTAL ITEM PICK PER JALUR </h1> ";
          echo  "        </div> ";
          echo  "       </div>";

          echo    "    <tr>";

          $ke = 0;
          $stmthdr = ociparse($conn, $sql);
          ociexecute($stmthdr);
          while (ocifetch($stmthdr)) {
            $nomor++;
            if ($nomor % 8 == 0) {
              echo    "    <tr>";
              $nomor = 0;
            }

            $waktu = "";
            echo " <td class='btn btn-transparant-light' >";

            if (OCIResult($stmthdr, 'TANDA') == 1) {

              $waktu =  "&nbsp;&nbsp;" . OCIResult($stmthdr, 'LASTPICK') . "' ";

              echo    "    <label>  ";
              echo  "<a href='.'" .  "class='btn btn-dark btn-lg ' role='button' aria-pressed='true' data-toggle='tooltip' data-placement='left' title='Check Aisle'>";
              echo   "&nbsp;&nbsp;&nbsp;&nbsp;" . (OCIResult($stmthdr, 'JALUR')) . "&nbsp;&nbsp;&nbsp;</h7> </br>" . number_format(OCIResult($stmthdr, 'TOTAL')) . $waktu . "&nbsp;&nbsp";
              //echo (OCIResult($stmthdr, 'NOMOR')) . "." . (OCIResult($stmthdr, 'STORE')) . "<br>" . (OCIResult($stmthdr, 'JML'));
              echo  "</a>";
            } else if (OCIResult($stmthdr, 'TOTAL') > 1500) {
              // echo " <td class='btn btn-danger' >";
              echo    "    <label>  ";
              echo  "<a href='.'" .  "class='btn btn-danger btn-lg' role='button' aria-pressed='true' data-toggle='tooltip' data-placement='left' title='Top Order'>";
              echo   "&nbsp;&nbsp;&nbsp;&nbsp;" . (OCIResult($stmthdr, 'JALUR')) . "&nbsp;&nbsp;&nbsp;</h7> </br>" . number_format(OCIResult($stmthdr, 'TOTAL')) . $waktu . "&nbsp;&nbsp";
              //echo (OCIResult($stmthdr, 'NOMOR')) . "." . (OCIResult($stmthdr, 'STORE')) . "<br>" . (OCIResult($stmthdr, 'JML'));
              echo  "</a>";
            } else if (OCIResult($stmthdr, 'TOTAL') > 1000 &&  OCIResult($stmthdr, 'TOTAL') < 1500) {
              // echo " <td class='btn btn-warning' >";
              echo    "    <label>  ";
              echo  "<a href='.'" .  "class='btn btn-warning btn-lg' role='button' aria-pressed='true' data-toggle='tooltip' data-placement='left' title='Middle Order'>";
              echo   "&nbsp;&nbsp;&nbsp;&nbsp;" . (OCIResult($stmthdr, 'JALUR')) . "&nbsp;&nbsp;&nbsp;</h7> </br>" . number_format(OCIResult($stmthdr, 'TOTAL')) . $waktu . "&nbsp;&nbsp";
              //echo (OCIResult($stmthdr, 'NOMOR')) . "." . (OCIResult($stmthdr, 'STORE')) . "<br>" . (OCIResult($stmthdr, 'JML'));
              echo  "</a>";
            } else {
              //echo " <td class='btn btn-success' >";
              echo    "    <label>  ";
              echo  "<a href='.'" .  "class='btn btn-success btn-lg' role='button' aria-pressed='true' data-toggle='tooltip' data-placement='left' title='Low Order'>";
              echo   "&nbsp;&nbsp;&nbsp;&nbsp;" . (OCIResult($stmthdr, 'JALUR')) . "&nbsp;&nbsp;&nbsp;</h7> </br>" . number_format(OCIResult($stmthdr, 'TOTAL')) . $waktu . "&nbsp;&nbsp";
              //echo (OCIResult($stmthdr, 'NOMOR')) . "." . (OCIResult($stmthdr, 'STORE')) . "<br>" . (OCIResult($stmthdr, 'JML'));
              echo  "</a>";
            }

            // echo "<a href='odl_pending.php' class='btn btn-primary btn-lg active' role='button' aria-pressed='true'>";
            // echo   "<h7>&nbsp;&nbsp;&nbsp;".(OCIResult($stmthdr, 'JALUR')) . "&nbsp;&nbsp;&nbsp;</h7> </br>" . number_format(OCIResult($stmthdr, 'TOTAL')). $waktu."&nbsp;&nbsp";
            // echo "</a>";



            //echo    "    <label>  ";
            // echo  "<a href='.'".  "class='btn btn-light btn-lg active' role='button' aria-pressed='true' data-toggle='tooltip' data-placement='left' title='Click me to see ContainerId'>";
            // echo   "&nbsp;&nbsp;&nbsp;&nbsp;".(OCIResult($stmthdr, 'JALUR')) . "&nbsp;&nbsp;&nbsp;</h7> </br>" . number_format(OCIResult($stmthdr, 'TOTAL')). $waktu."&nbsp;&nbsp";
            //echo (OCIResult($stmthdr, 'NOMOR')) . "." . (OCIResult($stmthdr, 'STORE')) . "<br>" . (OCIResult($stmthdr, 'JML'));
            // echo  "</a>";
            //}    

            //echo   "<h7>&nbsp;&nbsp;&nbsp;".(OCIResult($stmthdr, 'JALUR')) . "&nbsp;&nbsp;&nbsp;</h7> </br>" . number_format(OCIResult($stmthdr, 'TOTAL')). $waktu."&nbsp;&nbsp";
            echo    " </label>";
            echo    "    </td>";
          }


          $tampilan2 = $tampilan1;

          echo    "    </td>";
          echo    "    </tr>";
          echo "</table>";


          echo    "    <table class='table table-bordered '>";
          echo  "    <div class='card-header '> ";
          echo  "    <div> ";
          echo  "           <h1>LIST PROGRESS CONTAINER </h1> ";
          echo  "        </div> ";
          echo  "       </div>";


          ?>

          <?php


          //  $conn = ocilogon($username,$password,$db);
          //   $stid = oci_parse($conn, "begin dashboard; end;");
          //  oci_execute($stid);

          $sql = "select  'PRN' NOMOR, 'KOLI'
                    store, lpad(COUNT(KOLIAN),3,'#') ||' >> '|| lpad(COUNT(total),3,'#') JML
                    , CASE WHEN COUNT(KOLIAN) =count(total) THEN
                    0
                    ELSE
                    2
                    END HASIL
                    from
                    (
                    select MAX(CREATED_AT) TGL, store ,count(store) total
                    from
                    KOLI_TAB   GROUP BY STORE
                    ) AAAA,
                    (
                    SELECT  MAX(TGL) TGL, COUNT(STORE) KOLIAN
                    FROM
                    (
                    SELECT DISTINCT TRUNC(CREATED_AT) TGL, STORE  FROM
                    PTL_PICK_HEADER
                    WHERE TRUNC(CREATED_AT) = TRUNC(SYSDATE)
                    )
                    ) BBBB WHERE AAAA.TGL = BBBB.TGL
                    UNION ALL
                    SELECT NOMOR, STORE, JML, HASIL FROM PRN_KOLI_TAB_V";

          $tampilan2 = "";
          $tampilan1 = "";
          $nomor = -1;



          echo    "    <table class='table table-bordered'>";
          echo    "    <tr>";

          $ke = 0;
          $stmthdr = ociparse($conn, $sql);
          ociexecute($stmthdr);
          while (ocifetch($stmthdr)) {
            $nomor++;
            if ($nomor % 8 == 0) {
              echo    "    <tr>";
              $nomor = 0;
            }

            if (OCIResult($stmthdr, 'HASIL') == 0) {
              echo " <td class='btn btn-transparant disable'  >";
            } else if (OCIResult($stmthdr, 'HASIL') == 2) {
              echo " <td class='btn btn-transparant' >";
            } else {
              echo " <td class='btn btn-transparant'>";
            }


            // echo "<a href='pickingdcg_eps.php?STORE=".(OCIResult($stmthdr, 'STORE')) ;

            // echo " ";
            $tokonya = OCIResult($stmthdr, 'STORE');

            if (OCIResult($stmthdr, 'HASIL') == 0) {
              echo  "<a href=''  class='btn btn-info btn-lg active'  role='button'  data-toggle='tooltip' data-placement='top' title='Full Picking'>";
              echo (OCIResult($stmthdr, 'NOMOR')) . "." . (OCIResult($stmthdr, 'STORE')) . "<br>" . (OCIResult($stmthdr, 'JML'));
              echo  "</a>";
            } else if (OCIResult($stmthdr, 'HASIL') == 2) {
              echo  "<a href='cari_confirm.php?store=" . OCIResult($stmthdr, 'STORE') . "'" . "  class='btn btn-light btn-lg active' role='button' aria-pressed='true' data-toggle='tooltip' data-placement='left' title='Click me to see ContainerId'>";
              echo (OCIResult($stmthdr, 'NOMOR')) . "." . (OCIResult($stmthdr, 'STORE')) . "<br>" . (OCIResult($stmthdr, 'JML'));
              echo  "</a>";
            } else {
              echo  "<a href='cari_confirm.php?store=" . OCIResult($stmthdr, 'STORE') . "'" . "  class='btn btn-secondary btn-lg' role='button' aria-pressed='true' data-toggle='tooltip' data-placement='right' title='Click me to see ContainerId'>";
              echo (OCIResult($stmthdr, 'NOMOR')) . "." . (OCIResult($stmthdr, 'STORE')) . "<br>" . (OCIResult($stmthdr, 'JML'));
              echo  "</a>";
            }


            // echo "role='button' aria-pressed='true'>";
            //  echo (OCIResult($stmthdr, 'NOMOR')) . "." . (OCIResult($stmthdr, 'STORE')) . "<br>" . (OCIResult($stmthdr, 'JML'));
            //  echo "</a>";


            //      echo    "    <label> <h5>";
            //  echo (OCIResult($stmthdr, 'NOMOR')) . "." . (OCIResult($stmthdr, 'STORE')) . "</h5>" . (OCIResult($stmthdr, 'JML'));
            //  echo    " </label>";
            echo    "    </td>";
          }


          ?>



      </div><!-- /.container-fluid -->
      <!-- /.content-wrapper -->

      <footer>


      </footer>

      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-light">
        <!-- Control sidebar content goes here -->


      </aside>
      <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js"></script>
</body>

</html>