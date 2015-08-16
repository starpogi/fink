<?php
include_once "c456182.includes.php";
// TextboxList Autocomplete sample data for queryRemote: false (names are fetched all at once when TextboxList is initialized)

// get names (eg: database)
// the format is: 
// id, searchable plain text, html (for the textboxlist item, if empty the plain is used), html (for the autocomplete dropdown)

$response = array();
$places = array();

$placeq = mysql_query("SELECT id, country, street, store, city FROM places",$dbh);

while($place = mysql_fetch_array($placeq))
{
	$places[] = $place['id'] . ":" . $place['store'] . ":" . $place['street'] . ":" . $place['city'] . ":" . $place['country'];
}

// make sure they're sorted alphabetically, for binary search tests
sort($places);

foreach ($places as $i)
{
	$data = explode(":", $i);
	$response[] = array($data[0], changeChars($data[1]) . " @ " . changeChars($data[2]) . ", " . changeChars($data[3]) . ", " . changeChars($data[4]), null,  changeChars($data[1]) . " @ " .  changeChars($data[2]) . "<div class=\"tags\">" .  changeChars($data[3]) . ", " .  changeChars($data[4]));
}

function changeChars($string)
{
	return utf8_encode($string);
}

header('Content-type: application/json');
echo json_encode($response);
?>