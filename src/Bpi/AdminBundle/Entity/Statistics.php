<?php

namespace Bpi\AdminBundle\Entity;

class Statistics
{

    private $dateFrom;

    private $dateTo;

    private $agency;

    public function getDateFrom()
    {
        if ($this->dateFrom) {
            return $this->dateFrom->format('Y-m-d');
        }

        return null;
    }

    public function getDateTo()
    {
        if ($this->dateTo) {
            return $this->dateTo->format('Y-m-d');
        }

        return null;
    }

    public function getAgency()
    {
        return $this->agency;
    }

    public function setDateFrom($date)
    {
        $this->dateFrom = $date;
    }

    public function setDateTo($date)
    {
        $this->dateTo = $date;
    }

    public function setAgency($agency)
    {
        $this->agency = $agency;
    }
}
