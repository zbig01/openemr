<?php
/**
 * Transactions Dashboard Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @version 1.0.0
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
 
use OpenEMR\Core\Header;

require_once("../../interface/globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Patient Transactions Help");?></title>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <center><h2><a name='entire_doc'><?php echo xlt("Patient Transactions Help");?></a></h2></center>
            </div>
            <div class= "row">
                <div class="col-sm-12">
                    <p><?php echo xlt("Transactions are for events or information not necessarily tied to one specific visit or encounter");?>.
                    
                    <p><?php echo xlt("Most activities in relation to a patient are based on an encounter");?>.
                    
                    <p><?php echo xlt("Transactions provides a mechanism to link an activity to patient that is not encounter based");?>.
                    
                    <p><?php echo xlt("Upon entering the page there are two buttons visible below the navigation bar");?>.
                    <ul>
                        <li><?php echo xlt("Create New Transaction"); ?> <button type="button" class="btn btn-default btn-sm btn-add oe-no-float"><?php echo xlt("Create New Transaction"); ?></button></li>
                        <li><?php echo xlt("View/Print Blank Referral Form - that can be filled by hand"); ?>  <button type="button" class="btn btn-default btn-sm btn-print oe-no-float"><?php echo xlt("View/Print Blank Referral Form"); ?></button></li>
                    </ul>
                    
                    <p><?php echo xlt("Below this is a table that contains the existing transactions, if any, pertaining to the current patient");?>.
                    
                    <p><?php echo xlt("There are three sets of actions that can be performed on this page");?>.
                                        
                    <ul>
                        <li><a href="#section1"><?php echo xlt("Create New Transaction");?></a></li>
                        <li><a href="#section2"><?php echo xlt("Interact with created transactions");?></a></li>
                        <li><a href="#section3"><?php echo xlt("View/Print Blank Referral Form");?></a></li>
                    </ul>
                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Create New Transaction"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Click on Create New Transaction to open the Add/Edit Patient Transaction page");?>.
                    
                    <p><?php echo xlt("This is where the referrals and various other simple transactions are created");?>.
                    
                    <p><?php echo xlt("Use the help file on that page for further help");?>.
                </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Interact with created transactions"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("All Transactions for the patient will appear on the Transactions page in descending order of its date of creation");?>.
                    
                    <p><?php echo xlt("Each Transaction is listed on a separate line");?>.
                    
                    <p><?php echo xlt("Depending on the level of access you can View/Edit the Transaction");?>.
                    
                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo xlt("Those with adequate privilege would be able to able to Delete the transaction");?>.
                    
                    <p><?php echo xlt("These two actions are available for all transactions");?>.
                    
                    <p><?php echo xlt("A Referral has an additional action - to print the referral or save it as a pdf file");?>.
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("View/Print Blank Referral Form"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("In addition to creating a Referral the system also allows you to print a blank referral form that can be manually filled to generate a Referral");?>.
                                        
                    <p><?php echo xlt("This method will however result in the data becoming non-structured and one would loose the ability to document the reply in an electronic format");?>.
                </div>   
                
            </div>
        </div><!--end of container div-->
        
    </body>
</html>
