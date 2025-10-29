<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\PDO;

use PDO;
use App\Domain\Booking\Entity\Booking;
use App\Domain\Booking\Repository\BookingRepositoryInterface;
use App\Domain\Booking\ValueObject\BookingId;
use App\Domain\Booking\ValueObject\BookingDate;
use App\Domain\User\ValueObject\UserId;
use App\Domain\Show\ValueObject\ShowId;
use App\Infrastructure\Database\PDOConnection;

final class BookingRepository implements BookingRepositoryInterface
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = PDOConnection::getInstance();
    }
    
    public function save(Booking $booking): void
    {
        if ($booking->getId() === null) {
            $this->insert($booking);
        } else {
            $this->update($booking);
        }
    }
    
    private function insert(Booking $booking): void
    {
        $sql = "INSERT INTO bookings (user_id, show_id, booking_date, number_of_seats, created_at) 
                VALUES (:user_id, :show_id, :booking_date, :number_of_seats, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $booking->getUserId()->toString(),
            'show_id' => $booking->getShowId()->toInt(),
            'booking_date' => $booking->getBookingDate()->toString(),
            'number_of_seats' => $booking->getNumberOfSeats(),
        ]);
        
        $booking->setId(BookingId::fromInt((int) $this->pdo->lastInsertId()));
    }
    
    private function update(Booking $booking): void
    {
        $sql = "UPDATE bookings SET 
                user_id = :user_id,
                show_id = :show_id,
                booking_date = :booking_date,
                number_of_seats = :number_of_seats
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $booking->getId()->toInt(),
            'user_id' => $booking->getUserId()->toString(),
            'show_id' => $booking->getShowId()->toInt(),
            'booking_date' => $booking->getBookingDate()->toString(),
            'number_of_seats' => $booking->getNumberOfSeats(),
        ]);
    }
    
    public function findById(BookingId $id): ?Booking
    {
        $sql = "SELECT * FROM bookings WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id->toInt()]);
        
        $data = $stmt->fetch();
        
        return $data ? $this->hydrate($data) : null;
    }
    
    public function findByUserId(UserId $userId): array
    {
        $sql = "SELECT * FROM bookings WHERE user_id = :user_id ORDER BY booking_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId->toString()]);
        
        $bookings = [];
        while ($data = $stmt->fetch()) {
            $bookings[] = $this->hydrate($data);
        }
        
        return $bookings;
    }
    
    public function findByShowId(ShowId $showId): array
    {
        $sql = "SELECT * FROM bookings WHERE show_id = :show_id ORDER BY booking_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['show_id' => $showId->toInt()]);
        
        $bookings = [];
        while ($data = $stmt->fetch()) {
            $bookings[] = $this->hydrate($data);
        }
        
        return $bookings;
    }
    
    public function findAll(): array
    {
        $sql = "SELECT * FROM bookings ORDER BY booking_date DESC";
        $stmt = $this->pdo->query($sql);
        
        $bookings = [];
        while ($data = $stmt->fetch()) {
            $bookings[] = $this->hydrate($data);
        }
        
        return $bookings;
    }
    
    public function exists(BookingId $id): bool
    {
        return $this->findById($id) !== null;
    }
    
    public function delete(BookingId $id): void
    {
        $sql = "DELETE FROM bookings WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id->toInt()]);
    }
    
    public function countByShowId(ShowId $showId): int
    {
        $sql = "SELECT COUNT(*) FROM bookings WHERE show_id = :show_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['show_id' => $showId->toInt()]);
        
        return (int) $stmt->fetchColumn();
    }
    
    private function hydrate(array $data): Booking
    {
        return Booking::reconstitute(
            id: BookingId::fromInt((int) $data['id']),
            userId: UserId::fromString($data['user_id']),
            showId: ShowId::fromInt((int) $data['show_id']),
            bookingDate: BookingDate::fromString($data['booking_date']),
            numberOfSeats: (int) $data['number_of_seats']
        );
    }
}