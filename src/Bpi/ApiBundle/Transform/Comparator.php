<?php
namespace Bpi\ApiBundle\Transform;

class Comparator
{
	protected $result;
	
	public function __construct($a, $b, $order)
	{
		if (gettype($a) != gettype($b))
			throw new \RuntimeException('Cant compare operands with different types: '.  gettype($a).', '.gettype($b));
		
		$result = 0;
		
		if ($a instanceof \DateTime)
		{
			if ($a > $b)
				$result = 1;
			elseif($a < $b )
				$result = -1;
			else
				$result = 0;
		}
		else 
		{
			$result = strcmp($a, $b);
		}
		
		$this->result = $result * (int) $order;
	}
	
	public function getResult()
	{
		if ($this->result < 0)
			return -1;
		if ($this->result > 0)
			return 1;
		return $this->result;
	}
}