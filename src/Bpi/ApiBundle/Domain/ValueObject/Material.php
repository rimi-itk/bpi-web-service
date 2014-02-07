<?php
namespace Bpi\ApiBundle\Domain\ValueObject;

use Bpi\ApiBundle\Domain\ValueObject\AgencyId;

class Material implements IValueObject
{
    protected $library_code;
    protected $faust_code;
    protected $tag;

    /**
     * This could be ID, FAUST or ISBN number
     *
     * @param  string $fully_qualified_code
     * @return Material
     */
    public static function create($fully_qualified_code)
    {
        // @TODO: detect ISBN and other types
        if (!preg_match('~^(\d+)\-([^\:]+)\:(.+)~', $fully_qualified_code, $match)) {
            throw new \InvalidArgumentException("Incorrect material number: ". $fully_qualified_code);
        }

        return new static($match[1], $match[3], $match[2]);
    }

    public function __construct($library_code, $faust_code, $tag)
    {
        $this->library_code = $library_code;
        $this->faust_code = $faust_code;
        $this->tag = $tag;
    }

    /**
     * @param \Bpi\ApiBundle\Domain\ValueObject\Material $material
     * @return boolean
     */
    public function equals(IValueObject $material)
    {
        if (get_class($this) != get_class($tag)) {
            return false;
        }

        return $this->library_code == $material->library_code
            && $this->faust_code == $material->faust_code
            && $this->tag == $material->tag;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->library_code . '-' . $this->tag . ':' . $this->faust_code;
    }

    /**
     * Check if library of material equals given agency
     *
     * @param  AgencyID $external_library
     * @return boolean
     */
    public function isLibraryEquals(AgencyID $external_library) {
        $library = new AgencyId($this->library_code);

        return $library->equals($external_library);
    }

    /**
     * Reassign library code to given Agency
     *
     * @param  AgencyID $agency_id
     * @return Material modified copy
     */
    public function reassignToAgency(AgencyID $agency_id) {
        $modified = clone $this;
        $modified->library_code = (string) $agency_id;

        return $modified;
    }
}
