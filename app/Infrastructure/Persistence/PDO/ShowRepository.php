<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\PDO;

use PDO;
use App\Domain\Show\Entity\Show;
use App\Domain\Show\Repository\ShowRepositoryInterface;
use App\Domain\Show\ValueObject\ShowId;
use App\Domain\Show\ValueObject\ShowDate;
use App\Domain\Show\ValueObject\Price;
use App\Domain\Show\ValueObject\SeatsAvailability;
use App\Infrastructure\Database\PDOConnection;

final class ShowRepository implements ShowRepositoryInterface
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = PDOConnection::getInstance();
    }
    
    public function save(Show $show): void
    {
        if ($show->getId() === null) {
            $this->insert($show);
        } else {
            $this->update($show);
        }
    }
    
    private function insert(Show $show): void
    {
        $sql = "INSERT INTO shows (title, description, date, location, price, available_seats, image_url, created_at, updated_at) 
                VALUES (:title, :description, :date, :location, :price, :available_seats, :image_url, NOW(), NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'title' => $show->getTitle(),
            'description' => $show->getDescription(),
            'date' => $show->getDate()->toString(),
            'location' => $show->getLocation(),
            'price' => $show->getPrice()->toFloat(),
            'available_seats' => $show->getAvailableSeats()->toInt(),
            'image_url' => $show->getImageUrl(),
        ]);
        
        $show->setId(ShowId::fromInt((int) $this->pdo->lastInsertId()));
    }
    
    private function update(Show $show): void
    {
        $sql = "UPDATE shows SET 
                title = :title,
                description = :description,
                date = :date,
                location = :location,
                price = :price,
                available_seats = :available_seats,
                image_url = :image_url,
                updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $show->getId()->toInt(),
            'title' => $show->getTitle(),
            'description' => $show->getDescription(),
            'date' => $show->getDate()->toString(),
            'location' => $show->getLocation(),
            'price' => $show->getPrice()->toFloat(),
            'available_seats' => $show->getAvailableSeats()->toInt(),
            'image_url' => $show->getImageUrl(),
        ]);
    }
    
    public function findById(ShowId $id): ?Show
    {
        $sql = "SELECT * FROM shows WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id->toInt()]);
        
        $data = $stmt->fetch();
        
        return $data ? $this->hydrate($data) : null;
    }
    
    public function findAll(): array
    {
        $sql = "SELECT * FROM shows ORDER BY date DESC";
        $stmt = $this->pdo->query($sql);
        
        $shows = [];
        while ($data = $stmt->fetch()) {
            $shows[] = $this->hydrate($data);
        }
        
        return $shows;
    }
    
    public function findUpcoming(): array
    {
        $sql = "SELECT * FROM shows WHERE date > NOW() ORDER BY date ASC";
        $stmt = $this->pdo->query($sql);
        
        $shows = [];
        while ($data = $stmt->fetch()) {
            $shows[] = $this->hydrate($data);
        }
        
        return $shows;
    }
    
    public function exists(ShowId $id): bool
    {
        return $this->findById($id) !== null;
    }
    
    public function delete(ShowId $id): void
    {
        $sql = "DELETE FROM shows WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id->toInt()]);
    }
    
    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM shows";
        return (int) $this->pdo->query($sql)->fetchColumn();
    }
    
    private function hydrate(array $data): Show
    {
        return Show::reconstitute(
            id: ShowId::fromInt((int) $data['id']),
            title: $data['title'],
            description: $data['description'],
            date: ShowDate::fromString($data['date']),
            location: $data['location'],
            price: Price::fromFloat((float) $data['price']),
            availableSeats: SeatsAvailability::fromInt((int) $data['available_seats']),
            imageUrl: $data['image_url']
        );
    }
}