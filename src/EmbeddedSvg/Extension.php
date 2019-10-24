<?php

namespace PolywebCz\EmbeddedSvg;

use Nette\DI\CompilerExtension;

class Extension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$definition = $this->getContainerBuilder()->getDefinition('latte.latteFactory');
		if (class_exists(\Nette\DI\Definitions\FactoryDefinition::class)) { // Nette DI v3 compatibility
			$definition = $definition->getResultDefinition();
		}

		$definition
			->addSetup(
                '?->onCompile[] = function ($engine) { '
				. Macro::class . '::install($engine->getCompiler(), '
				. MacroSetting::class . '::createFromArray(?)'
				. ');}',
				['@self', $this->getConfig()]
			);
	}

}
