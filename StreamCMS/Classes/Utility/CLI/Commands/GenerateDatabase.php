<?php

declare(strict_types=1);

namespace StreamCMS\Utility\CLI\Commands;

use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use StreamCMS\Database\StreamCMS\StreamCMSDB;
use StreamCMS\Database\StreamCMS\StreamCMSModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateDatabase extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('orm:generate-schema')
            ->setAliases(['orm:generate:schema'])
            ->setDescription(
                'Generate database schema'
            );
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = StreamCMSDB::get()->getEntityManager();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $classes = [
            $em->getClassMetadata('Entities\User'),
            $em->getClassMetadata('Entities\Profile')
        ];
        $tool->createSchema($classes);
        $extend = StreamCMSModel::class;
        $ui = new SymfonyStyle($input, $output);
        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($em);
        $metadatas = $cmf->getAllMetadata();
        $metadatas = MetadataFilter::filter($metadatas, $input->getOption('filter'));

        return 0;
    }
}
