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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Fee", mappedBy="transaction", orphanRemoval=true)
     */
    private $fees;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $notified;

    const STATUS_PENDING = 0;
    const STATUS_PAID = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_FAILED = 3;
    const STATUS_DECLINED = 4;

    public function __construct($user_id, $invoice_number = null, $status = self::STATUS_PENDING, $date = null, $notified = false)
    {
        $this->fees = new ArrayCollection();
        $this->user_id = $user_id;
        $this->status = $status;
        $this->invoice_number = $invoice_number ?: uniqid();
        $this->date = $date ?: new \DateTime();
        $this->notified = $notified;
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getNotified(): ?bool
    {
        return $this->notified;
    }

    public function setNotified(?bool $notified): self
    {
        $this->notified = $notified;

        return $this;
    }
}
