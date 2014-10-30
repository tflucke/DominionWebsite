<!DOCTYPE HTML>
<html>
	<head>
		<?php require_once 'header.php'; ?>
		<title>Dominion Randomizer</title>
		<script type="text/javascript" src="scripts/cards.js"></script>
		<script type="text/javascript">
			$.dominion = {};
			
			function drawCards(table, cards)
			{
				if (typeof(cards) == typeof(undefined))
				{
					return;
				}
				table.html("");
				var row = $("<tr>").appendTo(table);
				var count = 0;
				cards.forEach(function(card)
					{
						$(card.drawTableCell(true, true)).appendTo(row);
						count++;
						if (count % 10 == 0)
							{
								row = $("<tr>").appendTo(table);
							}
					}
				);
				row.append($("<td />", {style : "width:*%;"}));
			}
			
			function loadCards()
				{
					var result = [];
					var dataArray = {};
					$("input").each(function()
					{
						var input = $(this);
						var name = typeof input.attr('name') === typeof undefined ? input.attr('id'):input.attr('name');
						if (input.is(":checkbox"))
						{
							if (input.is(":checked"))
							{
								dataArray[name] = "y";
							}
							else
							{
								dataArray[name] = "n";
							}
						}
						else
						{
							if (!input.is(":radio") || input.is(":checked"))
							{
								dataArray[name] = input.val();
							}
						}
						if (typeof(dataArray[name]) != typeof(undefined))
						{
							$.cookie(name, dataArray[name]);
						}
					});
					$.ajax(
					{
						type: "POST",
						url: "scripts/getCards.php",
						async: false,
						data: dataArray,
						cache: false
					})
					.done(function(data)
					{
						data = data.split(';');
						for (i = 0; i < data.length; i++)
						{
							result[i] = [];
							data[i] = data[i].split('\n');
							for (j=0; j<data[i].length; j++)
							{
								data[i][j] = data[i][j].split(',');
								var note = data[i][j].length > 4?data[i][j][4]:null;
								result[i][j] = new Card(
									data[i][j][0],
									parseInt(data[i][j][1]),
									data[i][j][2],
									parseInt(data[i][j][3]),
									note
								);
							}
						}
					});
					return result;
				}
			function drawAllCards()
			{
				drawCards($("#cardTable"), $.dominion.kingdoms);
				drawCards($("#cardTableNonKingdom"), $.dominion.nonkingdoms);
			}
			function sortAllCards()
			{
				var sortArray = new Array();
				var i = 0;
				$("#sortBy li").each(function() {sortArray[i++] = $(this).text();});

				$.dominion.kingdoms = sortCards($.dominion.kingdoms, sortArray);
				$.dominion.nonkingdoms = sortCards($.dominion.nonkingdoms, sortArray);
				$.cookie("order", sortArray.join("->"));
				
			}
			$(document).ready(function()
				{
					$("#content").masonry();
					$("#loadButton").bind("click", function()
					{
						var cards = loadCards();
						$.dominion.kingdoms = cards[0];
						$.dominion.nonkingdoms = cards[1];
						$.dominion.blackMarketDeck = cards[2];
						sortAllCards();
						drawAllCards();
						if (typeof($.dominion.blackMarketDeck) !== typeof(undefined))
						{
							$("#blackMarketInterface").css("display", "block");
						}
							
					});
					$(".promoCard").bind("change", function()
					{
						if ($( this ).is(":checked"))
						{
							if (!$("#promCheck").is(":checked"))
							{
								$("#promCheck").prop('checked', true);
							}
						}
						else
						{
							if ($(".promoCard:checked").length == 0)
							{
								$("#promCheck").prop('checked', false);
							}
						}
					});
					$("#promCheck").bind("change", function()
					{
						if ($( this ).is(":checked"))
						{
							$(".promoCard").prop('checked', true);
						}
						else
						{
							$(".promoCard").prop('checked', false);
						}
					});
					$("#limitAlchCheck").bind("change", function()
					{
						if ($( this ).is(":checked"))
						{
							$("#alchCheck").prop('checked', true);
						}
					});
					$("#alchCheck").bind("change", function()
					{
						if (!$( this ).is(":checked"))
						{
							$("#limitAlchCheck").prop('checked', false);
						}
					});
					$("#platColoTogether").bind("change", function()
					{
						if ($(this).is(':checked'))
						{
							$("#useBoth").css("display", "block");
							$("#usePlat").css("display", "none");
							$("#useColo").css("display", "none");
						}
						else
						{
							$("#useBoth").css("display", "none");
							$("#usePlat").css("display", "block");
							$("#useColo").css("display", "block");
						}
						$("#content").masonry();
					});
					if (typeof($.cookie("order")) != typeof(undefined))
					{
						var order = $.cookie("order").split("->");
						$("#sortBy").html("");
						for (var i = 0; i < order.length; i++)
						{
							$("#sortBy").append("<li>"+order[i]+"</li>");
						}
					}
					$("#sortBy").sortable()
						.disableSelection()
						.bind("sortupdate", function(event, ui)
						{
							sortAllCards();
							drawAllCards();
						})
						.find("li")
							.prepend(
									$("<span />", {class: "ui-icon ui-icon-arrowthick-2-n-s"})
									.css("display", "inline-block")
							);
					$("input").each(function()
					{
						var input = $(this);
						var name = typeof input.attr('name') === typeof undefined ? input.attr('id'):input.attr('name');
						if (typeof($.cookie(name)) != typeof(undefined))
						{
							if (input.is(":checkbox"))
							{
								input.prop('checked', $.cookie(name) == "y");
							}
							else if (input.is(":radio"))
							{
								input.prop('checked', $.cookie(name) == input.val());
							}
							else if (!input.is(":button"))
							{
								input.val($.cookie(name));
							}
							input.change();
						}
					});
				}
			);
		</script>
	</head>
	<body>
		<h1>Dominion Utility Dev</h1>
		<div style="width: 100%;">
			<form id="content" name="content" style="max-width: 1100px; display: block;">
				<div id="sets" class="border">
					<input type="button" value="Check All" onclick="$('#sets input').each(function() {this.checked = true;});" />
					<input type="button" value="Check None" onclick="$('#sets input').each(function() {this.checked = false;});" /><br />
					<input type="checkbox" id="baseCheck" /><label>Base</label><br />
					<input type="checkbox" id="intrCheck" /><label>Intrigue</label><br />
					<input type="checkbox" id="seasCheck" /><label>Seaside</label><br />
					<input type="checkbox" id="alchCheck" /><label>Alchemy</label><br />
					<div class="subset">
						<input id="limitAlchCheck" type="checkbox" name="alchLimit" /><label>3-5 Alchemy if used</label><br />
					</div>
					<input type="checkbox" id="prosCheck" /><label>Prosperity</label><br />
					<input type="checkbox" id="cornCheck" /><label>Cornucopia</label><br />
					<input type="checkbox" id="hintCheck" /><label>Hinterlands</label><br />
					<input type="checkbox" id="darkCheck" /><label>Dark Ages</label><br />
					<input type="checkbox" id="guilCheck" /><label>Guilds</label><br />
					<input type="checkbox" id="promCheck" /><label>Promo</label><br />
					<div class="subset">
						<input type="checkbox" id="blaMarCheck" class="promoCard" /> <label>Black Market</label><br />
						<input type="checkbox" id="envoCheck" class="promoCard" /> <label>Envoy</label><br />
						<input type="checkbox" id="stasCheck" class="promoCard" /> <label>Stash</label><br />
						<input type="checkbox" id="wallCheck" class="promoCard" /> <label>Walled Village</label><br />
						<input type="checkbox" id="goveCheck" class="promoCard" /> <label>Governor</label><br />
					</div>
				</div>
				<div class="border">
					<label for="countInput">Card Count: </label>
					<div style="float: right; position: relative;">
						<input type="number" id="countInput" value="10" max="205" min="1" />
					</div>
					<br />
					<div class="note">
						Note: <br />
						Dominion is meant to be<br />
						played with 10 kingdom cards.
					</div>
				</div>
				<div class="border">
					Select from: <br />
					<input id="SelectAll" type="radio" name="selectFrom" value="all" checked />
					<label for="SelectAll">All Selected</label><br />
					<input id="SelectFixed" type="radio" name="selectFrom" value="someFixed" min="1" max="10" maxlength="2" />
					<input type="number" name="selectFixedAmount" value="3" min="1" max="10" maxlength="2" size="2" />
					<label for="SelectFixed">  of Selected</label><br />
					<input id="SelectRandom" type="radio" name="selectFrom" value="someRandom" />
					<label for="SelectRandom">Random Number of Selected</label>
				</div>
				<div class="border">
					<label for="playerCount">Player Count: </label>
					<div style="float: right; position: relative;">
						<input type="number" id="playerCount" value="3" max="6" min="2" />
					</div>
				</div>
				<div class="border">
					<label for="autoBlackMarket">Virtual Black Market: </label>
					<input type="checkbox" id="autoBlackMarket" />
				</div>
				<div class="border polydiv" style="max-width: 684px">
					<label for="platColoTogether">Use Platinum and Colony together: </label>
					<input type="checkbox" id="platColoTogether" name="platColoTogether" /><br />
					<div id="useBoth" style="display: none;">
						<h3>Use Platinum/Colony: </h3>
						<input type="radio" name="platColo" value="always" checked /><label>Always</label><br />
						<input type="radio" name="platColo" value="percent" />
						<input type="number" name="platColoPercent" value="50" min="1" max="99" maxlength="2" /><label>of the Time</label><br />
						<input type="radio" name="platColo" value="proportion" /><label>Proportional to Prosperity</label><br />
						<input type="radio" name="platColo" value="never" /><label>Never</label>
					</div>
					<div id="usePlat">
						<h3>Use Platinum: </h3>
						<input type="radio" name="platinum" value="always" checked /><label>Always</label><br />
						<input type="radio" name="platinum" value="percent" />
						<input type="number" name="platPercent" value="50" min="1" max="99" maxlength="2" /><label>of the Time</label><br />
						<input type="radio" name="platinum" value="proportion" /><label>Proportional to Prosperity</label><br />
						<input type="radio" name="platinum" value="never" /><label>Never</label>
					</div>
					<div id="useColo">
						<h3>Use Colony: </h3>
						<input type="radio" name="colony" value="always" checked /><label>Always</label><br />
						<input type="radio" name="colony" value="percent" />
						<input type="number" name="coloPercent" value="50" min="1" max="99" maxlength="2" /><label>of the Time</label><br />
						<input type="radio" name="colony" value="proportion" /><label>Proportional to Prosperity</label><br />
						<input type="radio" name="colony" value="never" /><label>Never</label>
					</div>
					<div id="useColo">
						<h3>Use Shelters: </h3>
						<input type="radio" name="shelters" value="always" checked /><label>Always</label><br />
						<input type="radio" name="shelters" value="percent" />
						<input type="number" name="shelPercent" value="50" min="1" max="99" maxlength="2" /><label>of the Time</label><br />
						<input type="radio" name="shelters" value="proportion" /><label>Proportional to Dark Ages</label><br />
						<input type="radio" name="shelters" value="never" /><label>Never</label>
					</div>
				</div>
				<div class="border">
					<h2>
						To Do List:
					</h2>
					<ol>
						<li>Virtual Black Market Deck</li>
						<li>Autoselect Bane</li>
						<li>Require cards</li>
						<li>Clear Cookie Button</li>
					</ol>
				</div>
				<div class="border">
					<div style="float: left;">
						Sort by: 
						<ul id="sortBy" style="list-style-type: none; margin: 0; padding: 0;">
							<li>Name</li>
							<li>Set</li>
							<li>Cost</li>
						</ul>
					</div>
				</div>
				<div class="border">
					<input type="button" id="loadButton" value="Load Cards" />
				</div>
			</form>
			<div class="border" id="blackMarketInterface" style="display: none;">
				<table>
					<tr>
						<td class="cardCell">
							<img alt="Black Market Deck" src="images/promo/BlackMarketDeck.jpg" />
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div>
			<table id="cardTable"></table>
			<table id="cardTableNonKingdom"></table>
		</div>
	</body>
</html>
