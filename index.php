<?php
include 'html/main_page.html';
ini_set('error_reporting', E_ALL);

  // airplane characteristics
  $S = 201.45; // m^2 - square
  $l = 37.55; // m - wingspan
  $b_a = 5.285; // m - average aerodynamic wing chord
  $al = 0.24; // pers AEwC - alignment
  $G0 = 80000; // kg - weight with fuel
  $G_f0 = 20000; // kg - fuel weight
  $q_eng = 0.585; // kg per s - fuel consumption for one engine
  $I_x = 250000; // kg * m * s^2 - cross moment of inertia
  $I_y = 900000; // kg * m * s^2 - roadway moment of inertia
  $I_z = 660000; // kg * m * s^2 - lengthwise moment of inertia

  // hs flight mode
  $V0 = 78; // m per s - speed // Vhf - speed of  horizontal flight
  $H0 = 500; // m - height
  $pr = 0.119; // (kg * s^2) per m^2 - pressure
  $An = 338.36; // m per s - sound velocity
  $Alpha_bal = 7.1; // deg
  $Tetta0 = 0; // deg
  $g = 9.81; // m per s^2 - gravitational acceleration
  $m = $G0 / $g; // N - Weight

  $P_1_Dg = 7003;
  $P_1_V = -13.8;

  $C_x_Alpha = 0.286;
  $C_x_M = 0;
  $C_xhf = 0.043;

  $C_y0 = -0.255;
  $C_y_Alpha = 5.78;
  $C_y_Dv = 0.2865;
  $C_y_M = 0;
  $C_yhf = 0.6446;

  $C_z_B = -1.0715;
  $C_z_Dn = -0.183;

  $m_x_Dn = -0.0206;
  $m_x_vWy = -0.31;
  $m_x_vWx = -0.583;
  $m_x_B = -0.186;
  $m_x_De = -0.0688;

  $m_y_vWy = -0.21;
  $m_y_B = -0.2;
  $m_y_Dn = -0.0716;
  $m_y_De = 0;
  $m_y_vWx = -0.006;

  $m_z0 = 0.2;
  $m_z_vWz = -13;
  $m_z_vDAlpha = -3.8;
  $m_z_vAlpha = -1.38;
  $m_z_Dv = -0.96;
  $m_z_M = 0;

  // automatic landing approach
  $k = array();
  $T = array();
  $Psi_RWY = 0;
  $k_Gamma = 2.0;
  $k_Wx = 1.5;
  $k_Wy = 2.5;
  $k_Wy_pre = 2.5;
  $k[3] = 1.3;
  $k[5] = 2.0; // before glide path
  ## $k[5] = 3.0; // after glide path
  $k[6] = 1.3;
  $k[10] = 8.0;
  $k[15] = 1.0;
  $k[17] = 170.0; // before glide path
  ## $k[17] = 120.0; // after glide path
  $T_Wx = 1.6;
  $T_Wy = 2.5;
  $T[5] = 2.3;
  $T[15] = 0.85;
  $T[17] = 2.3;
  ## F_De = +- 12 dergees
  ## F_Dn = +- 10 dergees
  ## F[1] = +- 25 dergees
  ## F[2] = +- 20 dergees
  $L_RWY = 3000; // m
  $S_LOCn = 167; // µA per dergee
  $DI_LOC = 0;
  $T_LOC = 0.2;
  $Psi_g0 = 90;

  // for calculations
  $DGp = (rad2deg($H0) / 2.67) - $L_RWY - 1000; // Gp - glide path
  $Ga_B = $m_y_B - (($C_z_B * $pr * $S * $l) / (4 * $m)) * $m_y_vWy;
  $W_x_De = -0.73;
  $Xx = (($m_x_B * $I_y) / ($m_y_B * $I_x)) * (1 / sqrt(1 - pow(($m_x_vWx / $I_x), 2) * $I_y * $S * pow($l, 2) * ($pr / (4 * $m_y_B))));
  $C_ybal = (2 * $G0) / ($S * $pr * pow($V0, 2));
  $A_bal = 57.3 * (($C_ybal - $C_y0) / $C_y_Alpha);

  // coefficients for linear math. model of side plane profile
  $a[1] = -(($m_y_vWy * $pr * pow($V0, 1)) / (4 * $I_y)) * $S * pow($l, 2);
  $a[2] = -(($m_y_B * $pr * pow($V0, 2)) / (2 * $I_y)) * $S * pow($l, 1);
  $a[3] = -(($m_y_Dn * $pr * pow($V0, 2)) / (2 * $I_y)) * $S * pow($l, 1);
  $a[4] = -(($C_z_B * $pr * pow($V0, 1)) / (2 * $m)) * $S;
  $a[5] = -(($m_x_Dn * $pr * pow($V0, 2)) / (2 * $I_x)) * $S * pow($l, 1);
  $a[6] = -(($m_x_vWy * $pr * pow($V0, 1)) / (4 * $I_x)) * $S * pow($l, 2);
  $a[7] = -(($C_z_Dn * $pr * pow($V0, 1)) / (2 * $m)) * $S;
  $b[1] = -(($m_x_vWx * $pr * pow($V0, 1)) / (4 * $I_x)) * $S * pow($l, 2);
  $b[2] = -(($m_x_B * $pr * pow($V0, 2)) / (2 * $I_x)) * $S * pow($l, 1);
  $b[3] = -(($m_x_De * $pr * pow($V0, 2)) / (2 * $I_x)) * $S * pow($l, 1);
  $b[4] = ($g / $V0) * cos(deg2rad($Alpha_bal)); // * cos($A_hf)
  $b[5] = -(($m_y_De * $pr * pow($V0, 2)) / (2 * $I_y)) * $S * pow($l, 1);
  $b[6] = -(($m_y_vWx * $pr * pow($V0, 1)) / (4 * $I_y)) * $S * pow($l, 2);
  $b[7] = sin(deg2rad($Alpha_bal)); // sin($A_hf)
  echo "<div class=\"container\">
    <div class=\"section\">";
  echo "<b>a</b>: ";
  var_dump($a);
  echo "</div>
    <div class=\"section\">";
  echo "<b>b</b>: ";
  var_dump($b);
  echo "</div>
  </div>";

  //////////////////////////////////////
  ////////////Control Panel/////////////
  //////////////////////////////////////
  // $mode = "free flight kappa";     // 
  // $mode = "free flight De";        // 
  $mode = "regulation";            //
  //                                  //
  // $positioning_method = "course";  //
  // $positioning_method = "path";    //
  // $positioning_method = "way";     //
  //                                  //
  $integration_method = "eiler";   //
  //                                  //
  // $signal = "zero";                // 
  $signal = "normal";              //
  //////////////////////////////////////

  $graph_data = array_fill(1,8,array());

  for($flight_case = 1; $flight_case <= 8; $flight_case++) {

    $t = 0; // s - flight time
    $td = 0; // s - output time
    $tg = 0; // s - graphics output time
    $tf = 300.1; // s - flight ending time
    $dt = 0.01; // 1 per s - integration step
    $dd = 25; // s - output step
    $gd = 5; // s - graphics output step

    $X = array_fill(1, 17, 0);
    $Y = array_fill(1, 17, 0);
    $F = array_fill(1, 2, 0);
    // $X[7] = 1;
    $Y[7] = 0; // m
    $Y[17] = $G_f0; // kg

    switch($flight_case) {
      case 1 : {
        $Y[6] = $Psi_g0;
        $Y[9] = $Psi_g0;
        $Y[8] = -5000;
        $S_LOC = $S_LOCn;
      break;
      }
      case 2 : {
        $Y[6] = $Psi_g0;
        $Y[9] = $Psi_g0;
        $Y[8] = -3000;
        $S_LOC = $S_LOCn;
      break;
      }
      case 3 : {
        $Y[6] = $Psi_g0;
        $Y[9] = $Psi_g0;
        $Y[8] = -2000;
        $S_LOC = $S_LOCn;
      break;
      }
      case 4 : {
        $Y[6] = $Psi_g0;
        $Y[9] = $Psi_g0;
        $Y[8] = -3000;
        $S_LOC = 54;
      break;
      }
      case 5 : {
        $Y[6] = $Psi_g0;
        $Y[9] = $Psi_g0;
        $Y[8] = -3000;
        $S_LOC = $S_LOCn;
      break;
      }
      case 6 : {
        $Y[6] = $Psi_g0;
        $Y[9] = $Psi_g0;
        $Y[8] = -3000;
        $S_LOC = 280;
      break;
      }
      case 7 : {
        $Y[6] = 0;
        $Y[9] = 0;
        $Y[8] = 300;
        $S_LOC = $S_LOCn;
      break;
      }
      case 8 : {
        $Y[6] = -$Psi_g0;
        $Y[9] = -$Psi_g0;
        $Y[8] = 2000;
        $S_LOC = $S_LOCn;
      break;
      }
    }

    $W = 0;
    $NV = 0;
    $Dn = 0;
    $De = 0;

    echo "<div class=\"container no-pad-bot scrollspy\" id=\"flightcase-" . $flight_case ."\">
      <div class=\"section\">
        <h4>Flight case " . $flight_case . ":</h4>
      </div>
      <div class=\"divider\">
      </div>
      <div class=\"section\">" .
        "<h5 aling=\"left\">" .
        "Mode value = <u>" . $mode . "</u>. " .
        "Integration method value = <u>" . $integration_method . "</u></br>" .
        "Integration step value = <u>" . $dt . "</u></br>" .
        "Z0 value = <u>" . $Y[8] . "</u>. Psi_g0 value = <u>" . $Y[6] . "</u>. S_LOC value = <u>" . $S_LOC . "</u>" .
        "</h5>" .
      "</div>
      <div class=\"section\">
        <table width=\"100%\" cellspacing=\"0\" border=\"1\" class=\"highlight\">
          <thead>
            <tr>
              <th>T</th>
              <th>De</th>
              <th>Dn</th>
              <th>Psi</th>
              <th>Gamma</th>
              <th>Betta</th>
              <th>Psi_g</th>
              <th>X</th>
              <th>Z</th>
              <th>DPsi</th>
              <th>I_LOC</th>
              <th>Epsilon_k</th>
              <th>Gamma_set</th>
              <th>G_f</th>
            </tr>
          </thead>
          <tbody>";

    for($t; $t <= $tf; $t += $dt) {

      if($Y[7] <= $DGp) {
        $k[5] = 3.0;
        $k[17] = 120;
      }

      $X[1] = $Y[2]; // pPsi
      $X[2] = -$a[1] * $Y[2] - $b[6] * $Y[4] - $a[2] * $Y[5] - $a[3] * $Dn - $b[5] * $De; // pWy
      $X[3] = $Y[4]; // pGamma
      $X[4] = -$b[1] * $Y[4] - $a[6] * $Y[2] - $b[2] * $Y[5] - $a[5] * $Dn - $b[3] * $De; // pWx
      $X[5] = $Y[2] + $b[4] * $Y[3] + $b[7] * $Y[4] - $a[4] * $Y[5] - $a[7] * $Dn; // pBetta
      // $Psi_g = -1 * $Y[1];
      $X[6] = -1 * $X[1]; // pPsi_g
      $W_x = $W * cos(deg2rad($NV - $Y[6]));
      $W_z = $W * sin(deg2rad($NV - $Y[6]));
      $V_sh = $V0 + $W_x;
      $X[7] = $V_sh * cos(deg2rad($Y[6] + $Y[5])); // pD_RWY
      $X[8] = $V_sh * sin(deg2rad($Y[6] + $Y[5])); // pZ
      $X[9] = $X[6] - $Psi_RWY; // pDPsi

      $X[17] = -3 * $q_eng; // pG_f
      // $P_p = rad2deg(atan($Y[7] / $Y[6]));

      switch($mode) {
        case "regulation" : {
          $Epsilon_k_pre = rad2deg(atan($Y[8] / ($Y[7] + $L_RWY + 1000)));
          $I_LOC_pre = $S_LOC * $Epsilon_k_pre + $DI_LOC;
          if($I_LOC_pre > 250) {
            $I_LOC_pre = 250;
          } elseif($I_LOC_pre < -250) {
            $I_LOC_pre = -250;
          }
          $X[10] = ($I_LOC_pre - $Y[10]) / $T_LOC; // pI_LOC
          // $Epsilon_k = $Y[10] / $S_LOCn;
          $X[11] = $X[10] / $S_LOCn; // pEpsilon_k

          $X[12] = ($k[5] * $X[9] - $Y[12]) / $T[5]; // 
          $X[13] = ($k[17] * $X[11] - $Y[13]) / $T[17]; //
          $F[1] = -1 * $k[3] * $Y[9] + $k[10] * $Y[11]; //
          if($F[1] > 25) {
            $F[1] = 25;
          } elseif($F[1] < -25) {
            $F[1] = -25;
          }
          $F[2] = ($k[6] * $Y[9] + $Y[12] + $Y[13] + $F[1]); //
          if($F[2] > 20) {
            $F[2] = 20;
          } elseif($F[2] < -20) {
            $F[2] = -20;
          }
          $X[14] = (-1 * $k[15] * $F[2] - $Y[14]) / $T[15]; // Gamma_set

          $X[15] = $k_Wx * $X[4] - ($Y[15] / $T_Wx); //
          $De = $k_Gamma * ($Y[3] - $Y[14]) + $Y[15]; // De
          if($De > 12) {
            $De = 12;
          } elseif($De < -12) {
            $De = -12;
          }
          
          $X[16] = $k_Wy * $X[2] - ($Y[16] / $T_Wy); //
          $Dn = $Y[16] + $k_Wy_pre * $Y[2]; // Dn
          if($Dn > 10) {
            $Dn = 10;
          } elseif($Dn < -10) {
            $Dn = -10;
          }
        break;
        }
      }
      
      for($t; $t >= $td; $td += $dd){
        echo  "<tr>
        <td>" . number_format($td, 0, '.', ' ') . "</td>
        <td>" . number_format($De, 4, '.', ' ') . "</td>
        <td>" . number_format($Dn, 4, '.', ' ') . "</td>
        <td>" . number_format($Y[1], 2, '.', ' ') . "</td>
        <td>" . number_format($Y[3], 2, '.', ' ') . "</td>
        <td>" . number_format($Y[5], 4, '.', ' ') . "</td>
        <td>" . number_format($Y[6], 2, '.', ' ') . "</td>
        <td>" . number_format($Y[7], 0, '.', ' ') . "</td>
        <td>" . number_format($Y[8], 0, '.', ' ') . "</td>
        <td>" . number_format($Y[9], 2, '.', ' ') . "</td>
        <td>" . number_format($Y[10], 1, '.', ' ') . "</td>
        <td>" . number_format($Y[11], 4, '.', ' ') . "</td>
        <td>" . number_format($Y[14], 4, '.', ' ') . "</td>
        <td>" . number_format($Y[17], 0, '.', ' ') . "</td>
        </tr>";
      }

      for($t; $t >= $tg; $tg += $gd){
        array_push($graph_data[$flight_case], ["time" => $td, "Gamma" => $Y[3], "Psi_g" => $Y[6], "X" => $Y[7], "Z" => $Y[8], "G_f" => $Y[17]]);
        if($Y[7] >= 18300) {
          break 2;
        }
      }


      switch($integration_method) {
        case "eiler" : {
          for($i = 1; $i <= 17; $i++){
            $Y[$i] += $X[$i] * $dt;
          }
        break;
        }
      }
    }
          echo "</tbody>
        </table><br/>
      </div>";
    $graph_data_file = 'data' . $flight_case . '.json';
    $handle = fopen($graph_data_file, 'w') or die ('Cannot open file: ' . $graph_data_file);
    $graph_content = json_encode($graph_data[$flight_case]);
    fwrite($handle, $graph_content);
      echo "<div class = \"section\">
        <div class=\"chartWithMarkerOverlay\">
          <div id = \"chart_div_fc" . $flight_case . "\" style = \"width: 1000px; height: 500px; margin-left: -100px;\">
          </div>
          <div id = \"chart_div_mp" . $flight_case . "\" class = \"overlay-marker\">
            <img src = \"img/baseline_airplanemode_active_black_48_fliped.png\" class = \"gwd-img-" . $flight_case . $flight_case . $flight_case . $flight_case . " gwd-gen-" . $flight_case . $flight_case . $flight_case . $flight_case . "gwdanimation\"
            data-gwd-motion-path-key = \"gwd-motion-path-" . $flight_case . $flight_case . $flight_case . $flight_case . "\" data-gwd-has-tangent-following = \"\">
          </div>
        </div>
      </div>
    </div>";
  }

  $Vi = $V0 * 3.6 * sqrt(($pr)/(0.1249)); // Vhf
  $M = $Vi / $An;
  echo "<div class=\"container\">
    <div class=\"section\">
      <h4>Vi = " . $Vi . "</h2>
      <h5>M = " . $M . "</h3>
      <h5>Dgp = " . $DGp . "</h3>
    </div>
  </div>";
?>
<html>
<body>
  </main>
  <?php include 'html/footer.html';?>
  <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script src="js/materialize.js"></script>
  <script src="js/init.js"></script>
  <script type = "text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
      var SSpy_elements = document.querySelectorAll('.scrollspy');
      var SSpy_options = {throttle: 100, scrollOffset: 5, activeClass: "active"};
      var instances = M.ScrollSpy.init(SSpy_elements, SSpy_options);
    });
  </script>
  <script type = "text/javascript">
    function scrollToTop() {
      var graphics = document.getElementById("top-nav");
      graphics.scrollIntoView({block: "start", behavior: "smooth"});
    }
  </script>
  <script type = "text/javascript" src = "https://www.gstatic.com/charts/loader.js">
  </script>
  <script type = "text/javascript">
    google.charts.load('current', {packages: ['corechart','line']});
  </script>
  <script type="text/javascript" src="motionpath_runtime.min.1.0.js" gwd-motionpath-version="1.0">
  </script>
  <script>
    function chart_div_fc1() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 1');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 8; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[1]) - 1); $i++) {
            echo "["
            . $json_data[1][$i]['X'] . ",  "
            . $json_data[1][$i]['Z']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        animation: {
          startup: true,
          duration: 1000,
          easing: 'out'
        },
        'title' : 'flight case 1',
        curveType: 'function',
        colors: ['blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc1'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp1').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 58 + "px";
        document.querySelector('#chart_div_mp1').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 180 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc1);

    function chart_div_fc2() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 2');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 5; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[2]) - 1); $i++) {
            echo "["
            . $json_data[2][$i]['X'] . ",  "
            . $json_data[2][$i]['Z']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 2',
        curveType: 'function',
        colors: ['blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc2'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp2').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 50 + "px";
        document.querySelector('#chart_div_mp2').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 170 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc2);

    function chart_div_fc3() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 3');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 5; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[3]) - 1); $i++) {
            echo "["
            . $json_data[3][$i]['X'] . ",  "
            . $json_data[3][$i]['Z']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 3',
        curveType: 'function',
        colors: ['blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc3'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp3').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 50 + "px";
        document.querySelector('#chart_div_mp3').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 155 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc3);

    function chart_div_fc4() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 4');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 5; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[4]) - 1); $i++) {
            echo "["
            . $json_data[4][$i]['X'] . ",  "
            . $json_data[4][$i]['Z']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 4',
        curveType: 'function',
        colors: ['blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc4'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp4').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 115 + "px";
        document.querySelector('#chart_div_mp4').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 170 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc4);

    function chart_div_fc5() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 5');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 5; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[5]) - 1); $i++) {
            echo "["
            . $json_data[5][$i]['X'] . ",  "
            . $json_data[5][$i]['Z']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 5',
        curveType: 'function',
        colors: ['blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc5'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp5').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 58 + "px";
        document.querySelector('#chart_div_mp5').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 180 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc5);

    function chart_div_fc6() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 6');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 8; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[6]) - 1); $i++) {
            echo "["
            . $json_data[6][$i]['X'] . ",  "
            . $json_data[6][$i]['Z']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 6',
        curveType: 'function',
        colors: ['blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc6'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp6').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 48 + "px";
        document.querySelector('#chart_div_mp6').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 165 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc6);

    function chart_div_fc7() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 7');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 8; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[7]) - 1); $i++) {
            echo "["
            . $json_data[7][$i]['X'] . ",  "
            . $json_data[7][$i]['Z']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 7',
        curveType: 'function',
        colors: ['blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc7'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp7').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 58 + "px";
        document.querySelector('#chart_div_mp7').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 180 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc7);

    function chart_div_fc8() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 8');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 8; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[8]) - 1); $i++) {
            echo "["
            . $json_data[8][$i]['X'] . ",  "
            . $json_data[8][$i]['Z']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 8',
        curveType: 'function',
        colors: ['blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc8'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp8').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 58 + "px";
        document.querySelector('#chart_div_mp8').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 180 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc8);
  </script>
</body>
</html>