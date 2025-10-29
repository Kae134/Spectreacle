<?php

declare(strict_types=1);

namespace Spectreacle\Infrastructure\Database;

use Spectreacle\Domain\Show\Entities\Show;
use Spectreacle\Domain\Show\Repositories\ShowRepositoryInterface;
use DateTime;

class InMemoryShowRepository implements ShowRepositoryInterface
{
    private array $shows = [];
    private int $nextId = 1;

    public function __construct()
    {
        // Ajouter quelques spectacles de test
        $this->shows[] = new Show(
            $this->nextId++,
            'Le Roi Lion',
            'Une comédie musicale époustouflante basée sur le célèbre film Disney.',
            'théâtre',
            new DateTime('2025-12-15 20:00:00'),
            'Théâtre du Châtelet',
            500,
            450,
            75.00,
            '/assets/images/roi-lion.jpg'
        );

        $this->shows[] = new Show(
            $this->nextId++,
            'Concert Symphonique',
            'Un concert exceptionnel de l\'Orchestre National avec des œuvres de Beethoven et Mozart.',
            'concert',
            new DateTime('2025-12-20 19:30:00'),
            'Philharmonie de Paris',
            1200,
            800,
            45.00,
            '/assets/images/symphonique.jpg'
        );

        $this->shows[] = new Show(
            $this->nextId++,
            'La Traviata',
            'L\'opéra emblématique de Verdi dans une mise en scène moderne.',
            'opéra',
            new DateTime('2025-12-25 20:30:00'),
            'Opéra de Paris',
            800,
            600,
            120.00,
            '/assets/images/traviata.jpg'
        );

        $this->shows[] = new Show(
            $this->nextId++,
            'Stand-up Comedy Night',
            'Une soirée d\'humour avec les meilleurs comédiens français.',
            'théâtre',
            new DateTime('2025-11-30 21:00:00'),
            'Le Point Virgule',
            150,
            50,
            25.00,
            '/assets/images/comedy.jpg'
        );

        $this->shows[] = new Show(
            $this->nextId++,
            'Festival Jazz',
            'Une soirée jazz exceptionnelle avec des artistes internationaux.',
            'concert',
            new DateTime('2025-01-10 20:00:00'),
            'New Morning',
            300,
            200,
            35.00,
            '/assets/images/jazz.jpg'
        );
    }

    public function findById(int $id): ?Show
    {
        foreach ($this->shows as $show) {
            if ($show->getId() === $id) {
                return $show;
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return $this->shows;
    }

    public function findByCategory(string $category): array
    {
        return array_filter($this->shows, fn($show) => $show->getCategory() === $category);
    }

    public function findAvailable(): array
    {
        return array_filter($this->shows, fn($show) => $show->isAvailable());
    }

    public function save(Show $show): Show
    {
        // Pour la simplicité, on remplace le spectacle s'il existe déjà
        foreach ($this->shows as $index => $existingShow) {
            if ($existingShow->getId() === $show->getId()) {
                $this->shows[$index] = $show;
                return $show;
            }
        }
        
        $this->shows[] = $show;
        return $show;
    }

    public function create(
        string $title,
        string $description,
        string $category,
        \DateTimeInterface $dateTime,
        string $venue,
        int $totalSeats,
        float $price,
        string $imageUrl = ''
    ): Show {
        $show = new Show(
            $this->nextId++,
            $title,
            $description,
            $category,
            $dateTime,
            $venue,
            $totalSeats,
            $totalSeats, // availableSeats = totalSeats initialement
            $price,
            $imageUrl
        );

        $this->shows[] = $show;
        return $show;
    }
}