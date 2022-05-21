<!DOCTYPE html>
<html lang="fr" >
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<title>TradeGame</title>
	<meta name="theme-color" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>
	<link rel="icon" href="tradegame.png" />
	<link rel="apple-touch-icon" href="tradegame.png" />
</head>
<?php

// connexion
$SQLhost = // deleted;
$DBname = // deleted;
$username = // deleted;
$password = // deleted;

$bdd = new PDO("mysql:host=$SQLhost; dbname=$DBname; charset=utf8", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
include "product.php";

$connexion = false;

// sanitize string
function sanitize_string($str) {
    return str_replace(array("\n", "\r", "'", "\"", ";", ",", "\\", "-", "\$", "x00"), '_', $str);
}

// signe up
function inscription($user, $password, $actions, $bdd) {

	// get the user ip
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	// save user data
	$bdd->exec("ALTER TABLE users AUTO_INCREMENT 0");
	$req = $bdd->prepare("INSERT INTO users (user, password, actions, ip) VALUES (:user, :password, :actions, :ip)");
	$req->bindParam(':user', $user);
	$req->bindParam(':password', $password);
	$req->bindParam(':actions', serialize($actions));
	$req->bindParam(':ip', $ip);
	$req->execute();

	// create cookies
	setcookie("user", $user, time()+3600*24*365*15);
	setcookie("password", $password, time()+3600*24*365*15);
}

// redirect user
if (isset($_POST['password']) && isset($_POST['user']) && isset($_POST['connection'])) {
	if (strlen($_POST['password']) < 6 || strlen($_POST['user']) < 6 || strlen($_POST['password']) > 50 || strlen($_POST['user']) > 50) {
		header("location: connexion.html?message=small-large-input");
	} else {
		$password = sha1($_POST['password']);
		$user = htmlspecialchars(sanitize_string($_POST['user']));

		$reponse = $bdd->query("SELECT user, password FROM `users` WHERE user='$user'");
		while ($donnees = $reponse->fetch()) {
			if ($password == $donnees['password']) {
				setcookie("user", $user, time()+3600*24*365*15);
				setcookie("password", $password, time()+3600*24*365*15);
			} else {
				if ($_POST['connection'] == "1") {
					header("location: connexion.html?message=used_username&connection=1");
				} else {
					header("location: connexion.html?message=used_username&connection=0");
				}
			}
			$connexion = true;
		}

		// sign up
		if ($connexion == false) {
			if ($_POST['connection'] == "1") {
				header("location: connexion.html?message=used_undefined&connection=1");
			} else {
				inscription($user, $password, $actions, $bdd);
			}
		}
	}
} elseif (isset($_COOKIE['password']) && isset($_COOKIE['user'])) {
	$password = $_COOKIE['password'];
	$user = $_COOKIE['user'];

	$reponse = $bdd->query("SELECT user, ip FROM `users` WHERE password='$password' AND user='$user'");
	while ($donnees = $reponse->fetch()) {
		$connexion = true;
		// get user ip
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		if ($donnees['ip'] == "") {
			$bdd->exec("UPDATE users SET ip='$ip' WHERE password='$password' AND user='$user'");
		}
		$bdd->exec("UPDATE users SET ip2='$ip' WHERE password='$password' AND user='$user'");
	}
	if ($connexion == false) {
		setcookie("user", null, time()-3600);
		setcookie("password", null, time()-3600);
		header("location: connexion.html?message=no-connexion");
	}
} else {
	header("location: connexion.html?message=inscription");
}
?>
<body>
	<h1>TradeGame</h1>
	<section id="content">
		<?php
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
			$x = 0;

			// print product
			$reponse = $bdd->query("SELECT actions FROM `users` WHERE password='$password' AND user='$user'");
			while ($donnees = $reponse->fetch()) {
				while (isset($products[$x])) {
					echo "<section class='product-section'>";
					echo "<p class='product' style='color: " . $text[$x] . "; background:  " . $back[$x] . ";'>" . $products[$x] . "</p>";
					echo "<p class='price'>" . unserialize($donnees["actions"])[$products[$x]] . "</p>";
					echo "<p class='cour'>" . getprice($bdd, $products[$x]) . "</p>";
					echo "</section>";
					$x = $x + 1;
				}
			}
		?>
	</section>
	<section id="buttons">
		<button onclick="onglet(2)">formulaire de transaction</button>
		<button onclick="onglet(1)">chat générale</button>
		<button onclick="onglet(3)">guide de jeu</button>
	</section>
	<section id="flex">
		<form id="transact-form" action="traitement.php" method="post">
			<select name="action" required="required">
				<option>que voulez vous faire ?</option>
				<option>acheter</option>
				<option>vendre</option>
			</select>
			<select name="product" required="required">
				<option>que souhaitez vous acheter/vendre ?</option>
				<?php
				$x = 1;
					while (isset($products[$x])) {
						echo "<option>" . $products[$x] . "</option>";
						$x = $x + 1;
					}
				?>
			</select>
			<input name="number" placeholder="quantité" type="number" step="0.01" autocomplete="off" id="numberOfAction" required="required">
			<input name="start" type="number" id="start" style="display: none;" value="0">
			<input type="submit" value="exécuter">
			<input type="button" onclick="printEvolution()" value="demander un retour sur l'inflation générale">
		</form>
		<div id="log">
			<iframe id="log-iframe" src="log.php" frameborder="0"></iframe>
		</div>
	</section>
	<section id="chat">
		<iframe id="chat-iframe" src="Chat.php"></iframe>
		<div id="chat-flex">
			<input id="text" type="text" name="text" autocomplete="off" placeholder="Le message que vous voulez envoyer a la communauté">
			<input type="submit" onClick="document.getElementsByTagName('iframe')[0].src = 'Chat.php?text=' + document.getElementById('text').value; document.getElementById('text').value = ''">
		</div>
	</section>
	<section id="guide">
		<iframe id="iframe" src="explication.html"></iframe>
	</section>
	<canvas id="myChart"></canvas>
<?php 
// musique iframe
if (isset($_GET['musique'])) {
	if (isset($_GET['start'])) {
		if ($_GET['start'] > 1012) {
			$start = "0";
		} else {
			$start = $_GET['start'];
		}
	} else {
		$start = "0";
	}
	echo "<iframe width='0' height='0' src='https://www.youtube-nocookie.com/embed/jRUL9i6mdME?start=" . $start . "&autoplay=1' frameborder='0' allow='autoplay; loop' autoplay loop></iframe>";
}
 ?>

</body>
<script type="text/javascript">
// display tabs 
function onglet(x) {
	if (x == 1) {
		document.getElementById('flex').style.display = 'none';
		document.getElementById('chat').style.display = 'block'; 
		document.getElementById('guide').style.display = 'none';
	} if (x == 2) {
		document.getElementById('flex').style.display = 'flex';
		document.getElementById('chat').style.display = 'none';
		document.getElementById('guide').style.display = 'none';
	} if (x == 3) {
		document.getElementById('flex').style.display = 'none';
		document.getElementById('chat').style.display = 'none';
		document.getElementById('guide').style.display = 'block';
	}
}

// update form
urlParams = new URLSearchParams(window.location.search);
function actualiseForm() {
	if (urlParams.get("action") != null) {
	document.getElementsByTagName('select')[0].value = urlParams.get("action");
	}
	if (urlParams.get("product") != null) {
		document.getElementsByTagName('select')[1].value = urlParams.get("product");
	}
	if (urlParams.get("number") != null) {
		document.getElementById("numberOfAction").value = urlParams.get("number");
	}
	if (urlParams.get("scroll") != null) {
		window.scroll(0, Number(urlParams.get("scroll")));
	}
	if (urlParams.get("text") != null) {
		document.getElementById("chat-flex").firstChild.value = urlParams.get("text");
	}
	if (urlParams.get("message") == "no-money") {
		alert("tu n'a pas assez d'argent pour éffectuer cette transaction.");
	}
	if (urlParams.get("message") == "no-argument") {
		alert("tu n'a pas remplit tout les champs du fomulaire.");
	}
}
actualiseForm()



function printEvolution() {
	fetch('https://augustin.cf/tradegame/actualise.php?updatelog=true').then(
			// recharger la page
			window.open('https://augustin.cf/tradegame/index.php', '_self')
		);
}








datapoints = [];

function graph(total) {
	if (datapoints[datapoints.length - 1] != total[total.length - 1]) {

		// recharger les logs
		document.getElementById('log-iframe').src = "";
		document.getElementById('log-iframe').src = "log.php";

		datapoints = total;

		for (i = datapoints.length; i > 0; i--) {
			if (datapoints[i] == null) {
				datapoints[i] = datapoints[i-1];
			}
		}

		DATA_COUNT = datapoints.length;
		labels = [];
		for (let i = 0; i < DATA_COUNT; ++i) {
			labels.push(i.toString());
		}

		data = {
		labels: labels,
		datasets: [{
			label: 'ratio inflation/temps',
			data: datapoints,
			borderColor: "#000",
			tension: 0.2,
		}]
		};

		myChart = document.getElementById("myChart").getContext('2d');

		massPopChart = new Chart(myChart, {
			type: 'line',
			data: data,
			options: {
				responsive: true,
				plugins: {
					title: {
						display: true,
						text: 'Evolution de l\'inflation'
					},
				},
			},
		});
	}
}



async function wait(Ms) {
	await new Promise(resolve => setTimeout(resolve, Ms));
}


// arrondire le nombre d'action
x = 0;
while (document.getElementsByClassName("price")[x]) {
	document.getElementsByClassName("price")[x].innerHTML = Number(document.getElementsByClassName("price")[x].innerHTML).toFixed(2);
	x++;
}

// arrondire le prix des actions et ajouter €
x = 1;
while (document.getElementsByClassName("cour")[x]) {
	document.getElementsByClassName("cour")[x].innerHTML = Number(document.getElementsByClassName("cour")[x].innerHTML).toFixed(2) + "€";
	x++;
}
document.getElementsByClassName("price")[0].innerHTML = document.getElementsByClassName("price")[0].innerHTML + "€";
document.getElementsByClassName("cour")[0].innerHTML = "";


// actualiser les prix des actions, la musique et le chat
async function actualiseTransactions() {
	firstTotal = total = null;
	fetch('https://augustin.cf/tradegame/actualise.php').then(response => response.json()).then(data => firstTotal = total = data.total).then(data => Firstmessage = message = data.chat);

	// change => js
	start = <?php if (isset($_GET['start']) && $_GET['start'] != "") { echo $_GET['start']; } else { echo "0";} ?>;

	while (firstTotal == total) {
		fetch('https://augustin.cf/tradegame/actualise.php').then(response => response.json()).then(data => donnee = data).then(data => total = data.total).then(data => message = data.chat);
		await wait(5000);
		graph(total);

		// chat
		if (Firstmessage != message) {
			Firstmessage = message;
			document.getElementById('chat-iframe').src = "";
			document.getElementById('chat-iframe').src = "Chat.php";
		}

		// musique
		start=+5;
		document.getElementById("start").innerText = start;
	}
	for (i = document.getElementsByClassName("cour").length - 1; i > 0; i--) {
		document.getElementsByClassName("cour")[i].innerText = Number(donnee.totalArray[i - 1]).toFixed(2) + "€";
	}
	actualiseTransactions()
}
actualiseTransactions();



</script>
</html>