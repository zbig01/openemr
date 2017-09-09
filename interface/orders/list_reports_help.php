<?php
 // Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
use OpenEMR\Core\Header;

include_once("../globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Electronic Reports - Help");?></title>
    <style>
        @media only screen and (max-width: 768px) {
           [class*="col-"] {
           width: 100%;
           text-align:left!Important;
            }
        }
        @media only screen and (max-width: 1004px) and (min-width: 641px)  {
            .oe-large {
                display: none;
            }
            .oe-small {
                display: inline-block;
            }
        }
    </style>
    </head>
    <body>
        <div class="container">
            <div>
                <center><h2><a name = 'entire_doc'><?php echo xlt("Electronic Reports");?></a></h2></center>
            </div>
            <div class= "row">
                <p><?php echo xlt("The electronic reports page lets you list procedure orders and reports and view the reports which are being sent electronically by the lab.");?>
                
                <p><?php echo xlt("In order to use this page the electronic interface to the lab must be set up.");?>
            </div>
             <div class= "row">
                <p><?php echo xlt("The received reports will be displayed as per the selection criteria.");?>
                
                <p><?php echo xlt("In order to limit this report to the current patient you must first enter the patient's chart. You then check the current patient only checkbox on the top right of the 'Select' area."); ?> 
            </div>
            
           
        </div><!--end of container div-->
    </body>
</html>