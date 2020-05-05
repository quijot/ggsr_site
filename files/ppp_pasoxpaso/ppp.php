<?php
if ($_POST['rinex-list']) {
  $rinex_dir = 'upload/';
  $rinex_file = $_POST['rinex-list'];
  $result_dir = 'ppp_results/';
  $cmd = 'python3 csrs_ppp_auto.py --user_name santiagonob@gmail.com --ref ITRF --rnx ' . $rinex_dir . $rinex_file . ' --results_dir ../' . $result_dir;
  exec($cmd, $out, $ret);
  if (!$ret)
    echo 'Procesamiento exitoso';
  else
    echo 'ERROR';
} else
  echo 'Debe seleccionar un archivo RINEX de la lista.';
