<?php

// connexion
$SQLhost = // deleted;
$DBname = // deleted;
$username = // deleted;
$password = // deleted;

$bdd = new PDO("mysql:host=$SQLhost; dbname=$DBname; charset=utf8", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
include "product.php";

// get price
function getprice($bdd, $product) {
	$reponse = $bdd->query("SELECT id FROM `users` ORDER BY id DESC LIMIT 1");
	while ($donnees = $reponse->fetch()) {
		$numberOfUsers = intval($donnees["id"]);
	}
	$TotalOfProduct = 0;
	$reponse = $bdd->query("SELECT actions FROM `users`");
	while ($donnees = $reponse->fetch()) {
		$TotalOfProduct = $TotalOfProduct + unserialize($donnees["actions"])[$product];
	}
	return $TotalOfProduct / $numberOfUsers;
}


// generate JSON file
$x = 1;
$totalArray = [];
$value = 0;
while (isset($products[$x])) {
	$value = $value + getprice($bdd, $products[$x]);
	array_push($totalArray, getprice($bdd, $products[$x]));
	$x++;
}



// reverse the array
$total = [];
$reponse = $bdd->query("SELECT value, id FROM `courbeLog` ORDER BY id DESC LIMIT 500");
while ($donnees = $reponse->fetch()) {
	array_push($total, $donnees['value']);
	$x = $donnees['id'];
}
$total = array_reverse($total);



// delete useless data
$req = $bdd->prepare("DELETE FROM `courbeLog` WHERE id < :x");
$req->bindParam(':x', $x);
$req->execute();


// get chat id
$reponse = $bdd->query("SELECT id FROM `chat` ORDER BY id DESC LIMIT 1");
while ($donnees = $reponse->fetch()) {
	$chat = $donnees["id"];
}





// update stock
function updateCour($bdd, $value, $products) {
	// get the number of users
	$reponse = $bdd->query("SELECT id FROM `users` ORDER BY id DESC LIMIT 1");
	while ($donnees = $reponse->fetch()) {
		$numberOfUsers = $donnees["id"];
	}

	// get the bank account
	$reponse = $bdd->query("SELECT actions FROM `users` WHERE password='dd94709528bb1c83d08f3088d4043f4742891f4f' AND user='banque'");
	while ($donnees = $reponse->fetch()) {
		$compte = unserialize($donnees["actions"]);
	}


	// get the evolution value
	$reponse = $bdd->query("SELECT * FROM `courbe`");
	while ($donnees = $reponse->fetch()) {
		$product = $donnees["product"];
		$min = $donnees["min"];
		$max = $donnees["max"];

		// update user account
		$compte[$product] = $compte[$product] + rand($min, $max) * $numberOfUsers;
		if ($compte[$product] < 0) {
			
			// raise inflation if the price is negative
			if ($max + $min < 1) {
				$max = $max + 0.5;
				$min = $min + 0.5;
				$bdd->exec("UPDATE `courbe` SET `max`='$max',`min`='$min' WHERE product='$product'");

				// compensate on another action
				$product = $products[rand(1, count($products))];
				$max = $max - 0.5;
				$min = $min - 0.5;
				$bdd->exec("UPDATE `courbe` SET `max`='$max',`min`='$min' WHERE product='$product'");
			}

		} elseif ($compte[$product] > 6000) {
			
			// reduce inflation if the price is too high
			if ($max + $min > -1) {
				$max = $max - 0.5;
				$min = $min - 0.5;
				$bdd->exec("UPDATE `courbe` SET `max`='$max',`min`='$min' WHERE product='$product'");

				// compensate on another action
				$product = $products[rand(1, count($products))];
				$max = $max + 0.5;
				$min = $min + 0.5;
				$bdd->exec("UPDATE `courbe` SET `max`='$max',`min`='$min' WHERE product='$product'");
			}
		}
	}
	$compte = serialize($compte);

	// update modifications
	$bdd->exec("UPDATE users SET actions='$compte' WHERE password='dd94709528bb1c83d08f3088d4043f4742891f4f' AND user='banque'");

	// update the inflation value
	$req = $bdd->prepare("INSERT INTO courbeLog (value) VALUES (:value)");
	$req->bindParam(':value', round($value, 4));
	$req->execute();
}


function updateLog($bdd, $total) {
	// get the old inflation value
	$reponse = $bdd->query("SELECT log FROM `logInflation` ORDER BY id DESC LIMIT 1");
	while ($donnees = $reponse->fetch()) {
		$avant = $donnees["log"];
	}

	// get the precedent message
	$reponse = $bdd->query("SELECT log FROM `log` ORDER BY id DESC LIMIT 1");
	while ($donnees = $reponse->fetch()) {
		$logAvant = $donnees["log"];
	}


	if (!strpos($logAvant, '#00F')) {
		// get the new inflation value
		$apres = end($total);

		// save the new inflation value
		$req = $bdd->prepare("INSERT INTO logInflation (log) VALUES (:apres)");
		$req->bindParam(':apres', round($apres, 4));
		$req->execute();

		// PARTIE II
		// get a number between 1 and 3
		$date = date('h');
		$array = [
			"00" => 1,
			"01" => 2,
			"02" => 3,
			"03" => 1,
			"04" => 2,
			"05" => 3,
			"06" => 1,
			"07" => 2,
			"08" => 3,
			"09" => 1,
			"10" => 2,
			"11" => 3,
			"12" => 1,
		];
		$randMax = $array[$date];

		// get the best action
		$reponse = $bdd->query("SELECT product FROM `courbe` ORDER BY max + min DESC LIMIT $randMax");
		while ($donnees = $reponse->fetch()) {
			$maxValue = $donnees['product'];
		}

		// get the worst action
		$reponse = $bdd->query("SELECT product FROM `courbe` ORDER BY max + min LIMIT $randMax");
		while ($donnees = $reponse->fetch()) {
			$minValue = $donnees['product'];
		}

		// FIN PARTIE II

		// create the inflation message
		$log = "<p class='log' style='color: #00F;'>L'inflation générale a évolué de " . round(($apres - $avant) / 10, 2) . "% depuis la dernière notification, la banque centrale informe une probable montée de l'action \"" . $maxValue . "\" et une baisse de l'action \"" . $minValue . "\"</p>";

		// save the message
		$req = $bdd->prepare("INSERT INTO log (log) VALUES (:log)");
		$req->bindParam(':log', $log);
		$req->execute();
	}
}

if (isset($_GET['updatecour'])) {
	updateCour($bdd, $value, $products);
} elseif (isset($_GET['updatelog'])) {
	updateLog($bdd, $total);
}

echo "{ \"total\": " . json_encode($total) . ", \"totalArray\": " . json_encode($totalArray) . ", \"chat\": " . $chat . "0}"

?>