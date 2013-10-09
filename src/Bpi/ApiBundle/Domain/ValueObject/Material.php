<?php
namespace Bpi\ApiBundle\Domain\ValueObject;

class Material implements IValueObject
{
    protected $library_code;
    protected $faust_code;

    public static function create($fully_qualified_code)
    {
        if (!preg_match('~^\d{6}:.+~', $fully_qualified_code))
        {
            throw new \InvalidArgumentException("Incorrect material number: ". $fully_qualified_code);
        }

        list($library_code, $faust_code) = explode(':', $fully_qualified_code);

        return new static($library_code, $faust_code);
    }

    public function __construct($library_code, $faust_code)
    {
        $this->library_code = $library_code;
        $this->faust_code = $faust_code;
    }

    /**
     * @param \Bpi\ApiBundle\Domain\ValueObject\Material $material
     * @return boolean
     */
    public function equals(IValueObject $material)
    {
        if (get_class($this) != get_class($tag))
            return false;

        return $this->library_code == $material->library_code
            && $this->faust_code == $material->faust_code;
    }

    /**
     * @return string
     */
    public function __toString()
    {
    	return $this->library_code . ':' . $this->faust_code;
    }
}
