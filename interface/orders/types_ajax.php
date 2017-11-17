<?php
// Copyright (C) 2010-2012 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
use OpenEMR\Core\Header;

require_once("../globals.php");

$id = formData('id', 'G') + 0;
$order = formData('order', 'G') + 0;
$labid = formData('labid', 'G') + 0;

echo "$('#con$id').html('<table width=\"100%\" cellspacing=\"0\">";

// Determine indentation level for this container.
for ($level = 0, $parentid = $id; $parentid; ++$level) {
    $row = sqlQuery("SELECT parent FROM procedure_type WHERE procedure_type_id = '$parentid'");
    $parentid = $row['parent'] + 0;
}

$res = sqlStatement("SELECT * FROM procedure_type WHERE parent = '$id' " .
  "ORDER BY seq, name, procedure_type_id");

$encount = 0;

// Generate a table row for each immediate child.
while ($row = sqlFetchArray($res)) {
    $chid = $row['procedure_type_id'] + 0;

  // Find out if this child has any children.
    $trow = sqlQuery("SELECT procedure_type_id FROM procedure_type WHERE parent = '$chid' LIMIT 1");
    $iscontainer = !empty($trow['procedure_type_id']);

    $classes = 'col1';
    if ($iscontainer) {
        $classes .= ' haskids';
    }

    echo "<tr>";
    echo "<td id=\"td$chid\"";
    echo " onclick=\"toggle($chid)\"";
    echo " class=\"$classes\">";
    echo "<span style=\"margin:0 4 0 " . ($level * 9) . "pt\" class=\"plusminus\">";
    echo $iscontainer ? "+" : '|';
    echo "</span>";
    echo htmlspecialchars($row['name'], ENT_QUOTES) . "</td>";
  //
    echo "<td class=\"col2\">";
    if (substr($row['procedure_type'], 0, 3) == 'ord') {
        if ($order && ($labid == 0 || $row['lab_id'] == $labid)) {
            echo "<input type=\"radio\" name=\"form_order\" value=\"$chid\"";
            if ($chid == $order) {
                echo " checked";
            }

            echo " />";
        } else {
            echo xl('Yes');
        }
    } else {
        echo '&nbsp;';
    }

    echo "</td>";
  //
    echo "<td class=\"col3\">" . htmlspecialchars($row['procedure_code'], ENT_QUOTES) . "</td>";
    echo "<td class=\"col4\">" . htmlspecialchars($row['description'], ENT_QUOTES) . "</td>";
    echo "<td class=\"col5\">";
    echo "<span style=\"color:#000000;\" onclick=\"enode($chid)\" class=\"haskids fa fa-pencil fa-lg\" title=".xl("Edit")."></span>";
    echo "<span style=\"color:#000000; margin-left:30px\" onclick=\"anode($chid)\" class=\"haskids fa fa-plus fa-lg\" title=".xl("Add")." ></span>";
    echo "</td>";
    echo "</tr>";
}

echo "</table>');\n";
echo "nextOpen();\n";
