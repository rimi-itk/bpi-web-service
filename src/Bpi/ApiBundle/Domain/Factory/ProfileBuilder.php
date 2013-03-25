<?php
namespace Bpi\ApiBundle\Domain\Factory;

use Bpi\ApiBundle\Domain\ValueObject\Yearwheel;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;
use Bpi\ApiBundle\Domain\ValueObject\Tag;
use Bpi\ApiBundle\Domain\ValueObject\ValueObjectList as VOList;
use Bpi\ApiBundle\Domain\Repository\YearwheelRepository;
use Bpi\ApiBundle\Domain\Entity\Profile;

class ProfileBuilder
{
    protected $yearwheel, $audience, $category, $tags;

    /**
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Audience $audience
     * @return \Bpi\ApiBundle\Domain\Factory\ProfileBuilder
     */
    public function audience(Audience $audience)
    {
        $this->audience = $audience;
        return $this;
    }

    /**
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Category $category
     * @return \Bpi\ApiBundle\Domain\Factory\ProfileBuilder
     */
    public function category(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Yearwheel $yearwheel
     * @return \Bpi\ApiBundle\Domain\Factory\ProfileBuilder
     */
    public function yearwheel(Yearwheel $yearwheel)
    {
        $repo = new YearwheelRepository();
        if (!$repo->contains($yearwheel))
        {
            throw new InvalidStateException('Incorrect yearwheel value');
        }
        $this->yearwheel = $yearwheel;
        return $this;
    }

    /**
     * Accepts flatten tags
     *
     * @param string $tags
     * @return \Bpi\ApiBundle\Domain\Factory\ProfileBuilder
     */
    public function tags($tags)
    {
        $tags = explode(",", $tags);
        if (!count($tags))
            return;

        $tags = array_unique($tags);
        array_walk($tags, function(&$e){
            $e = new Tag(trim($e));
        });

        $this->tags = new VOList($tags);
        return $this;
    }

    /**
     *
     * @return boolean
     */
    protected function isValidForBuild()
    {
        return !(is_null($this->audience)
            || is_null($this->category)
        );
    }

    /**
     *
     * @return Bpi\ApiBundle\Domain\Entity\Profile
     * @throws \RuntimeException
     */
    public function build()
    {
        if (!$this->isValidForBuild())
            throw new \RuntimeException('Invalid state: can not build');

        return new Profile($this->audience, $this->category, $this->yearwheel, $this->tags);
    }
}
