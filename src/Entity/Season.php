<?php

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
class Season
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\OneToMany(targetEntity: Episode::class, mappedBy: 'season', orphanRemoval: true)]
    private Collection $episodes;

    #[ORM\ManyToOne(targetEntity: Series::class, inversedBy: 'seasons')]
    #[ORM\JoinColumn(nullable: false)]
    private Series $series;

    public function __construct(
        #[ORM\Column(type: 'smallint')]
        private int $number
    ) {
        $this->episodes = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return Collection<int, Episode>
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    /**
     * @return Collection<int, Episode>
     */
    public function getWatchedEpisodes(): Collection
    {
        return $this->episodes->filter(fn (Episode $episode) => $episode->isWatched());
    }

    public function setEpisodes(Episode $episode): self
    {
        if (! $this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->setSeason($this);
        }
        return $this;
    }

    public function removeEpisode(Episode $episode): self
    {
        if ($this->episodes->removeElement($episode)) {
            if ($episode->getSeason() === $this) {
                $episode->setSeason(null);
            }
        }
        return $this;
    }

    public function getSeries(): Series
    {
        return $this->series;
    }

    public function setSeries(?Series $series): self
    {
        $this->series = $series;
        return $this;
    }
}
