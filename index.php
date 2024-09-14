<!DOCTYPE html>
<html lang="de">

<head>
  <title>Training</title>

  <link rel="apple-touch-icon" sizes="180x180" href="images/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="images/favicon/favicon-16x16.png">
  <link rel="manifest" href="manifest.json" />
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">
  <meta name="robots" content="noindex" />

  <meta name="Description" content="Training attendance app for table tennis club Schenkon">
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="referrer" content="no-referrer" />
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=2.5, user-scalable=yes' />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black" />
  <meta http-equiv="refresh" content="90">
  <meta http-equiv="Content-Language" content="de">

  <!-- Include Required Prerequisites -->
  <script src="https://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" />

  <!-- dark mode -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-dark@1.0.3/src/bootstrap-dark.min.css" rel="stylesheet">

  <!-- Include Date Range Picker -->
  <script type="text/javascript" src="datepicker/js/bootstrap-datepicker.min.js"></script>
  <script type="text/javascript" src="datepicker/locales/bootstrap-datepicker.de.min.js"></script>
  <link rel="stylesheet" type="text/css" href="datepicker/css/bootstrap-datepicker3.min.css" />
  <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
  <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
  
  <link rel="stylesheet" type="text/css" href="css/style.css" />

  <script>
    var a = document.getElementsByTagName("a");
    for (var i = 0; i < a.length; i++) {
      a[i].onclick = function() {
        window.location = this.getAttribute("href");
        return false
      }
    }
  </script>

  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('js/sw.js')
    };
  </script>

</head>

<body style="margin:10px">
  <span id="usrTxt" style="display:none"></span>

  <script>
    var jsUser = <?php echo json_encode($_POST['name'] ?? null) ?>;
    if (jsUser != null && localStorage) localStorage.setItem('user', jsUser);
    if (jsUser == null && localStorage) jsUser = localStorage.getItem('user');
    document.getElementById("usrTxt").innerHTML = jsUser;
  </script>

  <?php

  include '../../../config.php';

  setlocale(LC_ALL, 'de_DE');

  // read cookie / storage
  if (isset($_COOKIE['user'])) {
    $user = $_COOKIE['user'];
  }

  function Conn()
  {
    global $conn;
    global $server;
    global $dbuser;
    global $dbpwd;
    global $db;

    $conn = mysqli_connect($server, $dbuser, $dbpwd, $db);

    if (mysqli_connect_errno()) {
      echo "DB-Error: " . mysqli_connect_error();
    }
  };

  function DisConn()
  {
    global $conn;
    mysqli_close($conn);
  };

  // get form data
  $name = $_POST['name'];
  $training = $_POST['training'];
  $insert = $_POST['insert'];
  $comment = $_POST['comment'];

  //CHOICE: on
  //DATE: 27.05.2016

  if ($insert) { //INSERT
    if (!$name) {
      die("<br><br><b><font color='red'>Zuwenig Angaben!</font></b><br><br><a href=javascript:history.back()>zurück</a>");
    }

    setcookie("user", $name, time() + 60 * 60 * 24 * 720, "/"); //720days
    Conn();

    $training = $training == "on" ? "Y" : "N";
    $datum = date("Y-m-d", strtotime($today));

    if (isset($_POST['date'])) {
      $datum = date("Y-m-d", strtotime($_POST['date']));
      //check date
      $datepost = strtotime($_POST['date']);
      //$datelimit = strtotime($today. ' + 120 days');
      if ($datepost < strtotime($today) /* || $datepost > $datelimit */) {
        die("<br><br><b><font color='red'>Ungültiges Datum!</font></b> (darf nicht älter als heute sein)<br><br><a href=javascript:history.back()>zurück</a>");
      }
    }

    if (!is_null($max_limit) && $training == 'Y' && $datum != '2020-06-19') { //exclude GV
      $daycount = "SELECT count(*) FROM `training_poll` WHERE date = '$datum' AND choice = 'Y'";
      $rs = mysqli_query($conn, $daycount) or die(mysqli_error());

      if (mysqli_fetch_row($rs)[0] >= $max_limit) {
        die("<br><br><b><font color='red'>Trainings-Limite von '" . $max_limit . "' bereits erreicht!</font></b><br><br><a href=javascript:history.back()>zurück</a>");
      }
    }

    $kw = new DateTime($datum);
    $kw = $kw->format('W');
    $sql_insert = "insert into `training_poll` (comment,date,user,choice,week,host) values ('" . mysqli_real_escape_string($conn, $comment) . "','" . mysqli_real_escape_string($conn, $datum) . "','" . mysqli_real_escape_string($conn, $name) . "','" . mysqli_real_escape_string($conn, $training) . "','" . $kw . "','" . $host . "')";

    if (!mysqli_query($conn, $sql_insert)) {
      echo "<i><font size='2' color='red'>Einfügen nicht möglich! (" . mysqli_error() . ")</font></i>\n";
    }

    DisConn();

    //unset($_POST['name']);
    //unset($_POST['date']);

    header('Location: https://ttcschenkon.ch/trainingsapp/');
  }

  if (isset($_GET['usr'])) { //DELETE
    Conn();
    $datum = $today;

    if (isset($_GET['dat'])) {
      $datum = mysqli_escape_string($conn, $_GET['dat']);
    }

    $deleteStmt = "DELETE FROM `training_poll` WHERE user='" . mysqli_real_escape_string($conn, $_GET['usr']) . "' and date='" . $datum . "'";

    if (!mysqli_query($conn, $deleteStmt)) {
      echo "<i><font size='2' color='red'>Löschen nicht möglich! (" . mysqli_error() . ")</font></i>\n";
    }

    DisConn();
    unset($_GET['usr']);
    unset($_GET['dat']);

    //header('Location: https://ttcschenkon.ch/trainingsapp/');
  }

  echo "<form method='POST' action='" . $_SERVER["PHP_SELF"] . "' name='trainingform' accept-charset='UTF-8'>";

  ?>

  <div class="row">
    <div class="col-sm-4">
      <fieldset class="form-group">
        <label for="attendance">Training</label><br />
        <input id="attendance" type="checkbox" name="training" checked data-toggle="toggle" data-on="Ja" data-off="Nein">
      </fieldset>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4">
      <fieldset class="form-group">
        <label for="name">Name</label>
        <input type="text" class="form-control" name="name" id="name" placeholder="Name">
        <script>
          document.getElementById('name').value = document.getElementById('usrTxt').innerHTML;
        </script>
      </fieldset>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4">
      <fieldset class="form-group">
        <label for="date">Datum</label>
        <div class="input-group date">
          <input id="date" type="text" name="date" value="<?php echo $today; ?>" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
        </div>
      </fieldset>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4">
      <fieldset class="form-group">
        <label for="comment">Kommentar</label>
        <input type="text" class="form-control" id="comment" name="comment" placeholder="Kommentar...">
      </fieldset>
    </div>
  </div>

  <input type='submit' name='insert' class="btn btn-primary btn-lg" value='Eintragen'>
  </form><br />

  <?php
  $seldate = date("Y-m-d", time());
  $sql = "SELECT * FROM `training_poll` WHERE date >= '$seldate' order by date, choice desc, user";

  Conn();
  $result = mysqli_query($conn, $sql) or die(mysqli_error());

  $titleToday = 1;
  $titleLater = 1;
  $titleWeek = 1;
  $openTag = 0;

  echo "<div class='panel panel-default dates' style='border-top-right-radius: 0px; border-right-width: 0px; border-bottom-right-radius: 0px; border-bottom-width: 0px;'><div class='panel-body' style='padding-top: 5px;'>";

  while ($zeile = mysqli_fetch_array($result)) {
    $choice = $zeile['choice'] == 'Y' ? "<font color='#00CD00'>Ja</font>" : "<font color='#CD0000'>Nein</font>";
    $outDate = new DateTime($zeile['date']);
    $kw = $outDate->format('W');

    if ($seldate == $zeile['date']) {
      if ($titleToday == 1) {
        echo "<h3>Heute im Training</h3><table class='.table'>\n";
        $titleToday = 0;
        $openTag = 1;
      }

      echo "<tr><td nowrap>" . $zeile['user'] . "</td><td>&nbsp;[<b>" . $choice . "</b>]</td>\n";

      if ($zeile['host'] == $host || $host == $admin_ip || $user == $zeile['user'] || $_POST['name'] == $zeile['user'] || $_GET['usr'] == $zeile['user']) {
        echo "<td nowrap>&nbsp;&nbsp;<font size='2'><a href='index.php?usr=" . $zeile['user'] . "&dat=" . $zeile['date'] . "'><img src='https://www.ttcschenkon.ch/trainingsapp/images/cross.png' alt='Löschen' HEIGHT='14', WIDTH='14' border='0'></a></font></td>\n";
      } else {
        echo "<td/>\n";
      }

      if ($zeile['comment'] != "") {
        echo "<td nowrap><font color='#cccccc'>&nbsp;&nbsp;&nbsp;<i>" . $zeile['comment'] . "</i></font></td>\n";
      }
      echo "</tr>\n";
    } else if ($kw == $week) {

      if ($titleWeek == 1) {
        if ($openTag == 1) echo "</table>\n";
        echo "<h3>Trainings diese Woche</h3><table class='.table'>\n";
        $titleWeek = 0;
        $openTag = 1;
      }

      echo "<tr><td nowrap>" . $days[$outDate->format('w')] . ", " . $outDate->format('d.m.') . "&nbsp;&nbsp;</td><td nowrap>" . $zeile['user'] . "</td><td>&nbsp;&nbsp;[<b>" . $choice . "</b>]</td>\n";

      if ($zeile['host'] == $host || $host == $admin_ip || $user == $zeile['user']) {
        echo "<td>&nbsp;&nbsp;<font size='2'><a href='.?usr=" . $zeile['user'] . "&dat=" . $zeile['date'] . "'><img src='https://www.ttcschenkon.ch/trainingsapp/images/cross.png' alt='Löschen' HEIGHT='14', WIDTH='14' border='0'></a></font></td>\n";
      } else {
        echo "<td/>\n";
      }

      if ($zeile['comment'] != "") {
        echo "<td nowrap><font color='#cccccc'>&nbsp;&nbsp;&nbsp;<i>" . $zeile['comment'] . "</i></font></td>\n";
      }
      echo "</tr>\n";
    } else {
      if ($titleLater == 1) {
        if ($openTag == 1) echo "</table>\n";

        echo "<h3>Spätere Trainings</h3><table class='.table'>\n";
        $titleLater = 0;
        $openTag = 1;
      }

      echo "<tr><td nowrap>" . $days[$outDate->format('w')] . ", " . $outDate->format('d.m.') . "&nbsp;&nbsp;</td><td nowrap>" . $zeile['user'] . "</td><td>&nbsp;&nbsp;[<b>" . $choice . "</b>]</td>\n";

      if ($zeile['host'] == $host || $host == $admin_ip || $user == $zeile['user']) {
        echo "<td>&nbsp;&nbsp;<font size='2'><a href='index.php?usr=" . $zeile['user'] . "&dat=" . $zeile['date'] . "'><img src='https://www.ttcschenkon.ch/trainingsapp/images/cross.png' alt='Löschen' HEIGHT='14', WIDTH='14' border='0'></a></font></td>\n";
      } else {
        echo "<td/>\n";
      }

      if ($zeile['comment'] != "") {
        echo "<td nowrap><font color='#cccccc'>&nbsp;&nbsp;&nbsp;<i>" . $zeile['comment'] . "</i></font></td>\n";
      }

      echo "</tr>\n";
    }
  }

  if ($openTag == 1) {
    echo "</table>\n";
  }

  //clubevents
  $title = 0;
  $sql = "SELECT * FROM wb_mod_procalendar_actions WHERE date_start >= CURRENT_DATE() and acttype = '3' order by date_start ASC, time_start ASC";
  $result = mysqli_query($conn, $sql) or die(mysqli_error());

  while ($evt = mysqli_fetch_array($result)) {
    if ($title == 0) {
      $title = 1;
      echo "<h3>Nächste Clubevents</h3>";
      echo "<table class='.table'>";
    }

    if ($cnt < 5) {
      $dat = new DateTime($evt['date_start']);
      echo "<tr><td>" . $dat->format('d.m.y') . " - " . $evt['name'] . "</td></tr>"; // 'd.m.y H:i\ '	  
      $cnt++;
    }
  }

  if ($title == 1) {
    echo "</table>";
  }

  mysqli_free_result($result);
  DisConn();

  ?>

  </div>
  </div>

  <script>
    $(function() {
      $('.date').datepicker({
        format: "dd.mm.yyyy",
        todayBtn: "linked",
        language: "de",
        startDate: "now",
        //endDate: "+20d",
        daysOfWeekHighlighted: "2,5",
        autoclose: true,
        todayHighlight: true
      });
    });
  </script>

</body>

</html>