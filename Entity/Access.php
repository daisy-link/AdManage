<?php

namespace Plugin\AdManage\Entity;

use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Customer;

class Access extends AbstractEntity
{

    protected $id;
    protected $unique_id;
    protected $Customer;
    protected $referrer;
    protected $ad_code;
    protected $ip_address;
    protected $user_agent;
    protected $page;
    protected $history;
    protected $create_date;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->unique_id;
    }

    /**
     * @param string $uniqueId
     * @return $this
     */
    public function setUniqueId($uniqueId)
    {
        $this->unique_id = $uniqueId;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    /**
     * @param Customer $Customer
     * @return $this
     */
    public function setCustomer($Customer)
    {
        $this->Customer = $Customer;

        return $this;
    }

    /**
     * @return string
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * @param string $referrer
     * @return $this
     */
    public function setReferrer($referrer)
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdCode()
    {
        return $this->ad_code;
    }

    /**
     * @param string $adCode
     * @return $this
     */
    public function setAdCode($adCode)
    {
        $this->ad_code = $adCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param string $ipAddress
     * @return $this
     */
    public function setIpAddress($ipAddress)
    {
        $this->ip_address = $ipAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->user_agent;
    }

    /**
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->user_agent = $userAgent;

        return $this;
    }

    /**
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param string $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return integer
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param integer $history
     * @return $this
     */
    public function setHistory($history)
    {
        $this->history = $history;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param \DateTime $updateDate
     * @return $this
     */
    public function setCreateDate($updateDate)
    {
        $this->create_date = $updateDate;

        return $this;
    }
}