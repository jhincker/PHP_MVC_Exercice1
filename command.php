<?php

class Command //Gère l’interaction avec l’utilisateur (readline, affichage)
{
    private ContactManager $manager;

    public function __construct(ContactManager $manager)
    {
        $this->manager = $manager;
    }

    public function execute(string $line): void
    {
        // --- Commande LIST ---
        if ($line === 'list') {
            $contacts = $this->manager->findAll();

            if (empty($contacts)) {
                echo "Aucun contact trouvé.\n";
            } else {
                foreach ($contacts as $contact) {
                    echo $contact . PHP_EOL;
                }
            }

            // --- Commande SHOW ID ---
        } elseif (preg_match('/^show (\d+)$/', $line, $matches)) {
            $id = (int)$matches[1];
            $contact = $this->manager->findById($id);

            if ($contact) {
                echo "Contact trouvé : " . $contact . PHP_EOL;
            } else {
                echo "Aucun contact avec l'ID $id.\n";
            }

            // --- Commande Create ---
        } else if ($line === 'create') {
            $this->create();

            // --- Commande Delete ---
        } elseif (preg_match('/^delete (\d+)$/', $line, $matches)) {
            $id = (int)$matches[1];
            $success = $this->manager->delete($id);

            if ($success) {
                echo "Contact supprimé.\n";
            } else {
                echo "Aucun contact avec cet ID.\n";
            }

            // --- Commande modify ---
        } elseif (preg_match('/^modify (\d+)$/', $line, $m)) {
            $id = (int) $m[1];
            $this->modify($id);

            // --- Help ---
        } elseif ($line === 'help') {
            $this->help();
            // --- Quitter ---
        } elseif ($line === 'exit') {
            echo "Au revoir !\n";
            exit;

            // --- Commande inconnue ---
        } else {
            echo "Commande inconnue. Essayez : list, show <id> ou exit.\n";
        }
    }

    public function create(): void
    {
        // Lire les infos de l’utilisateur
        $name = readline("Entrez le nom : ");
        $email = readline("Entrez l'email : ");
        $phone_number = readline("Entrez le numéro de téléphone : ");

        // Vérifier si un champ est vide
        if (empty($name) || empty($email) || empty($phone_number)) {
            echo "Tous les champs doivent être remplis.\n";
            return;
        }

        // Vérifier si l’email est valide
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "L'adresse email n'est pas valide.\n";
            return;
        }

        // Créer un objet Contact
        $contact = new Contact(0, $name, $email, $phone_number);

        // Appeler ContactManager->create()
        $success = $this->manager->create($contact);

        // Afficher un message selon le résultat
        if ($success) {
            echo "Le contact a bien été ajouté.\n";
        } else {
            echo "Erreur lors de l'ajout du contact.\n";
        }
    }

    public function modify(int $id): void
    {
        // 1) Charger
        $contact = $this->manager->findById($id);
        if (!$contact) {
            echo "Aucun contact avec l'ID $id.\n";
            return;
        }

        // 2) Afficher l’actuel via __toString()
        echo "Contact actuel : " . $contact . "\n"; // utilise __toString()

        // 3) Demander (laisser vide pour conserver)
        $newName  = readline("Nouveau nom (laisser vide pour conserver) : ");
        $newEmail = readline("Nouvel email (laisser vide pour conserver) : ");
        $newPhone = readline("Nouveau téléphone (laisser vide pour conserver) : ");

        // 4) Conserver si vide
        if ($newName === '') {
            $newName  = $contact->getName();
        }
        if ($newEmail === '') {
            $newEmail = $contact->getEmail();
        }
        if ($newPhone === '') {
            $newPhone = $contact->getPhoneNumber();
        }

        // 5) Valider email si changé
        if ($newEmail !== $contact->getEmail() && !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            echo "L'adresse email n'est pas valide.\n";
            return;
        }

        // 6) Appliquer setters
        $contact->setName($newName);
        $contact->setEmail($newEmail);
        $contact->setPhoneNumber($newPhone);

        // 7) Update
        $ok = $this->manager->update($contact);

        // 8) Message
        if ($ok) {
            echo "Contact mis à jour : " . $contact . "\n";
        } else {
            echo "Erreur lors de la mise à jour.\n";
        }
    }


    public function help(): void
    {
        echo "Commandes disponibles :\n";
        echo "  list               - Affiche tous les contacts\n";
        echo "  show <id>          - Affiche le contact par id\n";
        echo "  add                - Ajoute un contact\n";
        echo "  modify <id>        - Modifie un contact (nom/email/téléphone)\n";
        echo "  delete <id>        - Supprime un contact\n";
        echo "  help               - Affiche cette aide\n";
        echo "  exit               - Quitte l'application\n";
    }
}
