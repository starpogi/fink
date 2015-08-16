<?
include_once "includes/c456182.includes.php";
include_once "includes/functions.php";

if(isset($_POST['subadd']))
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
		$b = "INSERT INTO items (quantity, price, tagIds, placeId, date, item, priceFormatId, remarks, accountId) VALUES ('" . $_POST['quantity'] . "', '" . $_POST['price'] . "', '" . $tags . "', '" . $place . "', '" . $date . "', '" . changeChars($_POST['item']) . "', '1', '" . changeChars($_POST['remarks']) . "', '" . $_POST['account'] . "')";
		mysql_query($b, $dbh);
	}
	else
	{
		$msg = changeChars($_POST['item']) . " was not added.";
		$color = "#ffcc00";
	}
	
	if(mysql_affected_rows($dbh) > 0) { 
		$msg = changeChars($_POST['item']) . " has been added."; 
		$color = "#99FF66";
	}
}

if(isset($_POST['subtransfer']))
{
	$dateData = strtotime($_POST['date']);
	$timeData = strtotime($_POST['time']);
	
	$date = date("Y-m-d", $dateData) . " " . date("H:i:s", $timeData);
	
	// Check that the values are ok
	$value1 = mysql_fetch_array(mysql_query("SELECT SUM(price) AS total FROM items WHERE accountId = '" . $_POST['fromAccount'] . "'", $dbh));
	$acct1 = mysql_fetch_array(mysql_query("SELECT label FROM accounts WHERE id = '" . $_POST['fromAccount'] . "'", $dbh));
	$acct2 = mysql_fetch_array(mysql_query("SELECT label FROM accounts WHERE id = '" . $_POST['toAccount'] . "'", $dbh));
	
	if($value1['total'] >= $_POST['amount'])
	{
		$b = "INSERT INTO items (showacc, accountId, quantity, price, date, item, priceFormatId, remarks, mode, placeId, tagIds) VALUES ('0', '" . $_POST['fromAccount'] . "', '1', '" . (-1 * $_POST['amount']) . "', '" . $date . "', '" . changeChars($acct1['label']) . " &rArr; " . changeChars($acct2['label']) . "', '1', '" . changeChars($_POST['remarks']) . "', 'transfer', '1', '71')";
		mysql_query($b, $dbh);
		$d = "INSERT INTO items (showacc, accountId, quantity, price, date, item, priceFormatId, remarks, mode, placeId, tagIds) VALUES ('1', '" . $_POST['toAccount'] . "', '1', '" . ($_POST['amount']) . "', '" . $date . "', '" . changeChars($acct1['label']) . " &rArr; " . changeChars($acct2['label']) . "', '1', '" . changeChars($_POST['remarks']) . "', 'transfer', '1', '71')";
		mysql_query($d, $dbh);
	}
	else
	{
		$msg = "Cannot Transfer. You cannot exceed the amount in that account.";
		$color = "#ffcc00";
	}
	
	if(mysql_affected_rows($dbh) > 0) { 
		$msg = "Transfer complete."; 
		$color = "#99FF66";
	}
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

		<script src="js/GrowingInput.js" type="text/javascript" charset="utf-8"></script>		
		<script src="js/TextboxList.js" type="text/javascript" charset="utf-8"></script>		
		<script src="js/TextboxList.Autocomplete.js" type="text/javascript" charset="utf-8"></script>
		<!-- required for TextboxList.Autocomplete if method set to 'binary' -->
		<script src="js/TextboxList.Autocomplete.Binary.js" type="text/javascript" charset="utf-8"></script>		
		<script src="js/functions.js" type="text/javascript" charset="utf-8"></script>		
		<link rel="stylesheet" type="text/css" href="css/main.css" />
</head>

<body>
<a name="top"></a>
<div style="font-size:22px">Pamahalaanan ang mga Gastusin</div>
<div style="font-size:14px; text-transform:uppercase">pangalawang bersyon</div>
<hr /><br />
<?
	//TOTAL
	$d = mysql_fetch_array(mysql_query("SELECT SUM(price) AS tray, SUM(IF(price<0,price,0)) AS neg, SUM(IF(price>=0,price,0)) AS pos FROM items", $dbh));
	
	$accounts = mysql_query("SELECT label, id FROM accounts", $dbh);
	$sumtotal = mysql_fetch_array(mysql_query("SELECT SUM(price) as total FROM items",$dbh));
	$sumactive = mysql_fetch_array(mysql_query("SELECT SUM(items.price) AS total FROM items, accounts WHERE accounts.sum =  '1' AND accounts.id = items.accountId",$dbh));
?>
<!-- LIST ALL ACTIVE ACCOUNTS -->
<ol class="accounts">
	<li>
        <span class="accountName">All</span><span class="accountBalance"><?=formatMoney($sumtotal['total'], ",.:\$number &euro;", 1)?></span>
    </li>
    <li>
        <span class="accountName">Active</span><span class="accountBalance"><?=formatMoney($sumactive['total'], ",.:\$number &euro;", 1)?></span>
        <span class="selectedAccount">&nabla;</span>
    </li>
<? while($accountlist = @mysql_fetch_array($accounts))
{
	$summation = mysql_fetch_array(mysql_query("SELECT SUM(price) as total FROM items WHERE accountId='" . $accountlist[id] . "'",$dbh));
?>
    <li>
        <span class="accountName"><?=$accountlist['label']?></span><span class="accountBalance"><?=formatMoney($summation['total'], ",.:\$number &euro;", 1)?></span>
    </li>
<?
}
?>
</ol>

<br /><br />

<? if($msg) { ?><div class="buttons" action="hide:status" style="cursor:pointer;padding:7px;height:18px;background:<?=$color?>;border:3px solid black;margin-bottom:10px"><span style="float:left;clear:left;display:inline"><?=$msg?></span><span style="float:right;clear:right;font-size:12px">CLICK TO DISMISS</span></div><? } ?>

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
            	<td align="right">Quantity</td>
                <td><div class="input-box"><input type="text" id="quantity" class="add" name="quantity" value="1" /><select><option>piece</option><option>kg</option></select></div></td>
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
            	<td align="right">Account</td>
                <td><div class="input-box">
                	<select class="add" name="account">
                    <?
						$accounts = mysql_query("SELECT label, id FROM accounts", $dbh);
						while($account = @mysql_fetch_array($accounts))
						{
					?>
                    	<option value="<?=$account['id']?>"><?=$account['label']?></option>
                    <?	
						}
					?>
                    </select>
                </div></td>
            </tr>
            <tr>
            	<td align="right">Remarks</td>
                <td><div class="input-box"><input type="text" class="add" name="remarks" /></div></td>
            </tr>
            <tr>
            	<td colspan="2"><input type="submit" name="subadd" value="Add to the List" /><input type="button" class="buttons" action="item:discard" value="Discard" /></td>
            </tr>
		</table>
    </form>
    <br /><br />
</div>

<div id="transferBlock" style="display:none">
	<form method="post" name="transferForm" id="transferForm" action="<?=$_SERVER['PHP_SELF']?>">
    	<table width="700px" align="center" border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse">
            <tr>
            	<td align="right">From</td>
            	<td><select class="add" name="fromAccount">
                    <?
						$accounts = mysql_query("SELECT label, id FROM accounts", $dbh);
						while($account = @mysql_fetch_array($accounts))
						{
					?>
                    	<option value="<?=$account['id']?>"><?=$account['label']?></option>
                    <?	
						}
					?>
                    </select></td>
             </tr>
             <tr>
                <td align="right">To</td>
                <td><select class="add" name="toAccount">
                    <?
						$accounts = mysql_query("SELECT label, id FROM accounts", $dbh);
						while($account = @mysql_fetch_array($accounts))
						{
					?>
                    	<option value="<?=$account['id']?>"><?=$account['label']?></option>
                    <?	
						}
					?>
                    </select>
                </td>
            </tr>
            <tr>
            	<td align="right">Amount</td>
                <td><div class="input-box"><input type="text" id="amount" class="add" name="amount" /></div></td>
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
            	<td colspan="2"><input type="submit" name="subtransfer" value="Transfer" /><input type="button" class="buttons" action="item:discard" value="Discard" /></td>
            </tr>
		</table>
    </form>
    <br /><br />
</div>

<table width="100%" border="0" cellpadding="5" cellspacing="0" style="border-collapse:collapse">
	<tr>
    	<td colspan="4" align="right" style="padding:0px;text-align:left;height:60px">
        <span class="menucat" style="padding:0px 5px 0px 0px">
        	<span class="menucatlabel">Mark</span>
            <span action="items:all" class="buttons">All</span>
        </span>
        
        <span class="menucat">
            <span class="menucatlabel">Items</span>
            <span action="item:add" class="buttons">Add</span><span action="item:delete" class="buttons">Remove</span>
        </span>
        
        <span class="menucat">
        	<span class="menucatlabel">Transfer</span>
        	<span action="item:transfer" class="buttons">Money</span>
        </span>
        
        <span class="menucat">
        	<span class="menucatlabel">Show</span>
            <span action="show:today" class="buttons">Today</span><span action="show:dailytotal" class="buttons">Daily Total</span><span action="show:detailed" class="buttonsdis" rel="disable">Detailed</span>
        </span>
        
        <span class="menucat" style="border:0">
        	<span class="menucatlabel">More</span>
        	<span action="go:planner" class="buttons">Planner</span><span action="go:planner" class="buttons">Settings</span>
        </span>
       	</td>
        <td align="right" bgcolor="#FFFF66"><div class="detaileddaydisplay" style="display:none;"><?=formatMoney($d['pos'], ",.:\$number &euro;", 1)?><br /><span style='float:left;clear:left'>&mdash;</span><?=formatMoney($d['neg'], ",.:\$number &euro;", 1)?></div><?=formatMoney($d['tray'], ",.:\$number &euro;", 1)?></td>
        <td bgcolor="#FFCC33" width="13%">TOTAL</td>
    </tr>
    <!--
	<tr align="center" style="font-weight:bold; font-size:13px; text-transform:uppercase">
    	<td width="3%"><input type="checkbox" /></td>
        <td width="8%">Time</td>
        <td width="19%">Place</td>
        <td width="42%">Item</td>
        <td width="10%">Price</td>
        <td width="13%">Remarks</td>
    </tr>-->
    <?php
	$q = mysql_query("SELECT accounts.label, items.mode, items.quantity, items.id, places.store, places.city, places.country, places.street, items.tagIds, items.date, items.item, items.price, priceFormat.format, items.remarks FROM (places, items, priceFormat, accounts) WHERE items.showacc = '1' AND accounts.id = items.accountId AND places.id = items.placeId AND priceFormat.id = items.priceFormatId ORDER BY items.date DESC", $dbh);
	
	if(mysql_num_rows($q) > 0) {
	
	$id = 0;
	$itemid = 1;
	$itemlist = 0;
	$killday = 1;
	
	while($i = @mysql_fetch_array($q))
	{
	
		$datecode = strtotime($i['date']);
		$date = date("D, j M Y", $datecode);
		$time = date("h:i A", $datecode);
		
		// Date Codex : determines if it is still on the same date
			# determine date of the next id
		$r = mysql_fetch_array(mysql_query("SELECT COUNT(*) as count, DATE_FORMAT(items.date, '%e-%c-%Y') as date FROM items WHERE DATE_FORMAT(items.date, '%Y-%m-%d') = DATE_FORMAT('" . $i['date'] . "', '%Y-%m-%d') AND showacc = '1' GROUP BY DATE_FORMAT(items.date, '%e-%c-%Y')", $dbh));
		$thisday = $r['count'];
		
		// Determine Price Format
		$formatOptions = explode(":", $i['format']);
		
		if($i['mode'] == "expense")
		{
			// Day Sum POSITIVE
			$daysumPOS = $i['price'] >= 0 ? $daysumPOS + $i['price']:$daysumPOS;
		
			// Day Sum NEGATIVE
			$daysumNEG = $i['price'] < 0 ? $daysumNEG + $i['price']:$daysumNEG;
		}
		
		// Get Tags
		$tagIds = @explode(",", $i['tagIds']);
		$tags = @implode(", ", @array_map('getTagIdLabel', $tagIds));
		
    	// Mark the Next Days
		if($r['count'] > 0 && $killday == 1) {
    ?><tr bgcolor="#000000" style="border: 0px solid #333333;color: #FFFFFF; text-shadow: black 0.05em 0.05em 0.1em;">
    	<td colspan="5"><?=date("l", strtotime($r['date']))?></td><td align="right"><?=date("F j, Y", strtotime($r['date']))?></td>
    </tr><?
     $killday = 0; }  ?>
     
     <!-- Items -->
    <tr valign="top" style="border-bottom: 1px solid #999">
    	<td width="3%"><input type="checkbox" /> &diams;</td>
        <td width="8%" align="right"><?=$time?></td>
        <td width="19%" style="overflow:hidden; white-space:normal"><?=utf8_encode($i['store'])?> @ <?=utf8_encode($i['street'])?><div class="tags"><?=utf8_encode($i['city'])?>, <?=utf8_encode($i['country'])?></div></td>
        <td width="42%">
        	<div style="float:left;clear:left">
				<?=utf8_encode($i['item'])?>
                <div class="tags"><span class="accountItemLabel"><?=$i[label]?></span>, <?=$tags?></div>
            </div>
			
			<? if($i['quantity'] > 1) {?>
            	<div style="float:right;clear:right;width:20px;cursor:pointer;text-align:center;background:#66CCFF;padding:5px" name="indivprice<?=$itemlist?>" class="buttons" action="toggle:individualprices"><?=$i['quantity']?><div id="indivprice<?=$itemlist?>" style="font-size:12px;display:none"><?=formatMoney(abs($i['price']/$i['quantity']), $i['format'])?> each</div>
			<? } ?>
            <? if($i['mode'] == "transfer") {?>
            	<div style="float:right;width:20px;text-align:center;clear:right;background:#FFCC33;padding:5px">&hArr;</div>
			<? } ?>
            
        </td>
        <td width="10%" valign="middle" align="right" class="<?=($i['price'] < 0 ? "negprices" : "posprices") ?>"><?=formatMoney($i['price'], $i['format'])?></td>
        <td width="13%"><?=utf8_encode($i['remarks'])?></td>
    </tr><?
    	// Mark the Summations
		if($thisday == $itemid) {
    ?><tr style="border: 0px solid #333333">
    	<td colspan="4" id="day:<?=$id?>"></td><td style="padding: 0px; text-align: right;"><div class="detaileddaydisplay" style="display:none; padding: 2px"><?=formatMoney($daysumPOS, $i['format'], 1)?><br /><span style='float:left;clear:left'>&mdash;</span><?=formatMoney($daysumNEG, $i['format'], 1)?></div><div style="padding: 2px;" class="daydisplay"><input type="hidden" value="<?=formatMoney(($daysumNEG + $daysumPOS), $i['format'])?>" id="daytotal:<?=$id?>" /></div></td><td></td>
    </tr><?
     $killday = 1; $id++; $daysum = $daysumNEG = $daysumPOS = 0; $itemid = 0; /* Reset Counter */  }  $itemid++; $itemlist++;
     } } else {
    ?><tr valign="top">
    	<td colspan="6">Hey, there aren't any items! You might wanna start by adding one!</td>
    </tr>
    <? }
    ?><tr>
    	<td colspan="4" align="right" style="padding:0px;text-align:left"><span action="items:all" class="buttons">Check All</span>|<span action="item:add" class="buttons">Add Item</span><span action="item:delete" class="buttons">Delete Items</span>|<span action="item:transfer" class="buttons">Transfer Funds</span>|<span action="show:dailytotal" class="buttons">Show Daily Total</span><span action="show:detailed" class="buttonsdis" rel="disable">Show Detailed</span>|<span action="go:planner" class="buttons">Planner</span><span action="show:today" class="buttons">Today</span></td>
        <td align="right" bgcolor="#FFFF66"><div class="detaileddaydisplay" style="display:none;"><?=formatMoney($d['pos'], ",.:\$number &euro;", 1)?><br /><span style='float:left;clear:left'>&mdash;</span><?=formatMoney($d['neg'], ",.:\$number &euro;", 1)?></div><?=formatMoney($d['tray'], ",.:\$number &euro;")?></td>
        <td bgcolor="#FFCC33">TOTAL</td>
    </tr>
</table>
<br /><br /><br />copyright of javier onglao<br /><br />
</body>
</html>