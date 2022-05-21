<?php
// product available & color for each product
$products = array("compte", "petrole", "soja", "fer", "eau", "electricite", "cuivre", "plastique", "aluminium", "sable", "or", "gaz");

$back = array("#000", "#000", "#5CCA5C", "#999", "#4747FF", "#FF0", "#837244", "#837", "#999", "#EDEAA5", "#FF0", "#C6C3FE");

$text = array("#fff", "#fff", "#ffffff", "#fff", "#ffffff", "#000", "#ffffff", "#fff", "#fff", "#000000", "#000", "#000000");

// user default actions
$actions = [
	"compte"=> 5000,
	"petrole"=>0,
	"soja"=>0,
	"fer"=>0,
	"eau"=>0,
	"electricite"=>0,
	"cuivre"=>0,
	"plastique"=>0,
	"aluminium"=>0,
	"sable"=>0,
	"or"=>0,
	"gaz"=>0,
];


// green 
$messageForIncrease = array(
	"petrole"=> array(1, "Le pays du soleil levant à confondu soja et pétroles, 2000 tonnse de pétrole sont maintenant hors d'usages"),
	"soja"=> array(1, "Kim Jong Un a aujourd'hui déclaré \"콩만 있으면 다 괜찮아\" soit \"Tant qu'on a du soja tout va\""),
	"fer"=> array(1, "Le fer une nouvelle matière tendance aux USA"),
	"eau"=> array(1, "Donald Trump a déclaré: \"l'eau c'est bon\""),
	"electricite"=> array(2, "Une panne électrique a touché l'ISS aujourd'hui", "les hybrides utilisent de plus en plus d'électricité"),
	"cuivre"=> array(1, "De nouvelles gammes de trompettes arrivent sur le marché utilisant beaucoup de ressources"),
	"plastique"=> array(1, "Un accord récent vise à limier la production de plastique"),
	"aluminium"=> array(1, "De nouvelles armes ont été développé à partir d'aluminium"),
	"sable"=> array(1, "Cette année les aquariophiles ont été très gourmants en sable"),
	"or"=> array(1, "Le métier chercheurs d'or de moins en moins tendance"),
	"gaz"=> array(1, "Le gazoduc de Saint-Etienne a sauté cette nuit"),
);

// red
$messageForFall = array(
	"petrole"=> array(2, "Un nouveau gisement de pétrole a été trouvé au Vietnam", "Total a déposé un brevet sur la fabrication de pétrole à partir d'algues"),
	"soja"=> array(1, "La bonne météo a fait augmenter les rendements de soja"),
	"fer"=> array(1, "Le Kazakhstan a finalement décidé d'arrêter de munir tous ses soldats d'épées en fer"),
	"eau"=> array(1, "Une nouvelle usine de dessalement a ouvert en Suisse"),
	"electricite"=> array(1, "Le projet \"ENred\" qui consiste à implanter des panneaux solaires sur mars à aboutit"),
	"cuivre"=> array(1, "La gamme de trompettes sortit dernièrement a fait flop, des tonnes de cuivres minées sont inutilisées"),
	"plastique"=> array(1, "Une erreur d'acheminement du pétrole a empêché la fabrication de 1000 tonnes de plastique"),
	"aluminium"=> array(1, "Le papier aluminium se vend de moins en moins selon Philippe Etchebest"),
	"sable"=> array(1, "Les vitraux faits à partir de sable sont de moins en moins tendance dans les églises"),
	"or"=> array(1, "De l'or d'origine extraterrestre a été découvert en Alaska"),
	"gaz"=> array(1, "De nouveau gisement de gaz ont été trouvé en Russie, Poutine exprime sa joie"),
);
?>