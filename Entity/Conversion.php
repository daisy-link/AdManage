<?php

namespace Plugin\AdManage\Entity;

use Eccube\Entity\AbstractEntity;

class Conversion extends AbstractEntity
{
    protected $id;
    protected $Order;
    protected $unique_id;

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
     * @return \Eccube\Entity\Order
     */
    public function getOrder()
    {
        return $this->Order;
    }

    /**
     * @param \Eccube\Entity\Order
     * @return $this
     */
    public function setOrder($Order)
    {
        $this->Order = $Order;

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
}