<?php
//
// Render a full row of racks view with server names
// Version 0.1
//
// Written by Philipp Grau <phgrau@zedat.fu-berlin.de>
// 
//
// The purpose of this plugin is to render a web page with all racks of a row 
// with clickable hostnames.
// Currently five racks will be grouped in a row.
//
// History
// Version 0.1:  Initial release (Tested on 0.20.4)
//
// Installation:
// 1)  Copy script to plugins folder as fullrowview.php

$tab['row']['full_row_view'] = 'Full Row View';
$tabhandler['row']['full_row_view'] = 'FullRowView';
$ophandler['row']['full_row_view']['preparePrint'] ='preparePrint';

// Set variables
$frvVersion = "0.1";

function preparePrint()
{
    return showSuccess ("Created <a href=\"rowprint.php\">Print preview</a>!");
}	      
   
	       
// display« the import page.
function FullRowView()
{
    if (isset($_REQUEST['row_id']))
      $row_id = $_REQUEST['row_id'];
    else
      $rack_id=1;
    
    global $frvVersion;
    $rowData = getRowInfo ($row_id);
    
    // renderRack($rack_id);
    
    // echo 'huhu ' . $rackData['row_name'] . ' huhu ' . $rackData['row_id'];
    $cellfilter = getCellFilter();
    $rackList = filterCellList (listCells ('rack', $row_id), $cellfilter['expression']);
    // echo "<form method=post name=ImportObject action='?module=redirect&page=row&row_id=$row_id&tab=full_row_view&op=preparePrint'>";
    echo "<font size=1em color=gray>version ${frvVersion}&nbsp;</font>";
    // echo "<input type=submit name=got_very_fast_data value='Print view'>";
    // echo "</form>";
    echo '<table><tr><td>';
    $count = 1;
    foreach ($rackList as $rack) 
    {
	// echo "<br>Schrank: ${rack['name']} ${rack['id']}";
	// $rackData = spotEntity ('rack', ${rack['id']});
	echo '<div class="phgrack" style="float: left; width: 240px">';
	renderReducedRack("${rack['id']}");
	echo '</div>';
	if ($count % 5 === 0) {
	    echo '</td></tr><tr><td>';
	}

	$count = $count + 1;
				 
    }
    echo '</td></tr></table>';
    // echo "<br>foo";
}
?>

<?

// This is form interface.php: renderRack
// This function renders rack as HTML table.
function renderReducedRack ($rack_id, $hl_obj_id = 0)
{
	$rackData = spotEntity ('rack', $rack_id);
	amplifyCell ($rackData);
	markAllSpans ($rackData);
	if ($hl_obj_id > 0)
		highlightObject ($rackData, $hl_obj_id);
	markupObjectProblems ($rackData);
	$prev_id = getPrevIDforRack ($rackData['row_id'], $rack_id);
	$next_id = getNextIDforRack ($rackData['row_id'], $rack_id);
	echo "<center><table border=0><tr valign=middle>";
	echo '<td><h2>' . mkA ($rackData['name'], 'rack', $rackData['id']) . '</h2></td>';
	echo "</h2></td></tr></table>\n";
	echo "<table class=rack border=0 cellspacing=0 cellpadding=1>\n";
	echo "<tr><th width='10%'>&nbsp;</th><th width='20%'>Front</th>";
	echo "<th width='50%'>Interior</th><th width='20%'>Back</th></tr>\n";
	for ($i = $rackData['height']; $i > 0; $i--)
	{
		echo "<tr><td>" . inverseRackUnit ($i, $rackData) . "</td>";
		for ($locidx = 0; $locidx < 3; $locidx++)
		{
			if (isset ($rackData[$i][$locidx]['skipped']))
				continue;
			$state = $rackData[$i][$locidx]['state'];
			echo "<td class='atom state_${state}";
			if (isset ($rackData[$i][$locidx]['hl']))
				echo $rackData[$i][$locidx]['hl'];
			echo "'";
			if (isset ($rackData[$i][$locidx]['colspan']))
				echo ' colspan=' . $rackData[$i][$locidx]['colspan'];
			if (isset ($rackData[$i][$locidx]['rowspan']))
				echo ' rowspan=' . $rackData[$i][$locidx]['rowspan'];
			echo ">";
			switch ($state)
			{
				case 'T':
					printObjectDetailsForRenderRack($rackData[$i][$locidx]['object_id']);
					break;
				case 'A':
					echo '<div title="This rackspace does not exist">&nbsp;</div>';
					break;
				case 'F':
					echo '<div title="Free rackspace">&nbsp;</div>';
					break;
				case 'U':
					echo '<div title="Problematic rackspace, you CAN\'T mount here">&nbsp;</div>';
					break;
				default:
					echo '<div title="No data">&nbsp;</div>';
					break;
			}
			echo '</td>';
		}
		echo "</tr>\n";
	}
	echo "</table>\n";
	// Get a list of all of objects Zero-U mounted to this rack
	$zeroUObjects = getEntityRelatives('children', 'rack', $rack_id);
	if (count ($zeroUObjects) > 0)
	{
		echo "<br><table width='75%' class=rack border=0 cellspacing=0 cellpadding=1>\n";
		echo "<tr><th>Zero-U:</th></tr>\n";
		foreach ($zeroUObjects as $zeroUObject)
		{
			$state = ($zeroUObject['entity_id'] == $hl_obj_id) ? 'Th' : 'T';
			echo "<tr><td class='atom state_${state}'>";
			printObjectDetailsForRenderRack($zeroUObject['entity_id']);
			echo "</td></tr>\n";
		}
		echo "</table>\n";
	}
	echo "</center>\n";
}
?>