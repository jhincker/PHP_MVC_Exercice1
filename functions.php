<?php


class DBConnect
{
    public function getPDO(): PDO
    {
        return new \PDO('mysql:dbname=projetMVC;host=127.0.0.1;charset=utf8', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}

class ContactManager // Gère la communication avec la base de données
{
    public function __construct(private PDO $pdo)
    {
        //$this->pdo = $pdo; pas besoin de l'écire, automatique
    }

    public function findAll(): array
    {
        try {
            $stmt = $this->pdo->query('SELECT * FROM contact'); // abréviation de statement (ou instruction SQL), c’est l’objet qui représente la requête SQL préparée
            $rows = $stmt->fetchAll();

            $contacts = [];
            foreach ($rows as $row) {
                $contacts[] = new Contact(
                    $row['id'],
                    $row['name'],
                    $row['email'],
                    $row['phone_number']
                );
            }
            return $contacts;
        } catch (PDOException $e) {
            // soit on log, soit on remonte l'erreur
            return [];
        }
    }

    public function findById(int $id): ?Contact
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM contact WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();

            if ($row) {
                return new Contact(
                    $row['id'],
                    $row['name'],
                    $row['email'],
                    $row['phone_number']
                );
            }

            return null; //Aucun contact trouvé
        } catch (PDOException $e) {
            return null;
        }
    }

    public function create(Contact $contact): bool
    {
        try {
            $create = $this->pdo->prepare(
                'INSERT INTO contact (name, email, phone_number) VALUES (?, ?, ?)'
            );

            return $create->execute([
                $contact->getName(),
                $contact->getEmail(),
                $contact->getPhoneNumber()
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM contact WHERE id = :id');
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() === 1) {
                return true;  // un contact supprimé
            }

            return false;     // aucun contact avec cet ID
        } catch (PDOException $e) {
            return false;     // erreur SQL
        }
    }

    public function update(Contact $contact): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE contact SET name = ?, email = ?, phone_number = ? WHERE id = ?'
            );
            $ok = $stmt->execute([
                $contact->getName(),
                $contact->getEmail(),
                $contact->getPhoneNumber(),
                $contact->getId()
            ]);
            // succès si 1 ligne modifiée (ou 0 si mêmes valeurs — à toi de choisir)
            return $ok && $stmt->rowCount() >= 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}



class Contact // Représente un objet contact (les données elles-mêmes)
{
    public function __construct(private int $id, private string $name, private string $email, private string $phone_number) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPhoneNumber(string $phone_number): void
    {
        $this->phone_number = $phone_number;
    }

    public function __toString(): string
    {
        return "{$this->name} ({$this->email} / {$this->phone_number})";
    }
}

// Test
$db = new DBConnect();
$pdo = $db->getPDO();

$manager = new ContactManager($pdo);
$contacts = $manager->findAll();

foreach ($contacts as $contact) {
    echo $contact . PHP_EOL;
}
