<?php
/*
 * new.php for the creation of the misc_billing_form
 *
 * This program creates the misc_billing_form
 *
 * Copyright (C) 2007 Bo Huynh
 * Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-license.php.
 *
 * @package OpenEMR
 * @author Terry Hill <terry@lilysystems.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @link http://www.open-emr.org
 *
 */


use OpenEMR\Core\Header;
require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/api.inc");
require_once("date_qualifier_options.php");


if (! $encounter) { // comes from globals.php
 die(xlt("Internal error: we do not seem to be in an encounter!"));
}

$formid   = 0 + formData('id', 'G');
$obj = $formid ? formFetch("form_misc_billing_options", $formid) : array();

formHeader("Form: misc_billing_options");
function generateDateQualifierSelect($name,$options,$obj)
{
    echo     "<select name='".attr($name)."'>";
    for($idx=0;$idx<count($options);$idx++)
    {
        echo "<option value='".attr($options[$idx][1])."'";
        if($obj[$name]==$options[$idx][1]) echo " selected";
        echo ">".text($options[$idx][0])."</option>";
    }
    echo     "</select>";

}
function genProviderSelect($selname, $toptext, $default=0, $disabled=false) {
  $query = "SELECT id, lname, fname FROM users WHERE " .
    "( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
    "AND active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
    "ORDER BY lname, fname";
  $res = sqlStatement($query);
  echo "   <select name='" . attr($selname) . "'";
  if ($disabled) echo " disabled";
  echo ">\n";
  echo "    <option value=''>" . text($toptext) . "\n";
  while ($row = sqlFetchArray($res)) {
    $provid = $row['id'];
    echo "    <option value='" . attr($provid) . "'";
    if ($provid == $default) echo " selected";
    echo ">" . text($row['lname'] . ", " . $row['fname']) . "\n";
  }
  echo text($provid);
  echo "   </select>\n";
}
?>
<html>
<head>
<?php Header::setupHeader(['bootstrap', 'knockout', 'datetime-picker']);?>

<style>
	td{
		 padding: 3px 10px;
	}
	.code_fieldset{
		border: 1px solid #0000FF;
		background-color:#F5F5F5;
		display: block;
		margin-left: 2px;
		margin-right: 2px;
		padding-top: 0.35em;
		padding-bottom: 1em;
		padding-left: 0.75em;
		padding-right: 0.75em;
		font-size:1.3em;
		color:black;
	}

	.code_edit{
		background-color:#E0E0E0;
	}

	.code_legend{
		font-weight:700;
		font-size:16px;
		background-color:#E0E0E0;
		padding:0px 5px 0px 5px;
		border: none!Important;
		width:auto !Important;
		font-size:16px !Important;
		color:black;
		margin-bottom: 0px;
	} 

	#code_edit_table span{
		background-color:yellow;
		font-weight:700;
	}

	#code_edit_table tr td {
		padding: 0px 0px 5px 0px;
	}

	#code_edit_table .code_edit td {
		font-weight: 700;
		padding: 2px 0px 2px 0px;
	}

	#code_edit_table .code_edit td:first-child {
		padding: 0px 0px 0px 10px;
	}
	.block {
		height:100px;
		width:200px;
		text-align:left;
	}
	.center {
		margin:auto;
		
	}
	.form-group{
			margin-bottom: 5px;
			
	}
	legend{
		border-bottom: 2px solid #E5E5E5;
		background:#E5E5E5;
		padding-left:10px;
	}
	.form-horizontal .control-label {
		padding-top: 2px;
	}
	fieldset{
		background-color: #F2F2F2;
		margin-bottom:10px;
		padding-bottom:15px;
	}
	@media only screen and (max-width: 768px) {
					[class*="col-"] {
					width: 100%;
					text-align:left!Important;
	}
</style>
</head>
<body class="body_top">
	<div class="container">
		<div class="row">
            <div class="">
                <div class="page-header">
                    <h2><?php echo xlt('Misc Billing Options for HCFA-1500'); ?></h2>
                </div>
            </div>
        </div>
		<div class="row">
			<form method=post <?php echo "name='my_form' " .  "action='$rootdir/forms/misc_billing_options/save.php?id=" . attr($formid) . "'\n";?>>
				<fieldset>
					<legend class=""><?php echo xlt('Select Options for Current Encounter')?></legend>
					<div class='col-sm-11 col-offset-sm-1'>
						<span class="text"><?php echo xlt('Checked box = yes ,  empty = no');?><br><br></span>
						<div class="form-group">
							<label><?php echo xlt('BOX 10 A. Employment related '); ?>:
								<input type="checkbox" name="employment_related" id="employment_related" value="1" <?php if ($obj['employment_related'] == "1") echo "checked";?>>
							</label>
						</div>
						<div class="form-group">
							<label><?php echo xlt('BOX 10 B. Auto Accident '); ?>:
								<input type="text" name="accident_state" size=1 value="<?php echo attr($obj{"accident_state"});?>" >
							</label>
							<label><?php echo xlt('State'); ?>:
								<input type="text" name="accident_state" size=1 value="<?php echo attr($obj{"accident_state"});?>" >
							</label>
						</div>
						<div class="form-group">
							<label><?php echo xlt('BOX 10 C. Other Accident '); ?>:
								<input type="checkbox" name="other_accident" value="1" <?php if ($obj['other_accident'] == "1") echo "checked";?>>
							</label>
						</div>
						<div class="form-group">
							<label><?php echo xlt('BOX 10 D. EPSDT Referral Code');?> 
								<input type="text" name="medicaid_referral_code" value="<?php echo attr($obj{"medicaid_referral_code"});?>" >
							</label>
							<label><?php echo xlt('EPSDT'); ?> :
								 <input type="checkbox" name="epsdt_flag" value="1" <?php if ($obj['epsdt_flag'] == "1") echo "checked";?>>
							</label>
						</div>
						<span class="text" title="<?php echo xlt("For HCFA 02/12 Onset date specified on the Encounter Form needs a qualifier");?>"></span>
						<span class="text" title="<?php echo xlt('For HCFA 02/12 Box 15 is Other Date with a qualifier to specify what the date indicates');?>"></span>
						<div class="form-group">
							<label><?php echo xlt('BOX 14. Is Populated from the Encounter Screen as the Onset Date');?>
								
							</label>
						</div>
						<div class="form-group">
								<label for='off_work_from' class="col-sm-3 form-inline"><?php echo xlt('BOX 16. Date unable to work from');?>:</label>
									<?php $off_work_from = $obj{"off_work_from"}; ?>
										<input type="text" class='datepicker form-inline col-sm-1 ' name='off_work_from' id='off_work_from' value='<?php echo attr($off_work_from); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>'>
								<label for='off_work_to'class="col-sm-3 form-inline"><?php echo xlt('BOX 16. Date unable to work to');?>:</label>
									<?php $off_work_to = $obj{"off_work_to"}; ?>
										<input type="text"  class='datepicker col-sm-1' name='off_work_to' id='off_work_to' value='<?php echo attr($off_work_to); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>'>
						</div>
						<div class="clearfix "></div>
						<div class="form-group">
							<label class="form-inline"><?php echo xlt('BOX 17. Provider') ?>:
								<?php  # Build a drop-down list of providers. # Added (TLH)
									   echo genProviderSelect('provider_id', '-- '.xl("Please Select").' --',$obj{"provider_id"});
								?>
							</label>
							<label class="form-inline"><?php  echo xlt('BOX 17. Provider Qualifier'); ?>:
								<?php	echo generate_select_list('provider_qualifier_code', 'provider_qualifier_code',$obj{"provider_qualifier_code"}, 'Provider Qualifier Code');?>
							</label>
						</div>
						<div class="form-group">
							<label for='hospitalization_date_from' class="col-sm-3 form-inline"><?php echo xlt('BOX 18. Hospitalization date from');?>:</label>
								<?php $hospitalization_date_from = $obj{"hospitalization_date_from"}; ?>
									<input type="text" class='datepicker col-sm-1 ' name='hospitalization_date_from' id='hospitalization_date_from' value='<?php echo attr($hospitalization_date_from); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>'>
							<label for='off_work_to'class="col-sm-3 form-inline"><?php echo xlt('BOX 18. Hospitalization date to');?>:</label>
								<?php $hospitalization_date_to = $obj{"hospitalization_date_to"}; ?>
									<input type="text"  class='datepicker col-sm-1'  name='hospitalization_date_to' id='hospitalization_date_to' value='<?php echo attr($hospitalization_date_to); ?>' title='<?php echo xla('yyyy-mm-dd'); ?>'>
						</div>
						<div class="clearfix "></div>
						<div class="form-group">
							<label><?php echo xlt('BOX 20. Is Outside Lab used?'); ?>:
								<input type="checkbox" name="outside_lab" value="1" <?php if ($obj['outside_lab'] == "1") echo "checked";?>>
							</label>
							<label><?php echo xlt('Amount Charges'); ?>:
								<input type="text" size=7 align='right' name="lab_amount" value="<?php echo attr($obj{"lab_amount"});?>" >
							</label>
						</div>
						<div class="form-group">
							<label><?php echo xlt('BOX 22. Medicaid Resubmission Code (ICD-10) ');?>:
								<input type="text"  name="medicaid_resubmission_code" value="<?php echo attr($obj{"medicaid_resubmission_code"});?>" >
							</label>
							<label><?php echo xlt(' Medicaid Original Reference No. ');?>:
								<input type="text"  name="medicaid_original_reference" value="<?php echo attr($obj{"medicaid_original_reference"});?>" >
							</label>
						</div>
						<div class="form-group">
							<label><?php echo xlt('BOX 23. Prior Authorization No. ');?>:
								<input type="text"  name="prior_auth_number" value="<?php echo attr($obj{"prior_auth_number"});?>" >
							</label>
						</div>
						<div class="form-group">
							<label><?php echo xlt('X12 only: Replacement Claim '); ?>:
								<input type="checkbox" name="replacement_claim" value="1" <?php if ($obj['replacement_claim'] == "1") echo "checked";?>>
							</label>
							<label><?php echo xlt('X12 only ICN resubmission No.');?>:
								<input type="text" class="" name="icn_resubmission_number" value="<?php echo attr($obj{"icn_resubmission_number"});?>" >
							</label>
						</div>
					</div>
				</fieldset>
				<fieldset>
						<legend class=""><?php echo xlt('Additional Notes');?></legend>
							<div class="form-group">
								<div class="col-sm-10 col-sm-offset-1">
									<textarea name="comments"	class="form-control" cols="80" rows="3" ><?php echo text($obj{"comments"});?></textarea>
								</div>
							</div>
				</fieldset>

				<div class="form-group">
					<div class="col-sm-12 text-center">
						<div class="btn-group" role="group">
							<!-- Save/Cancel buttons -->
							<button type="submit" class="btn btn-default btn-save save" > <?php echo xla('Save'); ?></button>
							<button type="button" class="btn btn-default btn-cancel dontsave"><?php echo xla('Don\'t Save Changes'); ?></button> 
						</div>
					</div>
				</div>
			</form>
			<br>
			<br>
		</div>
	</div>
<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".save").click(function() { top.restoreSession(); document.my_form.submit(); });
    $(".dontsave").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/encounter_top.php";?>'; });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});
</script>
</body>
</html>