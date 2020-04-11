<?php declare(strict_types=1);

namespace App\Domain\Entities\User;

use Alksily\Entity\Model;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_session", uniqueConstraints={@ORM\UniqueConstraint(name="unique_uuid", columns={"uuid"})})
 */
class Session extends Model
{
    /**
     * @var Uuid
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    public $uuid;

    /**
     * @ORM\Column(type="string", length=16, options={"default": ""})
     */
    public $ip = '';

    /**
     * @ORM\Column(type="string", length=256, options={"default": ""})
     */
    public $agent = '';

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    public $date;
}
