<?php
error_reporting(0);

// Config
$server = array(
    'base' => 'http://192.168.0.20/',
    'cups' => 'htpp://192.168.0.20:631',
    'scan' => 'http://192.168.0.20/php-scanner'
);

$values = array();

// Getting System Values.
$uname = split(" ", exec("uname -a"), 4);

$values['system'] = array();
$values['system']['current_time'] = exec("date +'%d %b %Y %T %Z'");
$values['system']['frequency'] = exec("cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq") / 1000;
$values['system']['processor'] = str_replace("-compatible processor", "", explode(": ", exec("cat /proc/cpuinfo | grep Processor"))[1]);
$values['system']['cpu_temperature'] = round(exec("cat /sys/class/thermal/thermal_zone0/temp ") / 1000, 1);
$values['system']['system'] = $uname[0];
$values['system']['kernel'] = $uname[2];
$values['system']['host'] = exec('hostname -f');

// Load averages
$loadavg = explode(" ", exec("cat /proc/loadavg"));
$values['system']['load'] = $loadavg[2];

//Uptime
$uptime_array = explode(" ", exec("cat /proc/uptime"));
$seconds = round($uptime_array[0], 0);
$minutes = $seconds / 60;
$hours = $minutes / 60;
$days = floor($hours / 24);
$hours = sprintf('%02d', floor($hours - ($days * 24)));
$minutes = sprintf('%02d', floor($minutes - ($days * 24 * 60) - ($hours * 60)));
if ($days == 0) {
  $values['system']['uptime'] = $hours . ":" .  $minutes . " (hh:mm)";
} elseif($days == 1) {
  $values['system']['uptime'] = $days . " day, " .  $hours . ":" .  $minutes . " (hh:mm)";
} else {
  $values['system']['uptime'] = $days . " days, " .  $hours . ":" .  $minutes . " (hh:mm)";
}

//Memory Utilisation
$values['memory'] = array();
$meminfo = file("/proc/meminfo");
for ($i = 0; $i < count($meminfo); $i++) {
  list($item, $data) = split(":", $meminfo[$i], 2);
  $item = trim(chop($item));
  $data = intval(preg_replace("/[^0-9]/", "", trim(chop($data)))); //Remove non numeric characters
  switch($item) {
    case "MemTotal": $values['memory']['total'] = $data; break;
    case "MemFree":  $values['memory']['free'] = $data; break;
    case "SwapTotal":  $values['memory']['total_swap'] = $data; break;
    case "SwapFree":  $values['memory']['free_swap'] = $data; break;
    case "Buffers":  $values['memory']['buffer'] = $data; break;
    case "Cached":  $values['memory']['cache'] = $data; break;
    default: break;
  }
}

//Disk space check, with sizes reported in kB.
$values['hdd'] = array();
exec("df -T -l -BKB -x tmpfs -x devtmpfs -x rootfs", $diskfree);
for ($count = 1; $count < sizeof($diskfree); $count ++) {
  $diskinfo = split(" +", $diskfree[$count]);
  $values['hdd'][] = array(
    'drive' => $diskinfo[0],
    'type' => $diskinfo[1],
    'size' => $diskinfo[2],
    'used' => $diskinfo[3],
    'avail' => $diskinfo[4],
    'percent' => $diskinfo[5],
    'mount' => $diskinfo[6]
  );
}

// Rendering Page.
?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Raspberry Pi System Information</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Raspberry Pi Print Server Information">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>Raspberry Pi Server Info</title>
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="//fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="//code.getmdl.io/1.1.3/material.cyan-light_blue.min.css">
    <link rel="stylesheet" href="//getmdl.io/templates/dashboard/styles.css">
    <style>
      #view-source {
        position: fixed;
        display: block;
        right: 0;
        bottom: 0;
        margin-right: 40px;
        margin-bottom: 40px;
        z-index: 900;
      }
    </style>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  </head>
  <body>
    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">
      <header class="mdl-layout__header mdl-color--grey-100 mdl-color-text--grey-600">
        <div class="mdl-layout__header-row">
          <span class="mdl-layout-title">Raspberry Pi Print Server</span>
          <div class="mdl-layout-spacer"></div>
          <button class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon" id="hdrbtn">
            <i class="material-icons">more_vert</i>
          </button>
          <ul class="mdl-menu mdl-js-menu mdl-js-ripple-effect mdl-menu--bottom-right" for="hdrbtn">
            <li class="mdl-menu__item">
              <a href="https://github.com/whizzzkid/RPi-System-Info-Script">About</a>
            </li>
            <li class="mdl-menu__item">
              <a href="http://nishantarora.in">Contact</a>
            </li>
          </ul>
        </div>
      </header>
      <div class="mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">
        <nav class="mdl-navigation mdl-color--blue-grey-800">
          <a class="mdl-navigation__link" href="<?php echo $server['base']; ?>">
            <i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">home</i>Home</a>
          <a class="mdl-navigation__link" href="<?php echo $server['base']; ?>">
            <i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">print</i>Printer</a>
          <a class="mdl-navigation__link" href="<?php echo $server['scan']; ?>">
            <i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">scanner</i>Scanner</a>
          <div class="mdl-layout-spacer"></div>
          <a class="mdl-navigation__link" href="https://github.com/whizzzkid/RPi-System-Info-Script"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">help_outline</i><span class="visuallyhidden">Help</span></a>
        </nav>
      </div>
      <main class="mdl-layout__content mdl-color--grey-100">
        <div class="mdl-grid">
          <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--12-col mdl-grid" id="gauges">
          </div>
          <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--6-col mdl-grid">
            <h3>System Properties</h3>
            <table class="mdl-data-table mdl-js-data-table" style="width:100%;">
              <tbody>
                <tr>
                  <td class="mdl-data-table__cell--non-numeric">Hostname</td>
                  <td id="host"></td>
                </tr>
                <tr>
                  <td class="mdl-data-table__cell--non-numeric">System Time</td>
                  <td id="current_time"></td>
                </tr>
                <tr>
                  <td class="mdl-data-table__cell--non-numeric">Base</td>
                  <td id="system"></td>
                </tr>
                <tr>
                  <td class="mdl-data-table__cell--non-numeric">Kernel</td>
                  <td id="kernel"></td>
                </tr>
                <tr>
                  <td class="mdl-data-table__cell--non-numeric">CPU</td>
                  <td id="processor"></td>
                </tr>
                <tr>
                  <td class="mdl-data-table__cell--non-numeric">CPU Frequency</td>
                  <td id="frequency"></td>
                </tr>
                <tr>
                  <td class="mdl-data-table__cell--non-numeric">CPU Temprature</td>
                  <td id="cpu_temperature"></td>
                </tr>
                <tr>
                  <td class="mdl-data-table__cell--non-numeric">Uptime</td>
                  <td id="uptime"></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--6-col mdl-grid">
            <h3>Mounted Drives</h3>
            <ul class="mdl-list" id="drives" style="width:100%;"></ul>
          </div>
        </div>
      </main>
    </div>
    <script src="https://code.getmdl.io/1.1.3/material.min.js"></script>
    <script type="text/javascript">
      // App Namespace.
      var RPiServerApp = {};

      // Place holder for info from server.
      RPiServerApp.info = JSON.parse('<?php echo json_encode($values); ?>');

      // Updates system values.
      RPiServerApp.updateValues = function() {
        for (var i in this.info.system) {
          this.updateText(i, this.info.system[i]);
        }
      };

      // Draw chart callback.
      RPiServerApp.drawChart = function () {
        var gaugeData = google.visualization.arrayToDataTable([
          ['Label', 'Value'],
          ['Memory', this.convertToPercent(
              this.info.memory.total - this.info.memory.free,
              this.info.memory.total)],
          ['CPU Load', this.convertToPercent(this.info.system.load, 1)],
          ['Swap', this.convertToPercent(
              this.info.memory.total_swap - this.info.memory.free_swap,
              this.info.memory.total_swap)],
          ['Cache', this.convertToPercent(
              this.info.memory.total - this.info.memory.cache,
              this.info.memory.total)],
          ['Buffer', this.convertToPercent(
              this.info.memory.total - this.info.memory.buffer,
              this.info.memory.total)],
        ]);

        var gaugeOptions = {
          height: 250,
          redFrom: 85, redTo: 100,
          yellowFrom:70, yellowTo: 85,
          minorTicks: 5
        };

        var gauges = new google.visualization.Gauge(
            document.getElementById('gauges'));
        gauges.draw(gaugeData, gaugeOptions);
      };

      // Convert values to GB from KB.
      RPiServerApp.convertToGB = function(size) {
        var inGB = (parseFloat(size.replace('/[^\d]/g', '')))/(1024*1024)
        return Math.round(inGB) + 'GB';
      };

      // Generates drive info snippet.
      RPiServerApp.generateDriveInfoSnippet = function(drive) {
        return 'Type: ' + drive.type + ' | ' +
            'Size: ' + this.convertToGB(drive.size) + ' | ' +
            'Used: ' + drive.percent;
      };

      // Adds drive info.
      RPiServerApp.addDriveInfo = function() {
        var liElemTemplate = '<li class="mdl-list__item mdl-list__item--three-line"><span class="mdl-list__item-primary-content"><i class="material-icons">storage</i><span>#drive#</span><span class="mdl-list__item-text-body">#info#</span></span></li>';

        var drivesContainer = document.getElementById('drives');

        for (var i in this.info.hdd) {
          var newDrive = liElemTemplate.replace(
              '#drive#', this.info.hdd[i].drive).replace(
              '#info#', this.generateDriveInfoSnippet(
                  this.info.hdd[i]));
          drivesContainer.innerHTML += newDrive;
        }
      };

      // Inits.
      RPiServerApp.init = function() {
        google.charts.load('current', {'packages':['gauge', 'corechart']});
        google.charts.setOnLoadCallback(
            RPiServerApp.drawChart.bind(this));
        this.updateValues();
        this.addDriveInfo();
      };

      /**
       * Updates text.
       * @param {string} objectId
       * @param {string} text
       */
      RPiServerApp.updateText = function (objectId, text) {
        try {
          document.getElementById(objectId).textContent = text;
        } catch (e) {
          console.log(e);
          console.log(objectId);
          console.log(text);
        }
      };


      /**
       * Convert to percent
       * @param {string} some_value
       * @param {string} percent_of
       * @return {float} percent.
       */
      RPiServerApp.convertToPercent = function(some_value, percent_of) {
          return Math.round(
              ((parseFloat(some_value)*100)/parseFloat(percent_of)));
      };

      // Running App.
      RPiServerApp.init();
    </script>
  </body>
</html>