<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 */
class Transaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Fee", mappedBy="transaction_id", orphanRemoval=true)
     */
    private $fees;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $invoice_number;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $user_id;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $total_balance;

    public function __construct()
    {
        $this->fees = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Fee[]
     */
    public function getFees(): Collection
    {
        return $this->fees;
    }

    public function addFee(Fee $fee): self
    {
        if (!$this->fees->contains($fee)) {
            $this->fees[] = $fee;
            $fee->setTransaction($this);
        }

        return $this;
    }

    public function removeFee(Fee $fee): self
    {
        if ($this->fees->contains($fee)) {
            $this->fees->removeElement($fee);
            // set the owning side to null (unless already changed)
            if ($fee->getTransaction() === $this) {
                $fee->setTransaction(null);
            }
        }

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber(string $invoice_number): self
    {
        $this->invoice_number = $invoice_number;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->user_id;
    }

    public function setUserId(string $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getTotalBalance()
    {
        return $this->total_balance;
    }

    public function setTotalBalance($total_balance): self
    {
        $this->total_balance = $total_balance;

        return $this;
    }
}