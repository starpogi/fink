<?
include_once "includes/c456182.includes.php";

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
	return $string;
}

function formatMoney($amount, $format, $sort = 0)
{
	$formatOptions = explode(":", $format);
	$number = number_format(abs($amount), 2, substr($formatOptions[0],0,1), substr($formatOptions[0],1,1));
	
	eval("\$price = \"" . $formatOptions[1] . "\";");
	
	if($sort == 0) { $final = ($amount < 0) ? ("<span style='float:left;clear:left'>&mdash;</span>" . $price) : ("<span style='float:left;clear:left'></span>" . $price); }
	else { $final = $price; }
	return $final;
}

if(isset($_POST['sub']))
{
	$dateData = strtotime($_POST['date']);
	$timeData = strtotime($_POST['time']);
	
	$date = date("Y-m-d", $dateData) . " " . date("H:i:s", $timeData);
	
	$tagsData = explode(",", $_POST['tags']);
	$tags = implode(",", array_map('addTagAndReturn', $tagsData));
	
	if(!ctype_digit($_POST['place']) && $_POST['place'] != "") {
		
		$firstSegment = explode("@", $_POST['place']);
		$store = changeChars(trim($firstSegment[0]));
		
		$secondSegment = explode(",", $firstSegment[1]);
		$country = changeChars(trim(array_pop($secondSegment)));
		$city = changeChars(trim(array_pop($secondSegment)));
		$street = changeChars(trim(@implode(",", $secondSegment)));
		
		$d = mysql_num_rows(@mysql_query("SELECT id FROM places WHERE store = '" . $store . "' AND city = '" . $city . "' AND country = '" . $country . "' AND street = '" . $street . "'", $dbh));
		
		if($d == 0)
		{
			mysql_query("INSERT INTO places (store, city, country, street) VALUES ('" . $store . "', '" . $city . "', '" . $country . "', '" . $street . "')");
			$r = mysql_fetch_array(@mysql_query("SELECT id FROM places WHERE store = '" . $store . "' AND city = '" . $city . "' AND country = '" . $country . "' AND street = '" . $street . "'", $dbh));
			$place = $r['id'];
		}
		else
		{
			$place = $d['id'];
		}
	}
	else
	{ 
		$place = $_POST['place']; 
	}
	
	// Check for duplicates
	$d = @mysql_num_rows(@mysql_query("SELECT id FROM items WHERE price = '" . $_POST['price'] . "' AND tagIds = '" . $tags . "' AND placeId = '" . $place . "' AND date = '" . $date . "' AND remarks = '" . changeChars($_POST['remarks']) . "' AND item = '" . changeChars($_POST['item']) . "'", $dbh));
	
	if($d == 0 && $_POST['item'] && $tags && $place && $date && $_POST['price'])
	{
		mysql_query("INSERT INTO items (price, tagIds, placeId, date, item, priceFormatId, remarks) VALUES ('" . $_POST['price'] . "', '" . $tags . "', '" . $place . "', '" . $date . "', '" . changeChars($_POST['item']) . "', '1', '" . changeChars($_POST['remarks']) . "')", $dbh);
	}
	else
	{
		$msg = "Was not added.";
	}
	
	if(mysql_affected_rows($dbh) > 0) { $msg = "All Set!"; }
}
		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>pamahalaanan ang mga gastusin : b. 1</title>
	
		<!-- required stylesheet for TextboxList -->
		<link rel="stylesheet" href="css/TextboxList.css" type="text/css" media="screen" charset="utf-8" />
		<!-- required stylesheet for TextboxList.Autocomplete -->
		<link rel="stylesheet" href="css/TextboxList.Autocomplete.css" type="text/css" media="screen" charset="utf-8" />
		
		<script src="js/mootools-1.2.1-core-yc.js" type="text/javascript" charset="utf-8"></script>		

		<!-- required for TextboxList -->
		<script src="js/GrowingInput.js" type="text/javascript" charset="utf-8"></script>
				
		<script src="js/TextboxList.js" type="text/javascript" charset="utf-8"></script>		
		<script src="js/TextboxList.Autocomplete.js" type="text/javascript" charset="utf-8"></script>
		<!-- required for TextboxList.Autocomplete if method set to 'binary' -->
		<script src="js/TextboxList.Autocomplete.Binary.js" type="text/javascript" charset="utf-8"></script>		
		
        <!-- sample initialization -->
		<script type="text/javascript" charset="utf-8">	
		
			window.addEvent('domready', function(){
	
				// Easy Edit™
				// Javier Onglao
				
	
				// Auto Complete for Tags
				var tags = new TextboxList('tags', {unique: true, plugins: {autocomplete: {placeholder: 'Add tags to this item.'}}});
				
				tags.container.addClass('textboxlist-loading');				
				new Request.JSON({url: 'includes/tags.php', onSuccess: function(r){
					tags.plugins['autocomplete'].setValues(r);
					tags.container.removeClass('textboxlist-loading');
				}}).send();	
				
				// Auto Complete for Places
				var places = new TextboxList('place', {max: 1, unique: true, plugins: {autocomplete: {placeholder: 'Choose the place of the purchase of this item.'}}});
				
				places.container.addClass('textboxlist-loading');				
				new Request.JSON({url: 'includes/places.php', onSuccess: function(r){
					places.plugins['autocomplete'].setValues(r);
					places.container.removeClass('textboxlist-loading');
				}}).send();	
				
				var detaileddailytotal = function(el) {
					
					$$('[rel=disable]').each(function(x,e) {
						if($(x).get('action') == "hide:detailed")
						{	$(x).set('text', 'Show Detailed');
							$(x).set('action', 'show:detailed');	}
						else
						{	$(x).set('text', 'Hide Detailed');
							$(x).set('action', 'hide:detailed');	}
					});
									 				
					$$('.detaileddaydisplay').each(function(e,s) {
						var r = $(e).getStyle('display');
						
						if(r == 'block')
						{	$(e).setStyle('display', 'none');	}
						else 
						{	$(e).setStyle('display', 'block');	}
					});
				};
				
				var purgeDetaileddailytotal = function(el) {
						
					$$('.detaileddaydisplay').each(function(e,s) {
						$(e).setStyle('display', 'none');
					});
				};
				
				//$('addForm').addEvent('submit', function() { alert(tags.getValues().clean()); });	
				$$('.buttons').each(function(slab, num)
				{
					
					$(slab).addEvent('click', function() {
						switch(this.get('action'))
						{
							case 'item:add':
								$('addBlock').setStyle('display', 'block');
								window.location = '#add';
								$('item').focus();
								
								$$('[action=item:add]').each(function(c,d) {
									$(c).set('text', 'Discard');
									$(c).set('action', 'item:discarde');
								});
							break;
							
							case 'item:discarde':
								$('addBlock').setStyle('display', 'none');
								window.location = '#top';
								
								$$('[action=item:discarde]').each(function(c,d) {
									$(c).set('text', 'Add Item');
									$(c).set('action', 'item:add');
								});
							break;
							
							case 'item:discard':
								$('addBlock').setStyle('display', 'none');
								window.location = '#top';
								
								$$('[action=item:discarde]').each(function(c,d) {
									$(c).set('text', 'Add Item');
									$(c).set('action', 'item:add');
								});
							break;
							
							case 'show:dailytotal':
								$$('.daydisplay').each(function(a,b) {
									$('day:'+b).set('html', $(a).get('html'));
									$(a).set('html', $('daytotal:'+b).get('value'));
									$(a).tween('background-color', '#000');
								});
								
								$$('[rel=disable]').each(function(x,e) {
									 $(x).removeClass('buttonsdis');
									 $(x).addClass('buttons');
									 $(x).addEvent('click', detaileddailytotal);
								});
								
								$$('[action=show:dailytotal]').each(function(c,d) {
									$(c).set('text', 'Hide Daily Total');
									$(c).set('action', 'hide:dailytotal');
								});
							break;
							
							case 'hide:dailytotal':
								$$('.daydisplay').each(function(a,b) {
									$(a).set('html', $('day:'+b).get('html'));
									$(a).tween('background-color', '#333');
								});
								
								$$('[rel=disable]').each(function(x,e) {
									 $(x).removeClass('buttons');
									 $(x).addClass('buttonsdis');
									 
									 $(x).removeEvent('click', detaileddailytotal);
									 purgeDetaileddailytotal();
									 
									 $(x).set('text', 'Show Detailed');
									 $(x).set('action', 'show:detailed');
								});
								
								$$('[action=hide:dailytotal]').each(function(c,d) {
									$(c).set('text', 'Show Daily Total');
									$(c).set('action', 'show:dailytotal');
								});
							break;
							
						}
					});
				});	
			});
		</script>
		
		<!-- sample style -->
<style type="text/css" media="screen">
	input.add { border: 0; padding: 4px; *padding-bottom: 0; height: 14px; font: 11px "Lucida Grande", Verdana;  width: 500px; }
	input.add:focus { outline: 0 }
	div.input-box { padding: 4px 0px; border: 1px solid #999999; width: 550px; }
	
	.form_tags { margin-bottom: 10px; }
	
	/* Setting widget width example */
	.textboxlist { width: 550px; }
	
	/* Preloader for autocomplete */
	.textboxlist-loading { background: url('images/spinner.gif') no-repeat 380px center; }
	
	/* Autocomplete results styling */
	.form_friends .textboxlist-autocomplete-result { overflow: hidden; zoom: 1; }
	.form_friends .textboxlist-autocomplete-result img { float: left; padding-right: 10px; }
	
	.note { color: #666; font-size: 90%; }
	#footer { margin: 50px; text-align: center; }

	div.tags
	{
		color: #666666;
		font-size: 10px;
		text-transform: uppercase;
	}
	
	span.buttons
	{
		padding: 5px;
		border: 1px solid black;
		cursor: pointer;
		background: #99CCFF;
		color: #000000
	}
	
	span.buttons:hover
	{
		background: #000066;
		color: #fff;
		text-shadow: black 0.05em 0.05em 0.1em;
	}
	
	span.buttonsdis
	{
		padding: 5px;
		border: 1px solid black;
		color: #666666;
		cursor: default;
		background: #999999;
	}
</style>
</head>

<body>
<a name="top"></a>
<div style="font-size:22px">Pamahalaanan ang mga Gastusin</div>
<div style="font-size:14px; text-transform:uppercase">unang bersyon</div>
<hr /><br />

Prices in Euros<br /><br />
<?=$msg?>
<div id="addBlock" style="display:none">
	<form method="post" name="addForm" id="addForm" action="<?=$_SERVER['PHP_SELF']?>">
    	<table width="700px" align="center" border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse">
			<tr>
            	<td colspan="2" align="right"><a name="add"></a>Add Item</td>
            </tr>
            <tr>
            	<td align="right">Item</td>
                <td><div class="input-box"><input type="text" id="item" class="add" name="item" /></div></td>
            </tr>
            <tr>
            	<td align="right">Place of Purchase</td>
                <td width="80%"><div class="form_friends"><input type="text" id="place" name="place" /></div><div class="tags" style="white-space:normal">Start typing the place that you remember. Press Enter to add a new place (Store @ Street, City, Country)</div></td>
            </tr>
            <tr>
            	<td align="right">Price</td>
                <td><div class="input-box"><input type="text" id="price" class="add" name="price" /></div></td>
            </tr>
            <tr>
            	<td align="right">Tags</td>
                <td width="80%"><div class="form_friends"><input type="text" id="tags" name="tags" /></div><div class="tags" style="white-space:normal">Start typing tags that you remember to display them. You can add a new tag by pressing Enter.</div></td>
            </tr>
            <tr>
            	<td align="right">Date</td>
                <td><div class="input-box"><input type="text" id="date" class="add" name="date" /></div></td>
            </tr>
            <tr>
            	<td align="right">Time</td>
                <td><div class="input-box"><input type="text" id="time" class="add" name="time" /></div></td>
            </tr>
            <tr>
            	<td align="right">Remarks</td>
                <td><div class="input-box"><input type="text" class="add" name="remarks" /></div></td>
            </tr>
            <tr>
            	<td colspan="2"><input type="submit" name="sub" value="Add to the List" /><input type="button" class="buttons" action="item:discard" value="Discard" /></td>
            </tr>
		</table>
    </form>
    <br /><br />
</div>
<?
	$d = mysql_fetch_array(mysql_query("SELECT SUM(price) AS tray, SUM(IF(price<0,price,0)) AS neg, SUM(IF(price>=0,price,0)) AS pos FROM items", $dbh));
?>

<table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse">
	<tr>
    	<td colspan="5" align="right" style="padding:0px;text-align:left"><span action="item:add" class="buttons">Add Item</span><span action="item:delete" class="buttons">Delete Items</span>|<span action="show:today" class="buttons">Show Today</span><span action="show:dailytotal" class="buttons">Show Daily Total</span><span action="show:detailed" class="buttonsdis" rel="disable">Show Detailed</span>|<span action="plot:all" class="buttons">Plot Expenses</span></td>
        <td align="right" bgcolor="#FFFF66"><div class="detaileddaydisplay" style="display:none;"><?=formatMoney($d['pos'], ",.:\$number &euro;", 1)?><br /><span style='float:left;clear:left'>&mdash;</span><?=formatMoney($d['neg'], ",.:\$number &euro;", 1)?></div><?=formatMoney($d['tray'], ",.:\$number &euro;", 1)?></td>
        <td bgcolor="#FFCC33">TOTAL</td>
    </tr>
	<tr align="center" style="font-weight:bold; font-size:13px; text-transform:uppercase">
    	<td width="2%"><input type="checkbox" /></td>
    	<td width="10%">Date</td>
        <td width="6%">Time</td>
        <td width="17%">Place</td>
        <td width="42%">Item</td>
        <td width="10%">Price</td>
        <td width="13%">Remarks</td>
    </tr>
    <?php
	$q = mysql_query("SELECT items.id, places.store, places.city, places.country, places.street, items.tagIds, items.date, items.item, items.price, priceFormat.format, items.remarks FROM (places, items, priceFormat) WHERE places.id = items.placeId AND priceFormat.id = items.priceFormatId ORDER BY items.date DESC", $dbh);
	
	if(mysql_num_rows($q) > 0) {
	
	$id = 0;
	$itemid = 1;
	
	while($i = @mysql_fetch_array($q))
	{
	
		$datecode = strtotime($i['date']);
		$date = date("D, j M Y", $datecode);
		$time = date("h:i A", $datecode);
		
		// Date Codex : determines if it is still on the same date
			# determine date of the next id
		$r = mysql_fetch_array(mysql_query("SELECT COUNT(*) as count FROM items WHERE DATE_FORMAT(items.date, '%Y-%m-%d') = DATE_FORMAT('" . $i['date'] . "', '%Y-%m-%d') GROUP BY DATE_FORMAT(items.date, '%e-%c-%Y')", $dbh));
		$thisday = $r['count'];
		
		// Determine Price Format
		$formatOptions = explode(":", $i['format']);
		
		// Day Sum POSITIVE
		$daysumPOS = $i['price'] >= 0 ? $daysumPOS + $i['price']:$daysumPOS;
		
		// Day Sum NEGATIVE
		$daysumNEG = $i['price'] < 0 ? $daysumNEG + $i['price']:$daysumNEG;
		
		// Get Tags
		$tagIds = @explode(",", $i['tagIds']);
		$tags = @implode(", ", @array_map('getTagIdLabel', $tagIds));
	
	?>
    <tr valign="top">
    	<td><input type="checkbox" /></td>
    	<td align="right"><?=$date?></td>
        <td><?=$time?></td>
        <td width="13%" style="overflow:hidden; white-space:normal"><?=utf8_encode($i['store'])?> @ <?=utf8_encode($i['street'])?><div class="tags"><?=utf8_encode($i['city'])?>, <?=utf8_encode($i['country'])?></div></td>
        <td><?=utf8_encode($i['item'])?><div class="tags"><?=$tags?></div></td>
        <td align="right"><?=formatMoney($i['price'], $i['format'])?></td>
        <td><?=utf8_encode($i['remarks'])?></td>
    </tr><?
    	// Mark the Next Days
		if($thisday == $itemid) {
    ?><tr bgcolor="#333333" style="border: 0px solid #333333">
    	<td colspan="5" id="day:<?=$id?>"></td><td style="padding: 0px; text-align: right; color: #FFFFFF; text-shadow: black 0.05em 0.05em 0.1em;"><div class="detaileddaydisplay" style="display:none; padding: 2px"><?=formatMoney($daysumPOS, $i['format'], 1)?><br /><span style='float:left;clear:left'>&mdash;</span><?=formatMoney($daysumNEG, $i['format'], 1)?></div><div style="padding: 2px;" class="daydisplay"><input type="hidden" value="<?=formatMoney(($daysumNEG + $daysumPOS), $i['format'])?>" id="daytotal:<?=$id?>" /></div></td><td></td>
    </tr><?
     $id++; $daysum = $daysumNEG = $daysumPOS = 0; $itemid = 0; /* Reset Counter */  }  $itemid++;
    ?>
    <? } } else {
    ?><tr valign="top">
    	<td colspan="7">Hey, there aren't any items! You might wanna start by adding one!</td>
    </tr>
    <? }
    ?><tr>
    	<td colspan="5" align="right" style="padding:0px;text-align:left"><span action="item:add" class="buttons">Add Item</span><span action="item:delete" class="buttons">Delete Items</span>|<span action="show:today" class="buttons">Show Today</span><span action="show:dailytotal" class="buttons">Show Daily Total</span><span action="show:detailed" class="buttonsdis" rel="disable">Show Detailed</span>|<span action="plot:all" class="buttons">Plot Expenses</span></td>
        <td align="right" bgcolor="#FFFF66"><div class="detaileddaydisplay" style="display:none;"><?=formatMoney($d['pos'], ",.:\$number &euro;")?><br /><span style='float:left;clear:left'>&mdash;</span><?=formatMoney($d['neg'], ",.:\$number &euro;")?></div><?=formatMoney($d['tray'], ",.:\$number &euro;")?></td>
        <td bgcolor="#FFCC33">TOTAL</td>
    </tr>
</table>
<br /><br /><br />copyright of javier onglao<br /><br />
</body>
</html>
