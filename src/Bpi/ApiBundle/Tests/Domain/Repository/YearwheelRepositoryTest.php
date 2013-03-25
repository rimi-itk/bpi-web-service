<?php
namespace Bpi\ApiBundle\Tests\Domain;

use Bpi\ApiBundle\Domain\Repository\YearwheelRepository;
use Bpi\ApiBundle\Domain\ValueObject\Yearwheel;

class YearwheelRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Bpi\ApiBundle\Domain\Repository\YearwheelRepository
     */
    protected $repo;

    public function setUp()
    {
        $this->repo = new YearwheelRepository();
    }

    public function testFind()
    {
        // Easter
        $this->assertEquals('Easter', $this->repo->find('Easter')->name());
        $easter = new Yearwheel('Easter');
        $this->assertTrue($easter->equals($this->repo->find('Easter')));

        // Christmas
        $this->assertEquals('Christmas', $this->repo->find('Christmas')->name());

        // Summer
        $this->assertEquals('Summer', $this->repo->find('Summer')->name());

        // Winter
        $this->assertEquals('Winter', $this->repo->find('Winter')->name());
        $this->assertEquals(new Yearwheel('Winter'), $this->repo->find('Winter'));
    }
}
