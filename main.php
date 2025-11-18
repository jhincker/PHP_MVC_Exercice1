<?php
// --- Inclusion des classes ---
include('functions.php');
include('command.php');

// --- Connexion à la base ---
$db = new DBConnect();
$pdo = $db->getPDO();

// --- Instanciation du manager et du gestionnaire de commandes ---
$manager = new ContactManager($pdo);
$command = new Command($manager);

// --- Boucle principale ---
echo "Bienvenue dans le gestionnaire de contacts \n";
echo "Tapez 'help' pour afficher la liste des commandes disponibles.\n\n";

while (true) {
    $line = readline("Entrez votre commande : ");
    $command->execute(trim($line));
}
