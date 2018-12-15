<?php
/**
 * patient reminders gui
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ensofttek, LLC
 * @copyright Copyright (c) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Ensofttek, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/reminders.php");
require_once("$srcdir/clinical_rules.php");
require_once "$srcdir/report_database.inc";

use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;
?>

<html>
<head>

    <title><?php echo xlt("Patient Reminders"); ?></title>

    <?php Header::setupHeader('common'); ?>

    <style>
        a.arrowhead, a:hover.arrowhead, a:visited.arrowhead{
        color: black;
    }
    </style>


<?php
$patient_id = ($_GET['patient_id']) ? $_GET['patient_id'] : "";
$mode = ($_GET['mode']) ? $_GET['mode'] : "simple";
$sortby = $_GET['sortby'];
$sortorder = $_GET['sortorder'];
$begin = $_GET['begin'];

if (!empty($patient_id)) {
    //Only update one patient
    $update_rem_log = update_reminders('', $patient_id);
}

if ($mode == "simple") {
    // Collect the rules for the per patient rules selection tab
    $rules_default = resolve_rules_sql('', '0', true);
}

?>

<script language="javascript">
    // This is for callback by the find-patient popup.
    function setpatient(pid, lname, fname, dob) {
        var f = document.forms[0];
        f.form_patient.value = lname + ', ' + fname;
        f.patient_id.value = pid;
    }

    // This invokes the find-patient popup.
    function sel_patient() {
        dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 500, 400);
    }
</script>
<?php
$oemr_ui = new OemrUI(); //to display heading with selected icons and help modal if needed

//begin - edit as needed
if ($mode == "simple") {
        $arrOeUiSettings = array(
            'heading_title' => xl('Patient Reminders'),
            'include_patient_name' => true,
            'expandable' => true,
            'expandable_files' => array('patient_reminders_patient_xpd'),//all file names need suffix _xpd
            'action' => "back",//conceal, reveal, search, reset, link or back
            'action_title' => "",
            'action_href' => "../summary/demographics.php",//only for actions - reset, link or back
            'show_help_icon' => false,
            'help_file_name' => ""
        );
} else {
    $arrOeUiSettings = array(
            'heading_title' => xl('Patient Reminders'),
            'include_patient_name' => false,
            'expandable' => true,
            'expandable_files' => array('patient_reminders_xpd'),//all file names need suffix _xpd
            'action' => "conceal",//conceal, reveal, search, reset, link or back
            'action_title' => "",
            'action_href' => "",//only for actions - reset, link or back
            'show_help_icon' => false,
            'help_file_name' => ""
        );
}
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>
<body class='body_top'>
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-header">
                    <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php
                // This is for sorting the records.
                $sort = array("category, item", "lname, fname", "due_status", "date_created", "hipaa_allowemail", "hipaa_allowsms", "date_sent", "voice_status", "email_status", "sms_status", "mail_status");
                if ($sortby == "") {
                    $sortby = $sort[0];
                }

                if ($sortorder == "") {
                    $sortorder = "asc";
                }
                for ($i = 0; $i < count($sort); $i++) {
                    $sortlink[$i] = "<a class='arrowhead' href=\"patient_reminders.php?patient_id=" . attr_url($patient_id) ."&mode=" . attr_url($mode) . "&sortby=" . attr_url($sort[$i]) . "&sortorder=asc\" onclick=\"top.restoreSession()\" title ='" . xla('Sort Up') . "'>" .
                    "<i class='fa fa-sort-desc fa-lg' aria-hidden='true'></i></a>";
                }
                for ($i = 0; $i < count($sort); $i++) {
                    if ($sortby == $sort[$i]) {
                        switch ($sortorder) {
                            case "asc":
                                $sortlink[$i] = "<a class='arrowhead' href=\"patient_reminders.php?patient_id=" . attr_url($patient_id) . "&mode=" . attr_url($mode) . "&sortby=" . attr_url($sortby) . "&sortorder=desc\" onclick=\"top.restoreSession()\" title ='" . xla('Sort Up') . "'>" .
                                          "<i class='fa fa-sort-asc fa-lg' aria-hidden='true'></i></a>";
                                break;
                            case "desc":
                                $sortlink[$i] = "<a class='arrowhead' href=\"patient_reminders.php?patient_id=" . attr_url($patient_id) . "&mode=" . attr_url($mode) . "&sortby=" . attr_url($sortby) . "&sortorder=asc\" onclick=\"top.restoreSession()\" title ='" . xla('Sort Down') . "'>" .
                                          "<i class='fa fa-sort-desc fa-lg' aria-hidden='true'></i></a>";
                                break;
                        } break;
                    }
                }

                // This is for managing page numbering and display beneath the Patient Reminders table.
                $listnumber = 25;
                $sqlBindArray = array();
                if (!empty($patient_id)) {
                    $add_sql = "AND a.pid=? ";
                    array_push($sqlBindArray, $patient_id);
                }

                $sql = "SELECT a.id, a.due_status, a.category, a.item, a.date_created, a.date_sent, b.fname, b.lname " .
                  "FROM `patient_reminders` as a, `patient_data` as b " .
                  "WHERE a.active='1' AND a.pid=b.pid ".$add_sql;
                $result = sqlStatement($sql, $sqlBindArray);
                if (sqlNumRows($result) != 0) {
                    $total = sqlNumRows($result);
                } else {
                    $total = 0;
                }

                if ($begin == "" or $begin == 0) {
                    $begin = 0;
                }

                $prev = $begin - $listnumber;
                $next = $begin + $listnumber;
                $start = $begin + 1;
                $end = $listnumber + $start - 1;
                if ($end >= $total) {
                    $end = $total;
                }

                if ($end < $start) {
                    $start = 0;
                }

                if ($prev >= 0) {
                    $prevlink = "<a href=\"patient_reminders.php?patient_id=" . attr_url($patient_id) . "&mode=" . attr_url($mode) . "&sortby=" . attr_url($sortby) . "&sortorder=" . attr_url($sortorder) . "&begin=" . attr_url($prev) . "\" onclick=\"top.restoreSession()\"><<</a>";
                } else {
                    $prevlink = "<<";
                }

                if ($next < $total) {
                    $nextlink = "<a href=\"patient_reminders.php?patient_id=" . attr_url($patient_id) . "&mode=" . attr_url($mode) . "&sortby=" . attr_url($sortby) . "&sortorder=" . attr_url($sortorder) . "&begin=" . attr_url($next) . "\" onclick=\"top.restoreSession()\">>></a>";
                } else {
                    $nextlink = ">>";
                }
                ?>

                <?php if ($mode == "simple") { // show the per patient rule setting option ?>
                  <ul class="tabNav">
                    <li class='current'><a href='#'><?php echo xlt('Main'); ?></a></li>
                    <li ><a href='#' onclick='top.restoreSession()'><?php echo xlt('Rules'); ?></a></li>
                  </ul>
                  <div class="tabContainer">
                  <div class="tab current" style="height:auto;width:97%;">
                <?php } ?>

                <form method='post' name='theform' id='theform'>

                <div id='report_parameters' class='hideaway'>
                  <table>
                    <tr>
                      <td width='410px'>
                        <div style='float:left'>
                          <table class='text'>
                            <tr>
                              <td class='label_custom'>
                                <?php echo " "; ?>
                              </td>
                            </tr>
                          </table>
                        </div>
                      </td>
                      <td align='left' valign='middle' height="100%">
                        <table style='border-left:1px solid; width:100%; height:100%' >
                          <tr>
                            <td>
                              <div style='margin-left:15px'>
                                <?php if ($mode == "admin") { ?>
                                 <a id='process_button' href='#' class='css_button' onclick='return ReminderBatch("process")'>
                                   <span><?php echo xlt('Process Reminders'); ?></span>
                                 </a>
                                 <a id='process_send_button' href='#' class='css_button' onclick='return ReminderBatch("process_send")'>
                                   <span><?php echo xlt('Process and Send Reminders'); ?></span>
                                 </a>
                                 <span id='status_span'></span>
                                 <div id='processing' style='margin:10px;display:none;'><img src='../../pic/ajax-loader.gif'/></div>
                                <?php } else { ?>
                                <a href='patient_reminders.php?patient_id=<?php echo attr_url($patient_id); ?>&mode=<?php echo attr_url($mode); ?>' class='css_button' onclick='top.restoreSession()'>
                                  <span><?php echo xlt('Refresh'); ?></span>
                                </a>
                                <?php } ?>
                              </div>
                            </td>
                            <td align=right class='text'><?php echo $prevlink . " " . text($end) . " of " . text($total) . " " . $nextlink; ?></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </div>

                <div id='report_results'>
                    <table>
                      <thead>
                        <th><?php echo xlt('Item') . " " . $sortlink[0]; ?></th>
                        <th><?php echo xlt('Patient') . " " . $sortlink[1]; ?></th>
                        <th><?php echo xlt('Due Status') . " " . $sortlink[2]; ?></th>
                        <th><?php echo xlt('Date Created') . " " . $sortlink[3]; ?></th>
                        <th><?php echo xlt('Email Auth') . " " . $sortlink[4]; ?></th>
                        <th><?php echo xlt('SMS Auth') . " " . $sortlink[5]; ?></th>
                        <th><?php echo xlt('Date Sent') . " " . $sortlink[6]; ?></th>
                        <th><?php echo xlt('Voice Sent') . " " . $sortlink[7]; ?></th>
                        <th><?php echo xlt('Email Sent') . " " . $sortlink[8]; ?></th>
                        <th><?php echo xlt('SMS Sent') . " " . $sortlink[9]; ?></th>
                        <th><?php echo xlt('Mail Sent') . " " . $sortlink[10]; ?></th>
                      </thead>
                      <tbody>
                <?php

                    //Escape sort by parameter
                    $escapedsortby = explode(',', $sortby);
                foreach ($escapedsortby as $key => $columnName) {
                    $escapedsortby[$key] = escape_sql_column_name(trim($columnName), array('patient_reminders','patient_data'));
                }
                    $escapedsortby = implode(', ', $escapedsortby);

                    $sql = "SELECT a.id, a.due_status, a.category, a.item, a.date_created, a.date_sent, a.voice_status, " .
                                "a.sms_status, a.email_status, a.mail_status, b.fname, b.lname, b.hipaa_allowemail, b.hipaa_allowsms " .
                    "FROM `patient_reminders` as a, `patient_data` as b " .
                    "WHERE a.active='1' AND a.pid=b.pid " . $add_sql .
                    "ORDER BY " . $escapedsortby . " " .
                      escape_sort_order($sortorder) . " " .
                    "LIMIT " . escape_limit($begin) . ", " .
                      escape_limit($listnumber);
                    $result = sqlStatement($sql, $sqlBindArray);
                while ($myrow = sqlFetchArray($result)) { ?>
                        <tr>
                          <td><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_action_category'), $myrow['category']) . " : " .
                            generate_display_field(array('data_type'=>'1','list_id'=>'rule_action'), $myrow['item']); ?></td>
                          <td><?php echo text($myrow['lname'].", ".$myrow['fname']); ?></td>
                          <td><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_reminder_due_opt'), $myrow['due_status']); ?></td>
                          <td><?php echo ($myrow['date_created']) ? text($myrow['date_created']) : " "; ?></td>
                          <td><?php echo ($myrow['hipaa_allowemail']=='YES') ? xlt("YES") : xlt("NO"); ?></td>
                          <td><?php echo ($myrow['hipaa_allowsms']=='YES') ? xlt("YES") : xlt("NO"); ?></td>
                          <td><?php echo ($myrow['date_sent']) ? text($myrow['date_sent']) : xlt("Not Sent Yet"); ?></td>
                          <td><?php echo ($myrow['voice_status']==1) ? xlt("YES") : xlt("NO"); ?></td>
                          <td><?php echo ($myrow['email_status']==1) ? xlt("YES") : xlt("NO"); ?></td>
                          <td><?php echo ($myrow['sms_status']==1) ? xlt("YES") : xlt("NO"); ?></td>
                          <td><?php echo ($myrow['mail_status']==1) ? xlt("YES") : xlt("NO"); ?></td>
                        </tr>
                <?php } ?>
                      </tbody>
                    </table>
                </div>

                <?php if ($mode == "simple") { // show the per patient rule setting option ?>
                  </div>
                  <div class="tab" style="height:auto;width:97%;">
                    <div id='report_results'>
                      <table>
                        <tr>
                          <th rowspan="2"><?php echo xlt('Rule'); ?></th>
                          <th colspan="2"><?php echo xlt('Patient Reminder'); ?></th>
                        </tr>
                        <tr>
                          <th><?php echo xlt('Patient Setting'); ?></th>
                          <th style="left-margin:1em;"><?php echo xlt('Practice Default Setting'); ?></th>
                        </tr>
                        <?php foreach ($rules_default as $rule) { ?>
                          <tr>
                            <td style="border-right:1px solid black;"><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'clinical_rules'), $rule['id']); ?></td>
                            <td align="center">
                                <?php
                                $patient_rule = collect_rule($rule['id'], $patient_id);
                              // Set the patient specific setting for gui
                                if (empty($patient_rule)) {
                                    $select = "default";
                                } else {
                                    if ($patient_rule['patient_reminder_flag'] == "1") {
                                        $select = "on";
                                    } elseif ($patient_rule['patient_reminder_flag'] == "0") {
                                        $select = "off";
                                    } else { // $patient_rule['patient_reminder_flag'] == NULL
                                        $select = "default";
                                    }
                                } ?>
                              <select class="patient_reminder" name="<?php echo attr($rule['id']); ?>">
                                <option value="default" <?php echo ($select == "default") ? "selected" : ""; ?>><?php echo xlt('Default'); ?></option>
                                <option value="on" <?php echo ($select == "on") ? "selected" : ""; ?>><?php echo xlt('On'); ?></option>
                                <option value="off" <?php echo ($select == "off") ? "selected" : ""; ?>><?php echo xlt('Off'); ?></option>
                              </select>
                            </td>
                            <td align="center" style="border-right:1px solid black;">
                                <?php
                                if ($rule['patient_reminder_flag'] == "1") {
                                    echo xlt('On');
                                } else {
                                    echo xlt('Off');
                                }
                                ?>
                            </td>
                          </tr>
                        <?php } ?>
                      </table>
                    </div>
                  </div>
                  </div>
                <?php } ?>

                <input type='hidden' name='form_new_report_id' id='form_new_report_id' value=''/>
                </form>
            </div>
        </div>
    </div><!--end of container div-->
<?php $oemr_ui->oeBelowContainerDiv();?>
<script language="javascript">

$(document).ready(function(){

  tabbify();

  $(".patient_reminder").change(function() {
    top.restoreSession();
    $.post( "../../../library/ajax/rule_setting.php", {
      rule: this.name,
      type: 'patient_reminder',
      setting: this.value,
      patient_id: <?php echo js_escape($patient_id); ?>,
      csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
    });
  });

});

 // Show a template popup of patient reminders batch sending tool.
 function ReminderBatch(processType) {
   //Hide the buttons and show the processing animation
   $("#process_button").hide();
   $("#process_send_button").hide();
   $("#processing").show();

   top.restoreSession();
   $.get("../../../library/ajax/collect_new_report_id.php",
     { csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?> },
     function(data){
       // Set the report id in page form
       $("#form_new_report_id").attr("value",data);

       // Start collection status checks
       collectStatus($("#form_new_report_id").val());

       // Run the report
       top.restoreSession();
       $.post("../../../library/ajax/execute_pat_reminder.php",
         {process_type: processType,
          execute_report_id: $("#form_new_report_id").val(),
          csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
         });
   });

   return false;
 }

 function collectStatus(report_id) {
   // Collect the status string via an ajax request and place in DOM at timed intervals
   top.restoreSession();
   // Do not send the skip_timeout_reset parameter, so don't close window before report is done.
   $.post("../../../library/ajax/status_report.php",
     {
       status_report_id: report_id,
       csrf_token_form: <?php echo js_escape(collectCsrfToken()); ?>
     },
     function(data){
       if (data == "PENDING") {
         // Place the pending string in the DOM
         $('#status_span').replaceWith("<span id='status_span'>" + <?php echo xlj("Preparing To Run Report"); ?> + "</span>");
       }
       else if (data == "COMPLETE") {
         // Go into the results page
         top.restoreSession();
         link_report = "patient_reminders.php?mode=admin&patient_id=";
         window.open(link_report,'_self',false);
       }
       else {
         // Place the string in the DOM
         $('#status_span').replaceWith("<span id='status_span'>"+data+"</span>");
       }
   });
   // run status check every 10 seconds
   var repeater = setTimeout("collectStatus("+report_id+")", 10000);
 }

</script>
</body>
</html>
