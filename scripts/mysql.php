<?php
	require_once 'connectionConstants.php';
	class DominionConnection
		{
			private $pdo;
			
			public function __construct()
				{
					try
					{
						$this->pdo = new PDO(conString, usernane, password);
					}
					catch (PDOException $pdoe)
					{
						echo $pdoe->getMessage();
						$this->pdo = false;
						return false;
					}
				}
			
			private function exec($query)
				{//Generic function to run a query and return an array of results
					$result = $this->pdo->query($query);
					$resultArray = array();
					if ($result != false)
						{
							foreach ($result as $row)
							{
								$resultArray[] = $row;
							}
						}
					return $resultArray;
				}

			public function getRandom($sets="", $where="", $cardCount=10)
				{//Get a number of random cards from the database
					$array = $this->exec(
						"SELECT cards.id FROM
						(
							(SELECT id, setId FROM kingdomCards k".(strlen($where)>0?" WHERE $where":"").") AS cards
						JOIN
							(SELECT id, name FROM sets s ".(strlen($sets)>0?" WHERE name IN($sets)":"").") AS sets
						ON cards.setId = sets.id)
						ORDER BY RAND() LIMIT $cardCount;"
					);
					for ($i = 0; $i < count($array); $i++)
						{$array[$i] = $array[$i][0];}
					return $array;
				}
				
			public function getCards($where, $kingdom = true, $sets = "")
				{//Get card information from the database
					return $this->exec(
						"SELECT cards.id, cards.name AS cardName, cards.setId, cards.cost, (cards.type&b'10000'=b'10000') AS isVictory, sets.name AS setName FROM
							(
								(SELECT k.id, k.name, k.setId, k.cost, k.type FROM ".($kingdom?"kingdomCards":"nonKingdom")." k".(strlen($where)>0?" WHERE $where":"").") AS cards
							JOIN
								(SELECT id, name FROM sets s".(strlen($sets)>0?" WHERE name IN($sets)":"").") AS sets
							ON cards.setId = sets.id);"
						);
				}
			public function getIds($names, $kingdom = true)
				{//Get card information from the database
					$array = $this->exec(
						"SELECT id FROM ".($kingdom?"kingdomCards":"nonKingdom")." WHERE name in('".implode("', '", $names)."');"
						);
					for ($i = 0; $i < count($array); $i++)
						{$array[$i] = $array[$i][0];}
					return $array;
				}
			
			public function getRequired($cards)
				{//Get the required cards from the database
					$where = "id IN(".implode(", ", $cards).")";
					$array = $this->exec(
						"SELECT tmpRequired.id FROM
							(
								(
										(SELECT DISTINCT n.id, n.setId FROM nonKingdom n) AS tmpRequired
									INNER JOIN
										(
												(SELECT k.requires1 AS required FROM kingdomCards k WHERE $where AND k.requires1 IS NOT NULL)
											UNION
												(SELECT k.requires2 AS required FROM kingdomCards k WHERE $where AND k.requires2 IS NOT NULL)
										) AS tmpCards
									ON tmpCards.required = tmpRequired.id
								)
							JOIN
								(SELECT id, name FROM sets s) AS tmpSets
							ON tmpRequired.setId = tmpSets.id) GROUP BY tmpRequired.id;"
					);
					for ($i = 0; $i < count($array); $i++)
						{$array[$i] = $array[$i][0];}
					return $array;
				}
		}
?>