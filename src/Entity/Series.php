<?php

namespace App\Entity;

use App\Repository\SeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SeriesRepository::class)]
class Series extends \App\DTO\SeriesCreateFromInput
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\OneToMany(targetEntity: Season::class, mappedBy: 'series', orphanRemoval: true)]
    private Collection $seasons;

    public function __construct(
        #[ORM\Column]
        #[Assert\NotBlank]
        #[Assert\Length(min: 5)]
        public string $name = ''
    ) {
        $this->seasons = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Collection<int, Season>
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    public function setSeasons(Season $season): self
    {
        if (! $this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setSeries($this);
        }
        return $this;
    }

    public function removeSeason(Season $season): self
    {
        if ($this->seasons->removeElement($season)) {
            if ($season->getSeries() === $this) {
                $season->setSeries(null);
            }
        }
        return $this;
    }
}
