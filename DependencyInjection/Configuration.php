<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SwiftmailerDoctrineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fxp_swiftmailer_doctrine');

        $rootNode
            ->children()
                ->scalarNode('spool_email_class')->defaultValue('Fxp\Component\SwiftmailerDoctrine\Model\SpoolEmailInterface')->end()
                ->scalarNode('override_send_command')->defaultTrue()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
