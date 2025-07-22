<?php

namespace WHSymfony\WHItemIndexTableBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use WHSymfony\WHItemIndexTableBundle\Twig\WHItemIndexTableExtension;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class WHItemIndexTableBundle extends AbstractBundle
{
	protected string $extensionAlias = 'wh_index_table';

	public function configure(DefinitionConfigurator $definition): void
	{
		$definition->rootNode()
			->children()
				->booleanNode('toggle_direction_for_same_column')
					->defaultFalse()
					->info('Whether clicking again on the same sort-by column should toggle the sort direction for that column.')
				->end()
			->end()
		;
	}

	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$container->services()
			->set('wh_index_table.twig.extension', WHItemIndexTableExtension::class)
				->args([
					$config['toggle_direction_for_same_column'],
					service('request_stack')
				])
				->tag('twig.extension')
		;
	}
}
