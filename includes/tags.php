<?php
include_once "c456182.includes.php";

// TextboxList Autocomplete sample data for queryRemote: false (names are fetched all at once when TextboxList is initialized)

// get names (eg: database)
// the format is: 
// id, searchable plain text, html (for the textboxlist item, if empty the plain is used), html (for the autocomplete dropdown)

$response = array();
$tags = array();

$tagsquery = mysql_query("SELECT id, label FROM tags", $dbh);

while($tag = mysql_fetch_array($tagsquery))
{
	$tags[] = $tag['id'] . ":" . $tag['label'];
}

// make sure they're sorted alphabetically, for binary search tests
sort($tags);

foreach ($tags as $i)
{
	$data = explode(":", $i);
	$response[] = array($data[0], $data[1], null, $data[1]);
}

header('Content-type: application/json');
echo json_encode($response);