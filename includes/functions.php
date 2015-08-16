<?
## FUNCTIONS

function getTagIdLabel($id)
{
	$d = mysql_fetch_array(@mysql_query("SELECT label FROM tags WHERE id = '" . $id . "'"));
	return $d['label'];
}

function addTagAndReturn($label)
{
	if(!ctype_digit($label) && $label != "")
	{
		$d = mysql_num_rows(@mysql_query("SELECT id FROM tags WHERE label = '" . changeChars($label) . "'"));
		if($d == 0) {
			mysql_query("INSERT INTO tags (label) VALUES ('" . changeChars($label) . "')");
			$r = mysql_fetch_array(@mysql_query("SELECT id FROM tags WHERE label = '" . $label . "'"));
			return $r['id'];
		}
	}
	else
	{
		return $label;
	}
}

function changeChars($string)
{
	return utf8_encode($string);
}

function formatMoney($amount, $format, $sort = 0)
{
	$formatOptions = explode(":", $format);
	$number = number_format(abs($amount), 2, substr($formatOptions[0],0,1), substr($formatOptions[0],1,1));
	
	eval("\$price = \"" . $formatOptions[1] . "\";");
	
	if($sort == 0) { $final = ($amount < 0) ? ("<span style='color: #CC0000'>" . $price . "</span>") : ("<span style=''>" . $price . "</span>"); }
	else { $final = $price; }
	return $final;
}

?>