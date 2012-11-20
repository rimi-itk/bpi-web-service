<?php
namespace  Bpi\ApiBundle\Domain\Command;

interface ICommand
{
	/**
	 * @return CommandResult
	 */
	public function execute();
	
}