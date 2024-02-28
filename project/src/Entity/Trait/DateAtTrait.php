<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;

trait DateAtTrait
{
    #[ORM\Column(type: 'datetime')]
    protected \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $deletedAt = null;

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(LifecycleEventArgs|\DateTimeInterface $createdAt = null): self
    {
        if (isset($this->createdAt)) {
            return $this;
        }

        if ($createdAt instanceof \DateTimeInterface) {
            $this->createdAt = $createdAt;

            return $this;
        }

        $this->createdAt = new \DateTime();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PreUpdate
     */
    #[ORM\PreUpdate]
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTime $deletedAt = null): self
    {
        $this->deletedAt = $deletedAt ?? new \DateTime();

        return $this;
    }

    public function softDelete(): self
    {
        $this->deletedAt = new \DateTime();

        return $this;
    }

    public function restore(): self
    {
        $this->deletedAt = null;

        return $this;
    }
}
