<?php
//connexion
$connexion = false;

//connexion
$SQLhost = // deleted;
$DBname = // deleted;
$username = // deleted;
$password = // deleted;

$bdd = new PDO("mysql:host=$SQLhost; dbname=$DBname; charset=utf8", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
include "product.php";

function back($url) {
	if ($url == "index.php?message=success&start=") {
		$url = $url . $_POST['start'];
	} else {
		// get parameters
		$url = $url . $_POST['start'] . "&action=" . $_POST['action'] . "&product=" . $_POST['product'] . "&number=" . $_POST['number'];
	}
	// open url
	header("location: " . $url);
}

// get the stock price
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

function getNewprice($bdd, $product, $number) {

	// get the user number
	$reponse = $bdd->query("SELECT id FROM `users` ORDER BY id DESC LIMIT 1");
	while ($donnees = $reponse->fetch()) {
		$numberOfUsers = intval($donnees["id"]);
	}

	// get the stock number
	$TotalOfProduct = 0;
	$reponse = $bdd->query("SELECT actions FROM `users`");
	while ($donnees = $reponse->fetch()) {
		$TotalOfProduct = $TotalOfProduct + unserialize($donnees["actions"])[$product];
	}

	// get the new price
	return ($TotalOfProduct + $number) / $numberOfUsers;
}

function event($bdd, $products) {
	include "product.php";

	// get the product
	$product = $products[rand(1, count($products) - 1)];

	// get the user number
	$reponse = $bdd->query("SELECT id FROM `users` ORDER BY id DESC LIMIT 1");
	while ($donnees = $reponse->fetch()) {
		$numberOfUsers = intval($donnees["id"]);
	}

	// get the evolution
	$reponse = $bdd->query("SELECT min, max FROM `courbe` WHERE product ='$product'");
	while ($donnees = $reponse->fetch()) {
		$evolution = (rand($donnees["min"] * 50, $donnees["max"] * 50) / 10) * $numberOfUsers;
	}


	// get the bannk account
	$reponse = $bdd->query("SELECT actions FROM `users` WHERE password='dd94709528bb1c83d08f3088d4043f4742891f4f' AND user='banque'");
	while ($donnees = $reponse->fetch()) {
		$compte = unserialize($donnees["actions"]);
	}

	// get the old price
	$price1 = round(getprice($bdd, $product), 3);

	// update account
	if (rand(0, 1) == 0) {
		$compte[$product] = $compte[$product] - $evolution;
		if ($compte[$product] < 0) {
			$compte[$product] = 0;
		}
	} else {
		$compte[$product] = $compte[$product] + $evolution;
	}
	$compte = serialize($compte);


	// update modifications
	$bdd->exec("UPDATE users SET actions = '$compte' WHERE password='dd94709528bb1c83d08f3088d4043f4742891f4f' AND user='banque'");

	// get the new price
	$price2 = round(getprice($bdd, $product), 3);

	// print the event
	if ($price1 > $price2) {
		// decrease the evolution value
		$reponse = $bdd->query("SELECT min, max FROM `courbe` WHERE product ='$product'");
		while ($donnees = $reponse->fetch()) {
			$min = $donnees['min'] - 0.5;
			$max = $donnees['max'] - 0.5;

			$bdd->exec("UPDATE `courbe` SET `max`='$max',`min`='$min' WHERE product='$product'");
		}


		$log = "<p class='log' style='color: #F00;'>Evenement: " . $messageForFall[$product][rand(1, $messageForFall[$product][0])] . ", l'action " . $product . " est passée de " . $price1 . "€ à "  . $price2 . "€</p>";
		$char = "action est";
		if (strlen($product) > 1) {
			$req = $bdd->prepare("INSERT INTO log (log) VALUES (:log)");
			$req->bindParam(':log', $log);
			$req->execute();
		}
	} else if ($price1 < $price2) {
		// increase the evolution value
		$reponse = $bdd->query("SELECT min, max FROM `courbe` WHERE product ='$product'");
		while ($donnees = $reponse->fetch()) {
			$min = $donnees['min'] + 0.5;
			$max = $donnees['max'] + 0.5;

			$bdd->exec("UPDATE `courbe` SET `max`='$max',`min`='$min' WHERE product='$product'");
		}

		$log = "<p class='log' style='color: #0F0;'>Evenement: " . $messageForIncrease[$product][rand(1, $messageForIncrease[$product][0])] . ", l'action " . $product . " est passée de " . $price1 . "€ à "  . $price2 . "€</p>";
		$char = "action est";
		if (strlen($product) > 1) {
			$req = $bdd->prepare("INSERT INTO log (log) VALUES (:log)");
			$req->bindParam(':log', $log);
			$req->execute();
		}
	}
}


function sell($bdd, $product, $number, $products) {
	// connection
	$password = $_COOKIE['password'];
	$user = $_COOKIE['user'];

	// get the stock price
	$price = getprice($bdd, $product);

	// get stock
	$reponse = $bdd->query("SELECT actions FROM `users` WHERE password='$password' AND user='$user'");
	while ($donnees = $reponse->fetch()) {
		$compte = unserialize($donnees["actions"]);
	}
	if (isset($compte)) { // if the user exist
		if ($compte[$product] >= $number && $number > 0) { // if the user have enough money

			// create a anti tax
			if ($compte[$product] > $number) {
				$Newprice = getNewprice($bdd, $product, $number * - 1);
				$antitaxe = ($compte[$product] - $number) * (($price) - ($Newprice));
			} else {
				$antitaxe = 0;
			}
			// ajouter argent sur le compte puis ajouter les actions
			$compte["compte"] = $compte["compte"] + $price * $number + $antitaxe;
			$compte[$product] = $compte[$product] - $number;

			// encode the file and then save it
			$compte = serialize($compte);
			$bdd->exec("UPDATE users SET actions = '$compte' WHERE password='$password' AND user='$user'");

			// print stock message
			$log = "<p class='log'>" . $user . " a vendu " . $number . " actions " . $product . " à " . round($price, 4) . "€";
			$req = $bdd->prepare("INSERT INTO log (log) VALUES (:log)");
			$req->bindParam(':log', $log);
			$req->execute();

			// event?
			if (rand(0, 1) == 0) {
				event($bdd, $products);
			}

			// return to index
			back("index.php?message=success&start=");
			
		} else {

			// return to index
			back("index.php?message=no-actions&start=");
		}
	} else {

		// return to index
		back("index.php?message=no-connection&start=");
	}
}


function buy($bdd, $product, $number, $products) {
	// connection
	$password = $_COOKIE['password'];
	$user = $_COOKIE['user'];

	// get the product number
	$w = 0;
	while ($products[$w] && $w != true) {
		if ($products[$w] == $product) {
			$w = true;
		} else {
			$w = $w + 1;
		}
	}

	// return to index if XSS
	if ($w != true) {
		back("index.php?message=success&start=");
	}

	// get the product price
	$price = getprice($bdd, $product) * $number;

	// get the user account
	$reponse = $bdd->query("SELECT actions FROM `users` WHERE password='$password' AND user='$user'");
	while ($donnees = $reponse->fetch()) {
		$compte = unserialize($donnees["actions"]);
	}

	if (isset($compte)) { // if the user exist

		// define the tax
		$taxe = getNewprice($bdd, $product, $number + $compte[$product]) * ($number) - $price;

		if ($compte["compte"] >= $price && $number > 0) { // if the user have enough money



			// update the user acccount
			$compte["compte"] = $compte["compte"] - ($price + $taxe); 

			// encode the file and then save it
			$compte[$product] = $compte[$product] + $number;
			$compte = serialize($compte);
			$bdd->exec("UPDATE users SET actions = '$compte' WHERE password='$password' AND user='$user'");

			// print event
			$log = "<p class='log'>" . $user . " a acheté " . $number . " actions " . $product . " à " . round($price / $number, 4) . "€";
			$req = $bdd->prepare("INSERT INTO log (log) VALUES (:log)");
			$req->bindParam(':log', $log);
			$req->execute();

			// create an event
			//if (rand(1, 3) == 1) {
				event($bdd, $products);
			//}

			// return to index
			back("index.php?message=success&start=");
		} else {

			// return to index
			back("index.php?message=no-money&start=");
		}
	} else {

		// return to index
		back("index.php?message=no-connexion&start=");
	}
}


if (isset($_COOKIE['password']) && isset($_COOKIE['user'])) {

	// connection
	$password = $_COOKIE['password'];
	$user = $_COOKIE['user'];
	$reponse = $bdd->query("SELECT user FROM `users` WHERE password='$password' AND user='$user'");
	while ($donnees = $reponse->fetch()) {
		$connexion = true;
	}
	if ($connexion == false) {

		// return to index + delete cookies if the user dont exist
		setcookie("user", null, time()-3600);
		setcookie("password", null, time()-3600);
		back("connexion.html?message=no-connexion&start=");
	} elseif (isset($_POST['action']) && isset($_POST['product']) && isset($_POST['number'])) {

		// return to index if any argument is false
		if ($_POST['number'] <= 0) {
			back("index.php?message=no-argument&start=");
		}
		if ($_POST['action'] == "acheter") {

			// buy product
			if (in_array($_POST['product'], $products)) {
				buy($bdd, $_POST['product'], $_POST['number'], $products);
			} else {
				back("index.php?message=no-argument&start=");
			}
			
		} elseif ($_POST['action'] == "vendre") {

			// sell product
			if (in_array($_POST['product'], $products)) {
				sell($bdd, $_POST['product'], $_POST['number'], $products);
			} else {
				back("index.php?message=no-argument&start=");
			}
			
		} else {

			// return to index
			back("index.php?message=no-argument&start=");
		}
	} else {

		// return to index
		back("index.php?message=no-argument&start=");
	}
} else {

	// return to index
	back("index.php?message=no-connexion&start=");
}
?>