<?php

namespace PolywebCz\EmbeddedSvg;

class MacroSetting
{

	/** @var string */
	public $baseDir;

	/** @var string */
	public $macroName = 'svg';

	/** @var int */
	public $libXmlOptions = LIBXML_NOBLANKS;

	/** @var bool */
	public $prettyOutput = false;

	/** @var array */
	public $defaultAttributes = [];

	/** @var callable[] */
	public $onLoad = [];

	public static function createFromArray(array $setting): self
	{
		$me = new self();
		foreach ($setting as $property => $value) {
			$me->{$property} = $value;
		}
		return $me;
	}


	public function & __get($name)
	{
		throw new \LogicException('Cannot read an undeclared property ' . static::class . "::\$$name.");
	}


	public function __set($name, $value)
	{
		throw new \LogicException('Cannot write to an undeclared property ' . static::class . "::\$$name.");
	}

}
