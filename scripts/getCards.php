<?php
// require_once 'debug.php';
require_once 'mysql.php';
session_start();
$con = new DominionConnection();
if ($con !== false)
{
	$setArray = array ();
	$bannedArray = array ();
	
	if ($_POST ['baseCheck'] == "y")
	{
		$setArray [] = "Dominion";
	}
	if ($_POST ['intrCheck'] == "y")
	{
		$setArray [] = "Intrigue";
	}
	if ($_POST ['seasCheck'] == "y")
	{
		$setArray [] = "Seaside";
	}
	if ($_POST ['alchCheck'] == "y")
	{
		$setArray [] = "Alchemy";
	}
	if ($_POST ['prosCheck'] == "y")
	{
		$setArray [] = "Prosperity";
	}
	if ($_POST ['cornCheck'] == "y")
	{
		$setArray [] = "Cornucopia";
	}
	if ($_POST ['hintCheck'] == "y")
	{
		$setArray [] = "Hinterlands";
	}
	if ($_POST ['darkCheck'] == "y")
	{
		$setArray [] = "Dark Ages";
	}
	if ($_POST ['guilCheck'] == "y")
	{
		$setArray [] = "Guilds";
	}
	if ($_POST ['promCheck'] == "y")
	{
		// If Promo is selected, build the promo list
		$setArray [] = "Promo";

		if ($_POST ['blaMarCheck'] == "n")
		{
			$bannedArray [] = "Black Market";
		}
		if ($_POST ['envoCheck'] == "n")
		{
			$bannedArray [] = "Envoy";
		}
		if ($_POST ['stasCheck'] == "n")
		{
			$bannedArray [] = "Stash";
		}
		if ($_POST ['wallCheck'] == "n")
		{
			$bannedArray [] = "Walled Village";
		}
		if ($_POST ['goveCheck'] == "n")
		{
			$bannedArray [] = "Governor";
		}
	}
	if ($_POST['selectFrom'] != "all")
	{
		$numToUse;
		if ($_POST['selectFrom'] == "someFixed")
		{
			$numToUse = $_POST['selectFixedAmount'];
		}
		else
		{
			$numToUse = mt_rand(1, count($setArray));
		}
		
		$key = array_search("Promo", $setArray);
		if ($numToUse == 1 && $key !== false)
			{
				unset($setArray[$key]);
				$setArray = array_values($setArray);
			}
		
		while (count($setArray) > $numToUse)
			{
				unset($setArray[mt_rand(0, count($setArray) - 1)]);
				$setArray = array_values($setArray);
			}
	}
	
	$setsWhere = implode("', '", $setArray);
	
	if (strlen($setsWhere) > 0)
	{
		$setsWhere = "'" . $setsWhere . "'";
	}
	
	$cardCount = $_POST ['countInput'];

	$banWhere = "";
	if (count($bannedArray) > 0)
	{
		$bannedArray = $con->getIds($bannedArray);
		$banWhere = "id NOT IN(" . implode(", ", $bannedArray) . ")";
	}
	
	// Get the random ids
	// $cardArray = array_merge($cardArray, getRandom($con, $setsWhere, (strlen($where)>0?"$where ".(count($cardArray)>0?"AND ":""):"").(count($cardArray)>0?"id NOT IN(".implode(", ", $cardArray).")":""), $cardCount));
	$cardArray = $con->getRandom($setsWhere, $banWhere, $cardCount);
	
	$finalStr = "";


	$usePlatinums = false;
	$useColonies = false;
	$useShelters = false;
	if (count($cardArray) > 0)
	{
		$prosperityCount = 0;
		$darkAgesCount = 0;
		
		$result = $con->getCards("id IN(" . implode(", ", $cardArray) . ")");
		foreach ( $result as $value )
		{
			$finalStr .= $value ['cardName'] . "," . $value ['setId'] . "," . $value ['setName'] . "," . $value ['cost'];
			if ($value ['isVictory'])
			{
				$finalStr .= "," . ($_POST ['playerCount'] == 2 ? "8" : "12");
			}
			$finalStr .= "\n";

			if ($value ['setId'] == 5)
			{
				$prosperityCount++;
			}
			else if ($value ['setId'] == 8)
			{
				$darkAgesCount++;
			}
		}
		
		if ($_POST ['platColoTogether'] == "y")
		{
			if ($_POST ['platColo'] == "percent")
			{
				$useColonies = mt_rand(0, 99) < $_POST ['platColoPercent'];
			}
			else if ($_POST ['platColo'] == "proportion")
			{
				$useColonies = mt_rand(0, 9) < $prosperityCount;
			}
			else
			{
				$useColonies = $_POST ['platColo'] == "always";
			}
			$usePlatinums = $useColonies;
		}
		else
		{
			if ($_POST ['platinum'] == "percent")
			{
				$usePlatinums = mt_rand(0, 99) < $_POST ['platPercent'];
			}
			else if ($_POST ['platinum'] == "proportion")
			{
				$usePlatinums = mt_rand(0, 9) < $prosperityCount;
			}
			else
			{
				$usePlatinums = $_POST ['platinum'] == "always";
			}

			if ($_POST ['colony'] == "percent")
			{
				$useColonies = mt_rand(0, 99) < $_POST ['coloPercent'];
			}
			else if ($_POST ['colony'] == "proportion")
			{
				$useColonies = mt_rand(0, 9) < $prosperityCount;
			}
			else
			{
				$useColonies = $_POST ['colony'] == "always";
			}
		}
		
		if ($_POST ['shelters'] == "percent")
		{
			$useShelters = mt_rand(0, 99) < $_POST ['shelPercent'];
		}
		else if ($_POST ['shelters'] == "proportion")
		{
			$useShelters = mt_rand(0, 9) < $darkAgesCount;
		}
		else
		{
			$useShelters = $_POST ['shelters'] == "always";
		}
	}
	$finalStr = substr($finalStr, 0, -1);
	$finalStr .= ";";

	$doubleTreasure = $_POST ['playerCount'] >= 5;
	$extraVictory = $_POST ['playerCount'] > 2;
	$finalStr .= "Copper,1,Dominion,0".($doubleTreasure?",x2":"");
	$finalStr .= "\nSilver,1,Dominion,3".($doubleTreasure?",x2":"");
	$finalStr .= "\nGold,1,Dominion,6".($doubleTreasure?",x2":"");
	if ($usePlatinums)
	{
		$finalStr .= "\nPlatinum,5,Prosperity,9";
	}
	$finalStr .= "\nEstate,1,Dominion,2,".($extraVictory?"12":"8");
	if (!$useShelters)
	{
		$finalStr .= "+".$_POST ['playerCount']."x3";
	}
	$finalStr .= "\nDuchy,1,Dominion,5,".($extraVictory?"12":"8");
	$finalStr .= "\nProvince,1,Dominion,8,".($extraVictory?($_POST ['playerCount'] >= 5?$_POST ['playerCount']*3:"12"):"8");
	if ($useColonies)
	{
		$finalStr .= "\nColony,5,Prosperity,11,".($extraVictory?"12":"8");
	}
	$finalStr .= "\nCurse,1,Dominion,0,".(10 * ($_POST['playerCount'] - 1));
	if ($useShelters)
	{
		$finalStr .= "\nShelters,8,Dark Ages,1,".$_POST ['playerCount']."x3";
	}
		
	$required = $con->getCards("id IN(" . implode(", ", $con->getRequired($cardArray)) . ")", false);
	
	//Handle black market deck if set to
	$needBlackMarket = false;
	if ($_POST ['autoBlackMarket'] == "y")
	{
		for ($i = 0; $i < count($required); $i++)
		{
			if ($required[$i]['cardName'] == "Black Market Deck")
			{
				$needBlackMarket = true;
				unset($required[$i]);
				$setArray = array_values($required);
				break;
			}
		}
	}
	
	foreach ( $required as $value )
	{
		$finalStr .= "\n" . $value ['cardName'] . "," . $value ['setId'] . "," . $value ['setName'] . "," . $value ['cost'];
	}
	
	if ($needBlackMarket)
	{
		$deck = $con->getCards("id NOT IN(" . implode(", ", $cardArray) . ")", true, $setsWhere);
		$finalStr .= ";";
		foreach ( $deck as $value )
		{
			$finalStr .= $value ['cardName'] . "," . $value ['setId'] . "," . $value ['setName'] . "," . $value ['cost'] . "\n";
		}
		$finalStr = substr($finalStr, 0, -1);
	}
	
	echo $finalStr;
}
else
{
	echo "Failed!";
}

/*
 * define("baneId", executeQuery($con, "SELECT id FROM nonKingdom WHERE name='Bane';")[0]['id']); define("youngWitchId", executeQuery($con, "SELECT id FROM kingdomCards WHERE name='Young Witch';")[0]['id']); define("blackMarketDeckId", executeQuery($con, "SELECT id FROM nonKingdom WHERE name='Black Market Deck';")[0]['id']); define("alchemyId", executeQuery($con, "SELECT id FROM sets WHERE name='Alchemy';")[0]['id']); $bane = true; $blackMarket = true; function getRequired($con, $where) {//Get the required cards from the database return executeQuery($con, "SELECT tmpRequired.id FROM ( ( (SELECT DISTINCT n.id, n.setId FROM nonKingdom n) AS tmpRequired INNER JOIN ( (SELECT k.requires1 AS required FROM kingdomCards k WHERE ".(strlen($where)>0?"$where AND ":"")."k.requires1 IS NOT NULL) UNION (SELECT k.requires2 AS required FROM kingdomCards k WHERE ".(strlen($where)>0?"$where AND ":"")."k.requires2 IS NOT NULL) ) AS tmpCards ON tmpCards.required = tmpRequired.id ) JOIN (SELECT id, name FROM sets s) AS tmpSets ON tmpRequired.setId = tmpSets.id) GROUP BY tmpRequired.id;" ); } function fillRequired($con, $setWhere, &$ids, $where) {//Fill the an array with ids of all the required cards $i = 0; $required = array(); $result = getRequired($con, "id IN(" . implode(", ", $ids) . ")"); foreach ($result as $value) { if ($value['id'] == baneId && isset($_POST['autoBane'])) {//What if array returns nothing? $GLOBALS['bane'] = getRandom($con, $setWhere, (isset($where) && strlen($where)>0?"$where AND ":"")."(id NOT IN(".implode(", ", $ids).")) AND cost IN(2, 3) ", 1); if (count($GLOBALS['bane']) == 0) { $baneResult = getRandom($con, $setWhere, (isset($where) && strlen($where)>0?"$where AND ":"")."(id IN(".implode(", ", $ids).")) AND cost IN(2, 3) ", 1); if ($baneResult !== false) { $GLOBALS['bane'] = $baneResult; $ids[array_search($GLOBALS['bane'][0], $ids)] = getRandom($con, $setWhere, (isset($where) && strlen($where)>0?"$where AND ":"")."(id NOT IN(".implode(", ", $ids)."))", 1)[0]; } else {echo "alert('Could not find a proper bane.');";} } $GLOBALS['bane'] = $GLOBALS['bane'][0]; $baneArray = array($GLOBALS['bane']); $required = array_merge($required, fillRequired($con, $setWhere, $baneArray, $where)); } else if ($value['id'] == blackMarketDeckId && isset($_POST['virtualBlackMarket'])) { if (false !== strpos($setWhere, "'Cornucopia'") && !in_array(youngWitchId, $ids)) { $ids[] = youngWitchId; $required = fillRequired($con, $setWhere, $ids, $where); unset($ids[array_search(youngWitchId, $ids)]); $ids = array_values($ids); $GLOBALS['blackMarket'] = getCards($con, "id NOT IN(".implode(", ", $ids).")".($GLOBALS['bane']!=true?" AND id != $bane":""), true, $setWhere); break; } else {$GLOBALS['blackMarket'] = getCards($con, "id NOT IN(".implode(", ", $ids).")".($GLOBALS['bane']!=true?" AND id != $bane":""), true, $setWhere);} } else {$required[] = $value['id'];} } return $required; } //Build set where //Build set array $setArray = array(); $promoArray = array(); //Pick which sets to use if (isset($_POST['selectFrom']) && $_POST['selectFrom'] != "all") { $numToUse; if ($_POST['selectFrom'] == "someFixed") {$numToUse = $_POST['setCount'];} else {$numToUse = mt_rand(1, count($setArray));} $key = array_search("'Promo'", $setArray); if ($numToUse == 1 && $key !== false) { unset($setArray[$key]); $setArray = array_values($setArray); } while (count($setArray) > $numToUse) { unset($setArray[mt_rand(0, count($setArray) - 1)]); $setArray = array_values($setArray); } } //Prevent unselected promo cards from being chosen $where = ""; if (count($promoArray) > 0) {$where = "(setId != 10 OR name IN(".implode(", ", $promoArray).")) ";} //Fetch the ids of the requested cards //Prep to get the cards $cardCount = isset($_POST['cardNum'])?$_POST['cardNum']:0; $cardArray = array(); //Get the cards $requestedArray = array(); foreach ($_POST as $key => $value) { if (substr($key, 0, strlen("required-")) == "required-" && $value != '') {$requestedArray[] = $value;} } $alchRequested = 0; if (count($requestedArray) > 0) { $result = getCards($con, "name IN('" . implode("', '", $requestedArray) . "')"); foreach ($result as $value) { $cardArray[] = $value['id']; if ($value['setId'] == 4) {$alchRequested++;} $cardCount--; } } $alchIndex = array_search("'Alchemy'", $setArray); if ($alchIndex !== false && isset($_POST['alchLimit'])) { $alchCount = mt_rand(min(3, $cardCount), min(5, $cardCount)); $alchCount = max($alchCount - $alchRequested, 0); $result = getRandom($con, "id=".alchemyId, (count($cardArray)>0?"id NOT IN(".implode(", ", $cardArray).")":""), $alchCount); foreach ($result as $value) { $cardArray[] = $value; $cardCount--; } unset($setArray[$alchIndex]); $setArray = array_values($setArray); } $setsWhere = "name IN(".implode(", ", $setArray).")"; //Get the random ids $cardArray = array_merge($cardArray, getRandom($con, $setsWhere, (strlen($where)>0?"$where ".(count($cardArray)>0?"AND ":""):"").(count($cardArray)>0?"id NOT IN(".implode(", ", $cardArray).")":""), $cardCount)); //Get cards for the required ids $requiredArray = fillRequired($con, $setsWhere, $cardArray, $where); //Print the result to the Javascript if (count($cardArray)>0) { $result = getCards($con, "id IN(".implode(", ", $cardArray).")"); $i = 0; foreach ($result as $value) {echo "cards[" . $i++ . "] = new Card('" . str_replace("'", "\'", $value['cardName']) . "', " . $value['setId'] . ", '" . $value['setName'] . "', " . $value['cost'] . ($value['isVictory']?($_POST['playerNum'] == 2?", '8'":", '12'"):"") .");\n";} } $i = 0; if ($bane !== true) { $result = getCards($con, "id=$bane", true)[0]; echo "required[" . $i++ . "] = new Card('" . str_replace("'", "\'", $result['cardName']) . "', " . $result['setId'] . ", '" . $result['setName'] . "', " . $result['cost'] . ", 'Bane');\n"; } $treasureNote = ($_POST['playerNum'] >= 5?", 'x2'":""); echo "required[" . $i++ . "] = new Card('Copper', 1, 'Dominion', 0$treasureNote);\n"; echo "required[" . $i++ . "] = new Card('Silver', 1, 'Dominion', 3$treasureNote);\n"; echo "required[" . $i++ . "] = new Card('Gold', 1, 'Dominion', 6$treasureNote);\n"; echo "required[" . $i++ . "] = new Card('Curse', 1, 'Dominion', 0, '".(10 * ($_POST['playerNum'] - 1))."');\n"; $darkAgesCount; if (isset($_POST['dark']) && $_POST['shelters'] == "proportion") {$darkAgesCount = executeQuery($con, "SELECT count(*) FROM kingdomCards WHERE id IN(".implode(", ", $cardArray).") AND setId=8 GROUP BY setId;")[0]['count(*)'];} $useShelters = false; if ($_POST['shelters'] == "always" || ($_POST['shelters'] == "percent" && mt_rand(1, 100) <= intval($_POST['shelPercent'])) || ($_POST['shelters'] == "proportion" && mt_rand(1, intval($_POST['cardNum'])) <= $darkAgesCount)) {$useShelters = true;} if ($useShelters) { echo "required[" . $i++ . "] = new Card('Estate', 1, 'Dominion', 2, '".($_POST['playerNum']>2?"12":"8")."');\n"; echo "required[" . $i++ . "] = new Card('Shelters', 8, 'Dark Ages', 1, '".$_POST['playerNum']." Sets');\n"; } else {echo "required[" . $i++ . "] = new Card('Estate', 1, 'Dominion', 2, '".($_POST['playerNum']>2?"12":"8")." + 3x".$_POST['playerNum']."');\n";} if ($_POST['playerNum'] > 2) { echo "required[" . $i++ . "] = new Card('Duchy', 1, 'Dominion', 5, '12');\n"; if ($_POST['playerNum'] == 5) {echo "required[" . $i++ . "] = new Card('Province', 1, 'Dominion', 8, '15');\n";} else if ($_POST['playerNum'] == 6) {echo "required[" . $i++ . "] = new Card('Province', 1, 'Dominion', 8, '18');\n";} else {echo "required[" . $i++ . "] = new Card('Province', 1, 'Dominion', 8, '12');\n";} } else { echo "required[" . $i++ . "] = new Card('Duchy', 1, 'Dominion', 5, '8');\n"; echo "required[" . $i++ . "] = new Card('Province', 1, 'Dominion', 8, '8');\n"; } $prosperityCount; if (isset($_POST['pros']) && ($_POST['platinum'] == "proportion" || $_POST['colony'] == "proportion")) {$prosperityCount = executeQuery($con, "SELECT count(*) FROM kingdomCards WHERE id IN(".implode(", ", $cardArray).") AND setId=5 GROUP BY setId;")[0]['count(*)'];} if (isset($_POST['platColoTogether'])) { if ($_POST['platColo'] == "always" || ($_POST['platColo'] == "percent" && mt_rand(1, 100) <= intval($_POST['platColoPercent'])) || ($_POST['platColo'] == "proportion" && mt_rand(1, intval($_POST['cardNum'])) <= $prosperityCount)) { echo "required[" . $i++ . "] = new Card('Platinum', 5, 'Prosperity', 9);\n"; echo "required[" . $i++ . "] = new Card('Colony', 5, 'Prosperity', 11, ".($_POST['playerNum']>2?"'12'":"'8'").");\n"; } } else { if ($_POST['platinum'] == "always" || ($_POST['platinum'] == "percent" && mt_rand(1, 100) <= intval($_POST['platPercent'])) || ($_POST['platinum'] == "proportion" && mt_rand(1, intval($_POST['cardNum'])) <= $prosperityCount)) {echo "required[" . $i++ . "] = new Card('Platinum', 5, 'Prosperity', 9);\n";} if ($_POST['colony'] == "always" || ($_POST['colony'] == "percent" && mt_rand(1, 100) <= intval($_POST['coloPercent'])) || ($_POST['colony'] == "proportion" && mt_rand(1, intval($_POST['cardNum'])) <= $prosperityCount)) {echo "required[" . $i++ . "] = new Card('Colony', 5, 'Prosperity', 11, ".($_POST['playerNum']>2?"'12'":"'8'").");\n";} } if (count($requiredArray)>0) { $result = getCards($con, "id IN(".implode(", ", $requiredArray).")", false); foreach ($result as $value) {echo "required[" . $i++ . "] = new Card('" . str_replace("'", "\'", $value['cardName']) . "', " . $value['setId'] . ", '" . $value['setName'] . "', " . $value['cost'] . ");\n";} } if ($blackMarket !== true) { shuffle($blackMarket); $i = 0; foreach ($blackMarket as $value) {echo "blackMarketDeck[" . $i++ . "] = new Card('" . str_replace("'", "\'", $value['cardName']) . "', " . $value['setId'] . ", '" . $value['setName'] . "', " . $value['cost'] . ");\n";} }
 */
?>