<?php
namespace Bpi\ApiBundle\Domain\Entity;

class History
{
    private $id;
    /**
     *
     * @var \Bpi\ApiBundle\Domain\Agregate\Node
     */
    private $node;
    private $agency;
    private $date;
    private $datetime;
    private $action;

    /**
     *
     * @param \Bpi\ApiBundle\Domain\Agregate\Node $node
     * @param \Bpi\ApiBundle\Domain\ValueObject\AgencyId $agency
     * @param \Datetime $datetime
     * @param string $action
     */
    public function __construct($node, $agency, \Datetime $datetime, $action) {
      $this->node = $node;
      $this->agency = $agency->id();
      $this->datetime = $datetime;
      $this->date = $datetime->format('Y-m-d');
      $this->action = $action;
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
     * Set node
     *
     * @param Bpi\ApiBundle\Domain\Aggregate\Node $node
     * @return History
     */
    public function setNode($node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * Get node
     *
     * @return string
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Set agency
     *
     * @param Bpi\ApiBundle\Domain\Aggregate\Agency $agency
     * @return History
     */
    public function setAgency($agency)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return Bpi\ApiBundle\Domain\Aggregate\Agency
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Set datetime
     *
     * @param \Datetime $time
     * @return History
     */
    public function setDatetime(\Datetime $time)
    {
        $this->datetime = $time;

        return $this;
    }

    /**
     * Get datetime
     *
     * @return \Datetime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set date
     * Date in yyyy-mm-dd fromat
     *
     * @param string $time
     * @return History
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     * Date in yyyy-mm-dd fromat
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return History
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

}
