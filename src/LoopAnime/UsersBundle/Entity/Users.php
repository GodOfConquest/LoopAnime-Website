<?php

namespace LoopAnime\UsersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Users
 *
 * @ORM\Table("users")
 * @ORM\Entity(repositoryClass="LoopAnime\UsersBundle\Entity\UsersRepository")
 *
 * @ExclusionPolicy("all")
 */
class Users extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_user", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Expose
     */
    protected $id;

    /**
     * @var string
     *
     * @Expose
     */
    protected $username;

    /**
     * @var string
     *
     */
    protected $password;

    /**
     * @var string
     *
     * @Expose
     */
    protected $email;

    /**
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    protected $facebook_id;

    /**
     * @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true)
     */
    protected $facebook_access_token;

    /**
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     */
    protected $google_id;

    /**
     * @ORM\Column(name="google_access_token", type="string", length=255, nullable=true)
     */
    protected $google_access_token;

    /**
     * @ORM\Column(name="trakt_id", type="string", length=255, nullable=true)
     */
    protected $trakt_id;

    /**
     * @ORM\Column(name="trakt_access_token", type="string", length=255, nullable=true)
     */
    protected $trakt_access_token;

    /**
     * @var \DateTime
     *
     * @Expose
     */
    protected $lastLogin;

    /**
     * @var UsersPreferences
     *
     * ORM\OneToOne(targetEntity="LoopAnime\UsersBundle\Entity\UsersPreferences")
     * ORM\JoinColumn(name="id_user", referencedColumnName="id_user", nullable=true)
     *
     * @Expose
     */
    protected $preferences;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     *
     * @Expose
     */
    private $avatar;

    /**
     * @Assert\File(maxSize="2000000")
     */
    private $avatarFile;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthdate", type="datetime", nullable=true)
     *
     * @Expose
     */
    private $birthdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="datetime")
     *
     * @Expose
     */
    private $createTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="newsletter", type="boolean", nullable=true)
     *
     * @Expose
     */
    private $newsletter;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     *
     * @Expose
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=10, nullable=true)
     *
     * @Expose
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=3, nullable=true)
     *
     * @Expose
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="mal_username", type="string", length=100, nullable=true)
     */
    private $MALUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="mal_password", type="string", length=100, nullable=true)
     */
    private $MALPassword;

    public function __construct()
    {
        parent::__construct();
        $this->avatar = 'http://www.loop-anime.com/img/defaults/avatar_luffy.png';
    }

    public function hasMALAccess()
    {
        return !empty($this->getMALUsername());
    }

    public function getMALPassword()
    {
        return $this->MALPassword;
    }

    public function setMALPassword($password)
    {
        $this->MALPassword = $password;
        return $this;
    }

    public function getMALUsername()
    {
        return $this->MALUsername;
    }

    public function setMALUsername($username)
    {
        $this->MALUsername = $username;
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Users
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get UserPreferences
     *
     * @return UsersPreferences
     */
    public function getPreferences()
    {
        if ($this->preferences === null) {
            $this->preferences = New UsersPreferences($this);
        }
        return $this->preferences;
    }

    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    public function setFacebookId($id)
    {
        $this->facebook_id = $id;

        return $this;
    }

    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    public function setFacebookAccessToken($token)
    {
        $this->facebook_access_token = $token;

        return $this;
    }

    public function getGoogleId()
    {
        return $this->google_id;
    }

    public function setGoogleId($id)
    {
        $this->google_id = $id;

        return $this;
    }

    public function getGoogleAccessToken()
    {
        return $this->google_access_token;
    }

    public function setGoogleAccessToken($token)
    {
        $this->google_access_token = $token;

        return $this;
    }

    public function getTraktId()
    {
        return $this->trakt_id;
    }

    public function setTraktId($id)
    {
        $this->trakt_id = $id;

        return $this;
    }

    public function hasTraktAccess()
    {
        return !empty($this->trakt_access_token);
    }

    public function getTraktAccessToken()
    {
        return $this->trakt_access_token;
    }

    public function setTraktAccessToken($token)
    {
        $this->trakt_access_token = $token;

        return $this;
    }

    public function convert2Array()
    {
        return [
            "id" => $this->getId(),
            "avatar" => $this->getAvatar(),
            "birthdate" => $this->getBirthdate(),
            "username" => $this->getUsername(),
            "country" => $this->getCountry(),
            "email" => $this->getEmail(),
            "lastLogin" => $this->getLastLogin(),
            "lang" => $this->getLang(),
            "newsletter" => $this->getNewsletter(),
            "createTime" => $this->getCreateTime(),
            "status" => $this->getStatus(),
            "confirmationToken" => $this->getConfirmationToken()
        ];
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return Users
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAvatarAbsolutePath()
    {
        return $this->getAvatarUploadRootDir() . '/' . $this->getAvatar();
    }

    public function getAvatarWebPath()
    {
        $avatar = $this->avatar;
        if (empty($this->avatar)) {
            $avatar = 'img/defaults/avatar.jpg';
        }
        if (stripos($avatar, "http") !== 0) {
            $avatar = $this->getAvatarUploadDir() . '/' . $this->getAvatar(); ;
        }
        return $avatar;
    }

    public function getAvatarUploadRootDir()
    {
        return __DIR__ . '/../../../../web/' . $this->getAvatarUploadDir();
    }

    public function getAvatarUploadDir()
    {
        return 'img/avatar';
    }

    /**
     * Sets avatarFile.
     *
     * @param UploadedFile $avatarFile
     */
    public function setAvatarFile(UploadedFile $avatarFile = null)
    {
        $this->avatarFile = $avatarFile;
    }

    /**
     * Get avatarFile.
     *
     * @return UploadedFile
     */
    public function getAvatarFile()
    {
        return $this->avatarFile;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set birthdate
     *
     * @param \DateTime $birthdate
     * @return Users
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsernameWeb()
    {
        return substr($this->username, 0, 15) . (strlen($this->username) > 15 ? '...' : '');
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Users
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Users
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Users
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set lang
     *
     * @param string $lang
     * @return Users
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return integer
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set newsletter
     *
     * @param integer $newsletter
     * @return Users
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get createTime
     *
     * @return \DateTime
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     * @return Users
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Users
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function validate(ExecutionContextInterface $context)
    {
    }

    public function uploadAvatar()
    {
        // if file is null exit upload
        if (null === $this->getAvatarFile()) {
            return;
        }

        // set a random filename to the new avatar
        $fileExt = pathinfo($this->getAvatarFile()->getClientOriginalName(), PATHINFO_EXTENSION);
        $fileName = $this->generateAvatarName() . '.' . $fileExt;

        //
        // move takes the target directory and then the
        // target filename to move to
        $this->getAvatarFile()->move(
            $this->getAvatarUploadRootDir(),
            $fileName
        );

        // remove old avatar file image if exists
        if (!empty($this->avatar)) $this->removeAvatar($this->avatar);
        // set the filename with the name you've saved the file
        $this->avatar = $fileName;

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
     * @param string $file
     */
    public function removeAvatar($file)
    {
        $file_path = $this->getAvatarUploadRootDir() . '/' . $file;
        if (file_exists($file_path)) unlink($file_path);
    }

    public function generateAvatarName()
    {
        return substr("abcdefghijklmnopqrstuvwxyz", mt_rand(0, 25), 1) . substr(md5(time( )), 1);
    }

}
