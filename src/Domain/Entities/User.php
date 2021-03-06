<?php declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\AbstractEntity;
use App\Domain\Entities\User\Group as UserGroup;
use App\Domain\Entities\User\Integration as UserIntegration;
use App\Domain\Entities\User\Session as UserSession;
use App\Domain\Traits\FileTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * @ORM\Entity(repositoryClass="App\Domain\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User extends AbstractEntity
{
    use FileTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    protected Uuid $uuid;

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    /**
     * @ORM\Column(type="string", length=50, options={"default": ""})
     */
    protected string $username = '';

    /**
     * @return $this
     */
    public function setUsername(string $username)
    {
        if ($this->checkStrLenMax($username, 50)) {
            $this->username = $username;
        }

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @ORM\Column(type="string", length=120, unique=true, options={"default": ""})
     */
    protected string $email = '';

    /**
     * @throws \App\Domain\Service\User\Exception\WrongEmailValueException
     *
     * @return $this
     */
    public function setEmail(string $email)
    {
        try {
            if ($this->checkStrLenMax($email, 120) && $this->checkEmailByValue($email)) {
                $this->email = $email;
            }
        } catch (RuntimeException $e) {
            throw new \App\Domain\Service\User\Exception\WrongEmailValueException();
        }

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Gravatar
     *
     * @return string
     */
    public function avatar(int $size = 40)
    {
        if ($this->hasFiles()) {
            return $this->getFiles()->first()->getPublicPath();
        }

        return 'https://www.gravatar.com/avatar/' . md5(mb_strtolower(trim($this->email))) . '?s=' . $size;
    }

    /**
     * @ORM\Column(type="string", length=25, options={"default": ""})
     */
    protected string $phone = '';

    /**
     * @throws \App\Domain\Service\User\Exception\WrongPhoneValueException
     *
     * @return $this
     */
    public function setPhone(string $phone = null)
    {
        if ($phone) {
            try {
                if ($this->checkStrLenMax($phone, 25) && $this->checkPhoneByValue($phone)) {
                    $this->phone = $phone;
                }
            } catch (RuntimeException $e) {
                throw new \App\Domain\Service\User\Exception\WrongPhoneValueException();
            }
        } else {
            $this->phone = '';
        }

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @ORM\Column(type="string", length=140, options={"default": ""})
     */
    protected string $password = '';

    /**
     * @return $this
     */
    public function setPassword(string $password)
    {
        if ($password && $this->checkStrLenMax($password, 140)) {
            $this->password = $this->getPasswordHashByValue($password);
        }

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @ORM\Column(type="string", length=50, options={"default": ""})
     */
    protected string $firstname = '';

    /**
     * @return $this
     */
    public function setFirstname(string $firstname)
    {
        if ($this->checkStrLenMax($firstname, 50)) {
            $this->firstname = $firstname;
        }

        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @ORM\Column(type="string", length=50, options={"default": ""})
     */
    protected string $lastname = '';

    /**
     * @return $this
     */
    public function setLastname(string $lastname)
    {
        if ($this->checkStrLenMax($lastname, 50)) {
            $this->lastname = $lastname;
        }

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getName(string $type = 'full')
    {
        if ($this->lastname || $this->firstname) {
            switch ($type) {
                case 'full':
                    return implode(' ', [$this->lastname, $this->firstname]);

                case 'short':
                    return implode(' ', [mb_substr($this->lastname, 0, 1) . '.', $this->firstname]);
            }
        }

        return '';
    }

    /**
     * @ORM\Column(type="string", length=500, options={"default": ""})
     */
    protected string $address = '';

    /**
     * @return $this
     */
    public function setAddress(string $address)
    {
        if ($this->checkStrLenMax($address, 500)) {
            $this->address = $address;
        }

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @ORM\Column(type="string", length=250, options={"default": ""})
     */
    protected string $additional = '';

    /**
     * @return $this
     */
    public function setAdditional(string $additional)
    {
        if ($this->checkStrLenMax($additional, 250)) {
            $this->additional = $additional;
        }

        return $this;
    }

    public function getAdditional(): string
    {
        return $this->additional;
    }

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected bool $allow_mail = true;

    /**
     * @param mixed $allow_mail
     *
     * @return $this
     */
    public function setAllowMail($allow_mail)
    {
        $this->allow_mail = $this->getBooleanByValue($allow_mail);

        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowMail()
    {
        return $this->allow_mail;
    }

    /**
     * @see \App\Domain\Types\UserStatusType::LIST
     * @ORM\Column(type="UserStatusType", options={"default": \App\Domain\Types\UserStatusType::STATUS_WORK})
     */
    protected string $status = \App\Domain\Types\UserStatusType::STATUS_WORK;

    /**
     * @return $this
     */
    public function setStatus(string $status)
    {
        if (in_array($status, \App\Domain\Types\UserStatusType::LIST, true)) {
            $this->status = $status;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @ORM\Column(type="uuid", nullable=true, options={"default": NULL})
     */
    protected ?Uuid $group_uuid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Entities\User\Group")
     * @ORM\JoinColumn(name="group_uuid", referencedColumnName="uuid")
     */
    protected ?UserGroup $group = null;

    /**
     * @param string|UserGroup $group
     *
     * @return User
     */
    public function setGroup($group)
    {
        if (is_a($group, UserGroup::class)) {
            $this->group_uuid = $group->getUuid();
            $this->group = $group;
        } else {
            $this->group_uuid = null;
            $this->group = null;
        }

        return $this;
    }

    public function getGroup(): ?UserGroup
    {
        return $this->group;
    }

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected DateTime $register;

    /**
     * @param $register
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setRegister($register)
    {
        $this->register = $this->getDateTimeByValue($register);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getRegister()
    {
        return $this->register;
    }

    /**
     * @ORM\Column(name="`change`", type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected DateTime $change;

    /**
     * @param $change
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setChange($change)
    {
        $this->change = $this->getDateTimeByValue($change);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getChange()
    {
        return $this->change;
    }

    /**
     * @ORM\Column(type="array", options={"default": "a:0:{}"})
     */
    protected array $token = [];

    /**
     * @return $this
     */
    public function setToken(array $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param mixed  $data
     *
     * @return $this
     */
    public function changeToken(string $name, $data)
    {
        $this->token[$name] = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getToken(?string $name = null)
    {
        if ($name) {
            return $this->token[$name] ?? null;
        }

        return $this->token;
    }

    /**
     * @ORM\OneToMany(targetEntity="App\Domain\Entities\User\Integration", mappedBy="user", orphanRemoval=true)
     */
    protected $integrations = [];

    /**
     * @return $this
     */
    public function addIntegration(UserIntegration $integration)
    {
        $this->integrations[] = $integration;
        $integration->setUser($this);

        return $this;
    }

    /**
     * @param mixed $raw
     *
     * @return array|Collection
     */
    public function getIntegrations($raw = false)
    {
        return $raw ? $this->integrations : collect($this->integrations);
    }

    /**
     * @ORM\OneToOne(targetEntity="App\Domain\Entities\User\Session", mappedBy="user", orphanRemoval=true)
     */
    protected ?UserSession $session = null;

    /**
     * @return $this
     */
    public function setSession(UserSession $session)
    {
        $this->session = $session;
        $this->session->setUser($this);

        return $this;
    }

    /**
     * @return null|UserSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @var array
     * @ORM\OneToMany(targetEntity="\App\Domain\Entities\File\UserFileRelation", mappedBy="user", orphanRemoval=true)
     * @ORM\OrderBy({"order": "ASC"})
     */
    protected $files = [];

    /**
     * Return model as array
     */
    public function toArray(): array
    {
        $buf = parent::toArray();

        if ($this->session) {
            $buf['session'] = $this->session->toArray();
        }

        return $buf;
    }
}
