<?php
// Copyright (C) 2010-2017 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
use OpenEMR\Core\Header;

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

$typeid = formData('typeid', 'R') + 0;
$parent = formData('parent', 'R') + 0;

$info_msg = "";

function QuotedOrNull($fld)
{
    $fld = formDataCore($fld, true);
    if ($fld) {
        return "'$fld'";
    }

    return "NULL";
}

function invalue($name)
{
    $fld = formData($name, "P", true);
    return "'$fld'";
}

function rbinput($name, $value, $desc, $colname)
{
    global $row;
    $ret  = "<input type='radio' name='$name' value='$value'";
    if ($row[$colname] == $value) {
        $ret .= " checked";
    }

    $ret .= " />$desc";
    return $ret;
}

function rbvalue($rbname)
{
    $tmp = $_POST[$rbname];
    if (! $tmp) {
        $tmp = '0';
    }

    return "'$tmp'";
}

function cbvalue($cbname)
{
    return empty($_POST[$cbname]) ? 0 : 1;
}

function recursiveDelete($typeid)
{
    $res = sqlStatement("SELECT procedure_type_id FROM " .
    "procedure_type WHERE parent = '$typeid'");
    while ($row = sqlFetchArray($res)) {
        recursiveDelete($row['procedure_type_id']);
    }

    sqlStatement("DELETE FROM procedure_type WHERE " .
    "procedure_type_id = '$typeid'");
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker']);?>
<script type="text/javascript"
    src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
<title><?php echo $typeid ? xlt('Edit') : xlt('Add New'); ?> <?php echo xlt('Order/Result Type'); ?></title>


<style>
td {
    font-size: 10pt;
}

.inputtext {
    padding-left: 2px;
    padding-right: 2px;
}

.button {
    font-family: sans-serif;
    font-size: 9pt;
    font-weight: bold;
}

.ordonly {
    
}

.resonly {
    
}
.label-div > a {
    display:none;
}
.label-div:hover > a {
   display:inline-block; 
}
div[id$="_info"] {
    background: #F7FAB3;
    padding: 20px;
    margin: 10px 15px 0px 15px;
}
div[id$="_info"] > a {
    margin-left:10px;
}
</style>

<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-2-2/index.js"></script>

<script language="JavaScript">

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// The name of the form field for find-code popup results.
var rcvarname;

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
function set_related(codetype, code, selector, codedesc) {
    var f = document.forms[0];
    var s = f[rcvarname].value;
    if (code) {
        if (s.length > 0) s += ';';
        s += codetype + ':' + code;
    } else {
        s = '';
    }
    f[rcvarname].value = s;
}

// This is for callback by the find-code popup.
// Returns the array of currently selected codes with each element in codetype:code format.
function get_related() {
 return document.forms[0][rcvarname].value.split(';');
}

// This is for callback by the find-code popup.
// Deletes the specified codetype:code from the currently selected list.
function del_related(s) {
 my_del_related(s, document.forms[0][rcvarname], false);
}

// This invokes the find-code popup.
function sel_related(varname) {
 if (typeof varname == 'undefined') varname = 'form_related_code';
 rcvarname = varname;
 dlgopen('../patient_file/encounter/find_code_dynamic.php', '_blank', 900, 600);
}

// Show or hide sections depending on procedure type.
function proc_type_changed() {
    var f = document.forms[0];
    var pt = f.form_procedure_type;
    var ix = pt.selectedIndex;
    if (ix < 0) ix = 0;
    var ptval = pt.options[ix].value;
    var ptpfx = ptval.substring(0, 3);
    $('.ordonly').hide();
    $('.resonly').hide();
    if (ptpfx == 'ord') $('.ordonly').show();
    if (ptpfx == 'res' || ptpfx == 'rec') $('.resonly').show();
    if (ptpfx == 'grp') {
        $('#form_legend').html(
            "<?php echo xlt('Enter Details for Group'); ?>");
    } else if (ptpfx == 'ord') {
        $('#form_legend').html(
            "<?php echo xlt('Enter Details for Individual Procedures'); ?>"
        );
    } else if (ptpfx == 'res') {
        $('#form_legend').html(
            "<?php echo xlt('Enter Details for Discrete Results'); ?>");
    } else if (ptpfx == 'rec') {
        $('#form_legend').html(
            "<?php echo xlt('Enter Details for Recommendation'); ?>");
    }
}
    $(document).ready(function() {
        proc_type_changed();
    });

</script>

</head>

    <body class="body_top">
        <div class= "container">
        <?php
        // If we are saving, then save and close the window.
        //
        if ($_POST['form_save']) {
            $sets =
    		"name = "           . invalue('form_name')           . ", " .
		    "lab_id = "         . invalue('form_lab_id')         . ", " .
		    "procedure_code = " . invalue('form_procedure_code') . ", " .
		    "procedure_type = " . invalue('form_procedure_type') . ", " .
		    "body_site = "      . invalue('form_body_site')      . ", " .
		    "specimen = "       . invalue('form_specimen')       . ", " .
		    "route_admin = "    . invalue('form_route_admin')    . ", " .
		    "laterality = "     . invalue('form_laterality')     . ", " .
		    "description = "    . invalue('form_description')    . ", " .
		    "units = "          . invalue('form_units')          . ", " .
		    "`range` = "        . invalue('form_range')          . ", " .
		    "standard_code = "  . invalue('form_standard_code')  . ", " .
		    "related_code = "   . invalue('form_related_code')   . ", " .
		    "seq = "            . invalue('form_seq');
            
            if ($typeid) {
                sqlStatement("UPDATE procedure_type SET $sets WHERE procedure_type_id = '$typeid'");
                // Get parent ID so we can refresh the tree view.
                $row = sqlQuery("SELECT parent FROM procedure_type WHERE " . "procedure_type_id = '$typeid'");
                $parent = $row['parent'];
            } else {
                $newid = sqlInsert("INSERT INTO procedure_type SET parent = '$parent', $sets");
                // $newid is not really used in this script
            }
        } else if ($_POST['form_delete']) {
            if ($typeid) {
                // Get parent ID so we can refresh the tree view after deleting.
                $row = sqlQuery("SELECT parent FROM procedure_type WHERE " .
				"procedure_type_id = '$typeid'");
                $parent = $row['parent'];
                recursiveDelete($typeid);
            }
        }

        if ($_POST['form_save'] || $_POST['form_delete']) {
            // Find out if this parent still has any children.
            $trow = sqlQuery("SELECT procedure_type_id FROM procedure_type WHERE parent = '$parent' LIMIT 1");
            $haskids = empty($trow['procedure_type_id']) ? 'false' : 'true';
            // Close this window and redisplay the updated list.
            echo "<script language='JavaScript'>\n";
            if ($info_msg) {
                echo " alert('$info_msg');\n";
            }
            
            echo " window.close();\n";
            echo " if (opener.refreshFamily) opener.refreshFamily($parent,$haskids);\n";
            echo "</script></body></html></div>\n";
            exit();
        }

        if ($typeid) {
            $row = sqlQuery("SELECT * FROM procedure_type WHERE procedure_type_id = '$typeid'");
        }
        ?>
        <div class="row">
            <form method='post' name='theform' class="form-horizontal"
                action='types_edit.php?typeid=<?php echo $typeid ?>&parent=<?php echo $parent ?>'>
                <!-- no restoreSession() on submit because session data are not relevant -->
                <fieldset>
                    <legend name="form_legend" id="form_legend"><?php echo xlt('Enter Details'); ?></legend>
                     <div class="forms col-xs-12">
                        <div class="col-sm-2 label-div">
                            <label class="control-label " for="form_procedure_type"><?php echo xlt('Procedure Tier'); ?>:</label> <a href="#procedure_type_info"  class="info-anchor"  data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <?php
                            echo generate_select_list('form_procedure_type', 'proc_type', 
                            $row['procedure_type'], xl('The type of this entity'), ' ', '', 'proc_type_changed()');
                            ?>
                        </div>
                        <div id="procedure_type_info" class="col-sm-8 collapse">
                            <a href="#procedure_type_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("In order to properly store and retrieve test results and place new orders, tests/orders have to be setup in 
                                a hierarchical manner, 4-tier setup for groups of tests and a 3-tier setup  for single tests.");?></p>
                            <p><?php echo xlt("Group - First tier - For a group of tests e.g. Annual PE");?></p>
                            <p><?php echo xlt("Group - Second tier - For a Test containing a collection of tests e.g. CBC, Renal Panel");?></p>
                            <p><?php echo xlt("Procedure Type - Third Tier - Individual Tests e.g. WBC, Hb, Hct, Na, K");?></p>
                            <p><?php echo xlt("Discrete Result - Fourth Tier - Default Units, Default Range");?></p>
                            <p><?php echo xlt("Recommendation - Optional");?></p>
                            <p><?php echo xlt("For a group of tests: Group> Group >Procedure Type > Discrete results");?></p>
                            <p><?php echo xlt("For single tests: Group >Procedure Type > Discrete results");?></p>
                            <p><?php echo xlt("As the fist step choose the tier and fill in the required details");?></p>
                            <p><?php echo xlt("For detailed instructions close the 'Enter Details' popup and click on the Help icon on the main form. ");?><i class="fa fa-question-circle" aria-hidden="true"></i></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_name"><?php echo xlt('Name'); ?>:</label><a href="#name_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <input type='text' name='form_name' id='form_name 'maxlength='63'
                                value='<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>'
                                title='<?php echo xlt('Your name for this category, procedure or result'); ?>'
                                 class='form-control'>
                        </div>
                        <div id="name_info" class="col-sm-8 collapse">
                            <a href="#name_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("Name for this Category, Procedure or Result");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_description"><?php echo xlt('Description'); ?>:</label><a href="#description_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <input type='text' name='form_description' id='form_description'
                                maxlength='255'
                                value='<?php echo htmlspecialchars($row['description'], ENT_QUOTES); ?>'
                                title='<?php echo xlt('Description of this procedure or result code'); ?>'
                                class='form-control'>
                        </div>
                        <div id="description_info" class="col-sm-8 collapse">
                            <a href="#description_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("A short description of this procedure or result code");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_seq"><?php echo xlt('Sequence'); ?>:</label><a href="#sequence_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <input type='text' name='form_seq' id=='form_seq' maxlength='11'
                                value='<?php echo $row['seq'] + 0; ?>'
                                title='<?php echo xla('Relative ordering of this entity'); ?>'
                                class='form-control'>
                        </div>
                        <div id="sequence_info" class="col-sm-8 collapse">
                            <a href="#sequence_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("The order in which the Category, Procedure or Result appears");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12 ordonly">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_lab_id"><?php echo xlt('Order From'); ?>:</label><a href="#order_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <select name='form_lab_id' id='form_lab_id' class='form-control'
                                title='<?php echo xla('The entity performing this procedure'); ?>'>
                                <?php
                                $ppres = sqlStatement("SELECT ppid, name FROM procedure_providers " . "ORDER BY name, ppid");
                                while ($pprow = sqlFetchArray($ppres)) {
                                    echo "<option value='" . attr($pprow['ppid']) . "'";
                                    if ($pprow['ppid'] == $row['lab_id']) {
                                        echo " selected";
                                    }
                                    
                                    echo ">" . text($pprow['name']) . "</option>";
                                }
                                ?>
                               </select>
                        </div>
                        <div id="order_info" class="col-sm-8 collapse">
                             <a href="#order_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("The entity performing this procedure");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12 ordonly resonly">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_procedure_code"><?php echo xlt('Identifying Code'); ?>:</label><a href="#procedure_code_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <input type='text' name='form_procedure_code' id='form_procedure_code'
                                maxlength='31'
                                value='<?php echo htmlspecialchars($row['procedure_code'], ENT_QUOTES); ?>'
                                title='<?php echo xla('The vendor-specific code identifying this procedure or result'); ?>'
                                class='form-control'>
                        </div>
                        <div id="procedure_code_info" class="col-sm-8 collapse">
                            <a href="#procedure_code_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("The vendor-specific code identifying this procedure or result. If no vendor enter any arbitrary unique number, preferably a 5 digit zero-padded e.g. 00211");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12 ordonly">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_standard_code"><?php echo xlt('Standard Code'); ?>:</label><a href="#standard_code_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <input type='text' name='form_standard_code' id='form_standard_code'
                                value='<?php echo attr($row['standard_code']); ?>'
                                title='<?php echo xla('Enter the LOINC code for this procedure'); ?>'
                                class='form-control'>
                        </div>
                        <div id="standard_code_info" class="col-sm-8 collapse">
                            <a href="#standard_code_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("Enter the Logical Observation Identifiers Names and Codes (LOINC) code for this procedure. LOINC is a database and universal standard for identifying medical laboratory observations.");?></p>
                            <p><?php echo xlt("This code is optional if only using manual lab data entry.");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12 ordonly">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_body_site"><?php echo xlt('Body Site'); ?>:</label><a href="#body_site_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <?php
                                generate_form_field(array(
                                    'data_type' => 1,
                                    'field_id' => 'body_site',
                                    'list_id' => 'proc_body_site',
                                    'description' => xl('Body site, if applicable')
                                ), $row['body_site']);
                                ?>
                        </div>
                        <div id="body_site_info" class="col-sm-8 collapse">
                            <a href="#body_site_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("Enter the relevant site if applicable.");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12 ordonly">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_specimen"><?php echo xlt('Specimen Type'); ?>:</label><a href="#specimen_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <?php
                                generate_form_field(array(
                                    'data_type' => 1,
                                    'field_id' => 'specimen',
                                    'list_id' => 'proc_specimen',
                                    'description' => xl('Specimen Type')
                                ), $row['specimen']);
                                ?>
                        </div>
                        <div id="specimen_info" class="col-sm-8 collapse">
                            <a href="#specimen_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("Enter the specimen type if applicable.");?></p>
                            <p><?php echo xlt("This code is optional, but is a good practise to do so.");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12 ordonly">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_route_admin"><?php echo xlt('Administer Via'); ?>:</label><a href="#administer_via_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <?php
                                generate_form_field(array(
                                    'data_type' => 1,
                                    'field_id' => 'route_admin',
                                    'list_id' => 'proc_route',
                                    'description' => xl('Route of administration, if applicable')
                                ), $row['route_admin']);
                                ?>
                        </div>
                        <div id="administer_via_info" class="col-sm-8 collapse">
                            <a href="#administer_via_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("Enter the specimen type if applicable.");?></p>
                            <p><?php echo xlt("This code is optional.");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12 ordonly">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_laterality"><?php echo xlt('Laterality'); ?>:</label><a href="#laterality_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <?php
                                generate_form_field(array(
                                    'data_type' => 1,
                                    'field_id' => 'laterality',
                                    'list_id' => 'proc_lat',
                                    'description' => xl('Laterality of this procedure, if applicable')
                                ), $row['laterality']);
                            ?>
                        </div>
                        <div id="laterality_info" class="col-sm-8 collapse">
                            <a href="#laterality_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("Enter the laterality of this procedure, if applicable.");?></p>
                            <p><?php echo xlt("This code is optional.");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12 resonly">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_units"><?php echo xlt('Default Units'); ?>:</label><a href="#units_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <?php
                                generate_form_field(array(
                                    'data_type' => 1,
                                    'field_id' => 'units',
                                    'list_id' => 'proc_unit',
                                    'description' => xl('Optional default units for manual entry of results')
                                ), $row['units']);
                            ?>
                        </div>
                        <div id="units_info" class="col-sm-8 collapse">
                            <a href="#units_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("Enter the default units for this test.");?></p>
                            <p><?php echo xlt("This code is optional, but is a good practise.");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12 resonly">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_range"><?php echo xlt('Default Range'); ?>:</label><a href="#range_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <input type='text' name='form_range' id='form_range' maxlength='255'
                                value='<?php echo htmlspecialchars($row['range'], ENT_QUOTES); ?>'
                                title='<?php echo xla('Optional default range for manual entry of results'); ?>'
                                class='form-control' >
                        </div>
                        <div id="range_info" class="col-sm-8 collapse">
                            <a href="#range_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("Enter the default range values if applicable, used in manual entry of results.");?></p>
                            <p><?php echo xlt("This code is optional.");?></p>
                        </div>
                    </div>
                    <div class="forms col-xs-12 resonly">
                        <div class="col-sm-2 label-div">
                            <label class="control-label col-sm-2" for="form_related_code"><?php echo xlt('Followup Services'); ?>:</label><a href="#related_code_info" data-toggle="collapse"><i class="fa fa-info-circle" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-sm-8">
                            <input type='text'  name='form_related_code' id='form_related_code'
                                value='<?php echo $row['related_code'] ?>'
                                onclick='sel_related("form_related_code")'
                                title='<?php echo xla('Click to select services to perform if this result is abnormal'); ?>'
                                class='form-control' readonly />
                        </div>
                        <div id="related_code_info" class="col-sm-8 collapse">
                            <a href="#related_code_info" data-toggle="collapse" class="pull-right"><i class="fa fa-times" style="color:gray" aria-hidden="true"></i></a>
                            <p><?php echo xlt("Click to select services to perform if this result is abnormal.");?></p>
                            <p><?php echo xlt("This code is optional.");?></p>
                        </div>
                    </div>
                </fieldset>
                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-alig:center or right as the case may be in individual stylesheets ?>
                <div class="form-group clearfix">
                    <div class="col-xs-12 text-left position-override">
                        <div class="btn-group btn-group-pinch" role="group">
                            <button type='submit' name='form_save'  class="btn btn-default btn-save"  value='<?php echo xla('Save'); ?>'><?php echo xlt('Save'); ?></button>
                            <!--<button type="button" class="btn btn-link btn-cancel btn-separate-left"onclick="top.restoreSession(); location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>';"><?php echo xlt('Cancel');?></button>
                            <input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />
                            &nbsp;
                            <input type='button' value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />-->
                            <button type="button" class="btn btn-link btn-cancel btn-separate-left" onclick='window.close()';><?php echo xlt('Cancel');?></button>
                            <?php if ($typeid) { ?>
                                <!--<input type='submit' name='form_delete' value='<?php echo xla('Delete'); ?>' style='color: red' />-->
                                <button type='submit' name='form_delete'  class="btn btn-default btn-cancel btn-delete btn-separate-left" value='<?php echo xla('Delete'); ?>'><?php echo xlt('Delete'); ?></button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </form>
        </div><!--end of conatainer div-->
    </body>
</html>

