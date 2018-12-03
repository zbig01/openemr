<?php
/**
 * transactions.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/transactions.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;
?>
<html>
<head>
    <title><?php echo xlt('Patient Transactions');?></title>
    <?php Header::setupHeader('common'); ?>

<script type="text/javascript">
    // Called by the deleteme.php window on a successful delete.
    function imdeleted() {
        top.restoreSession();
        location.href = '../../patient_file/transaction/transactions.php';
    }
    // Process click on Delete button.
    function deleteme(transactionId) {
        top.restoreSession();
        dlgopen('../deleter.php?transaction=' + encodeURIComponent(transactionId) + '&csrf_token_form=' + <?php echo js_url(collectCsrfToken()); ?>, '_blank', 500, 450);
        return false;
    }
<?php require_once("$include_root/patient_file/erx_patient_portal_js.php"); // jQuery for popups for eRx and patient portal ?>
</script>
<?php
//BEGIN - edit as needed - variables needed to construct the array $arrHeading - needed to output the Heading text with icons and Help modal code
$name = " - " . getPatientNameFirstLast($pid); //un-comment to include fname lname, use ONLY on relevant pages :))
$heading_title = xlt('Patient Transactions') . $name; // Minimum needed is the heading title
//3 optional icons - for ease of use and troubleshooting first create the variables and then use them to populate the arrays:)
$arrExpandable = array();//2 elements - int|bool $current_state, int|bool $expandable . $current_state = collectAndOrganizeExpandSetting($arr_files_php).
                        //$arr_files_php is also an indexed array, current file name first, linked file names thereafter, all need _xpd suffix, names to be unique
$arrAction = array();//3 elements - string $action (conceal, reveal, search, reset, link and back), string $action_title - leave blank for actions
                    // (conceal, reveal and search), string $action_href - needed for actions (reset, link and back)
$show_help_icon = 1;
$help_file_name = 'transactions_dashboard_help.php';
$arrHelp = array($show_help_icon, $help_file_name );// 2 elements - int|bool $show_help_icon, string $help_file_name - file needs to exist in Documentation/help_files directory
//END - edit as needed
//DO NOT EDIT BELOW
$arrHeading = array($heading_title, $arrExpandable, $arrAction, $arrHelp); // minimum $heading_title, others can be an empty arrays - displays only heading
$oemr_ui = new OemrUI($arrHeading);
$arr_display_heading = $oemr_ui->pageHeading(); // returns an indexed array containing heading string with selected icons and container string value
$heading = $arr_display_heading[0];
$container = $arr_display_heading[1];// if you want page to always open as full-width override the default returned value with $container = 'container-fluid'
echo "<script>\r\n";
require_once("$srcdir/js/oeUI/universalTooltip.js");
echo "\r\n</script>\r\n";
?>
</head>

<body class="body_top">
    <div class="container">
        <!--<h1><?php echo xlt('Patient Transactions');?></h1>-->
        <?php $header_title = xl('Patient Transactions for');?>
        <div class="row">
            <div class="col-sm-12">
                <?php require_once("$include_root/patient_file/summary/dashboard_header.php");?>
            </div>
        </div>
        <div class="row" >
            <div class="col-sm-12">
                <?php
                $list_id = "transactions"; // to indicate nav item is active, count and give correct id
                // Collect the patient menu then build it
                $menuPatient = new PatientMenuRole();
                $menuPatient->displayHorizNavBarMenu();
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="btn-group">
                    <!--<a href="../summary/demographics.php" class="btn btn-default btn-back" onclick="top.restoreSession()">
                        <?php echo xlt('Back to Patient'); ?></a>-->
                    <a href="add_transaction.php" class="btn btn-default btn-add" onclick="top.restoreSession()">
                        <?php echo xlt('Create New Transaction'); ?></a>
                    <a href="print_referral.php" class="btn btn-default btn-print" onclick="top.restoreSession()">
                        <?php echo xlt('View/Print Blank Referral Form'); ?></a>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-sm-12 text">

                <?php
                if ($result = getTransByPid($pid)) {
                ?>

                    <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th><?php echo xlt('Type'); ?></th>
                            <th><?php echo xlt('Date'); ?></th>
                            <th><?php echo xlt('User'); ?></th>
                            <th><?php echo xlt('Details'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $item) {
                            if (!isset($item['body'])) {
                                $item['body'] = '';
                            }

                            if (getdate() == strtotime($item['date'])) {
                                $date = "Today, " . date('D F ds', strtotime($item['date']));
                            } else {
                                $date = date('D F ds', strtotime($item['date']));
                            }

                            $date = oeFormatShortDate($item['refer_date']);
                            $id = $item['id'];
                            $edit = xl('View/Edit');
                            $view = xl('Print'); //actually prints or displays ready to print
                            $delete = xl('Delete');
                            $title = xl($item['title']);
                            ?>
                            <tr>
                                <td>
                                    <div class="btn-group oe-pull-toward">
                                        <a href='add_transaction.php?transid=<?php echo attr_url($id); ?>&title=<?php echo attr_url($title); ?>&inmode=edit'
                                            onclick='top.restoreSession()'
                                            class='btn btn-default btn-edit'>
                                            <?php echo text($edit); ?>
                                        </a>
                                        <?php if (acl_check('admin', 'super')) { ?>
                                            <a href='#'
                                                onclick='deleteme(<?php echo attr_js($id); ?>)'
                                                class='btn btn-default btn-delete'>
                                                <?php echo text($delete); ?>
                                            </a>
                                        <?php } ?>
                                        <?php if ($item['title'] == 'LBTref') { ?>
                                            <a href='print_referral.php?transid=<?php echo attr_url($id); ?>' onclick='top.restoreSession();'
                                                class='btn btn-print btn-default'>
                                                <?php echo text($view); ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td><?php echo getLayoutTitle('Transactions', $item['title']); ?></td>
                                <td><?php echo text($date); ?></td>
                                <td><?php echo text($item['user']); ?></td>
                                <td><?php echo text($item['body']); ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                    </table>

                <?php
                } else {
                ?>
                <span class="text"><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo xlt('There are no transactions on file for this patient.'); ?></span>
                <?php
                }
                ?>
            </div>
        </div>
    </div><!--end of container div-->
    <?php $oemr_ui->helpFileModal(); // help file name passed in $arrHeading [3][1] ?>
    <script>
        var listId = '#' + <?php echo js_escape($list_id); ?>;
        $(document).ready(function(){
            $(listId).addClass("active");
        });
    </script>
</body>
</html>
