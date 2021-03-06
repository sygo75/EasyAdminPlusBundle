<?php

namespace Lle\EasyAdminPlusBundle\DependencyInjection;

use Lle\EasyAdminPlusBundle\EditInPlaceType\EipTypeInterface;
use Lle\EasyAdminPlusBundle\Filter\FilterType\FilterTypeInterface;
use Lle\EasyAdminPlusBundle\Service\Batch\BatchInterface;
use Lle\EasyAdminPlusBundle\Service\Exporter\ExporterInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class LleEasyAdminPlusExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        if ($container->hasParameter('twig.form.resources')) {
            $container->setParameter('twig.form.resources', array_merge(
                ['@LleEasyAdminPlus/form/widget.html.twig'],
                $container->getParameter('twig.form.resources')
            ));
        }
        
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $config = $this->processConfigTranslator($config, $container);

        $container->setParameter('easy_admin_plus', $config);
        $container->setParameter('easy_admin_plus.translator.locales', $config['translator']['locales']);
        $container->registerForAutoconfiguration(FilterTypeInterface::class)->addTag('lle.easy_admin_plus.filter');
        $container->registerForAutoconfiguration(EipTypeInterface::class)->addTag('lle.easy_admin_plus.edit_in_place');
        $container->registerForAutoconfiguration(BatchInterface::class)->addTag('lle.easy_admin_plus.batch');
        $container->registerForAutoconfiguration(ExporterInterface::class)->addTag('lle.exporter');
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('form.yaml');
        $loader->load('filter.yaml');
        $loader->load('editinplace.yaml');
    }

    private function processConfigTranslator(array $config, ContainerBuilder $container): array
    {
        if (empty($config['translator']['paths'])) {
            $config['translator']['paths'] = [
                $container->getParameter('kernel.project_dir').'/translations',
            ];
        }
        if (empty($config['translator']['locales'])) {
            if($container->hasParameter('locale') && $container->getParameter('locale')){
                $config['translator']['locales'] = [$container->getParameter('locale')];
            }else{
                $config['translator']['locales'] = [$container->getParameter('kernel.default_locale')];
                $container->setParameter('locale', $container->getParameter('kernel.default_locale'));
            }
        }

        return $config;
    }
}
