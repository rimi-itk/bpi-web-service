<?php
/**
 * Created by PhpStorm.
 * User: avoykov
 * Date: 27.08.2015
 * Time: 11:18
 */
namespace Bpi\RestMediaTypeBundle;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("result")
 */
class XmlGroupOperation extends XmlResponse {
    /**
     * @var
     */
    private $skipped;

    /**
     * @var
     *
     * @Serializer\XmlList(inline = false, entry = "item")
     * @Serializer\Type("array<string>")
     */
    private $skipped_list;

    /**
     * @var
     */
    private $success;

    /**
     * @var
     *
     * @Serializer\XmlList(inline = false, entry = "item")
     * @Serializer\Type("array<string>")
     */
    private $success_list;

    /**
     * @return mixed
     */
    public function getSuccessList()
    {
        return $this->success_list;
    }

    /**
     * @param mixed $success_list
     */
    public function setSuccessList($success_list)
    {
        $this->success_list = $success_list;
    }

    /**
     * @return mixed
     */
    public function getSkippedList()
    {
        return $this->skipped_list;
    }

    /**
     * @param mixed $skipped_list
     */
    public function setSkippedList($skipped_list)
    {
        $this->skipped_list = $skipped_list;
    }

    /**
     * @return mixed
     */
    public function getSkipped()
    {
        return $this->skipped;
    }

    /**
     * @param mixed $skipped
     */
    public function setSkipped($skipped)
    {
        $this->skipped = $skipped;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param mixed $success
     */
    public function setSuccess($success)
    {
        $this->success = $success;
    }
}
