<?php

require 'env.php';

function verify_captcha()
{
  if (isset($_POST['recaptcha_response'])) {
    // Build POST request:
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_response = $_POST['recaptcha_response'];
    // Make and decode POST request:
    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);
    // Return true or false based on the score returned
    return ($recaptcha->success == true && $recaptcha->score >= 0.5);
  }
}

function upload_file()
{
  $target_dir = 'upload/';
  $filter = '*.{gz,zip,Z}';
  $source_path = $_FILES['file']['tmp_name'];
  $filename = $_FILES['file']['name'];
  $target_path = $target_dir . $filename;
  if (file_exists($target_path))
    load_files_to_select('rinex-list', $target_dir, $filter, $target_path);
  elseif (is_uploaded_file($source_path)) {
    if (move_uploaded_file($source_path, $target_path))
      load_files_to_select('rinex-list', $target_dir, $filter, $target_path);
  }
}

function load_files_to_select($select_id, $dir, $filter, $selected_file = "", $show_path = false, $show_size = false)
{
  $files = glob($dir . $filter, GLOB_BRACE);
  echo '<select class="form-control" multiple="" id="' . $select_id . '" name="' . $select_id . '">';
  foreach ($files as $f) {
    $fname =  $show_path ? $f : str_replace($dir, '', $f);
    $fsize = !$show_size ? '' : '&emsp;' . round(filesize($f) / 1024 ** 2, 2) . 'MB';
    $sel =  $f != $selected_file ? '' : ' selected';
    echo '<option' . $sel . '>' . $fname . $fsize . '</option>';
  }
  echo '</select>';
}

$MAX_SIZE = 20 * 1024 * 1024;
$VALID_TYPES = array('application/gzip', 'application/x-compress', 'application/zip');
$_ERRORS = array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE, UPLOAD_ERR_NO_FILE);

if (!empty($_FILES))
  if (in_array($_FILES['file']['error'], $_ERRORS))
    echo 'No file or wrong size!';
  elseif (!in_array($_FILES['file']['type'], $VALID_TYPES))
    // Verify type
    echo 'Wrong filetype!';
  elseif ($_FILES['file']['size'] > $MAX_SIZE)
    // Verify size
    echo 'Wrong size!';
  elseif (verify_captcha())
    upload_file();
  else
    echo 'R U a robot?';

if ($_GET['upload'])
  echo '<a href="." class="btn btn-sm btn-danger">Reintentar</a>';
