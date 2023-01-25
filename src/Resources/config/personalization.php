<?php

declare(strict_types=1);

use Kreyu\Bundle\DataTableBundle\Personalization\Persistence\CachePersonalizationPersister;
use Kreyu\Bundle\DataTableBundle\Personalization\Persistence\PersonalizationPersisterInterface;
use Kreyu\Bundle\DataTableBundle\Personalization\Persistence\PersonalizationPersisterSubjectProviderInterface;
use Kreyu\Bundle\DataTableBundle\Personalization\Persistence\TokenStoragePersonalizationPersisterSubjectProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services
        ->set('kreyu_data_table.personalization.persister.cache', CachePersonalizationPersister::class)
        ->args([service('kreyu_data_table.persistence.cache_pool')])
        ->alias(PersonalizationPersisterInterface::class, 'kreyu_data_table.personalization.persister.cache');

    $services
        ->set('kreyu_data_table.personalization.persister_subject_provider.token_storage', TokenStoragePersonalizationPersisterSubjectProvider::class)
        ->args([service('security.token_storage')])
        ->alias(PersonalizationPersisterSubjectProviderInterface::class, 'kreyu_data_table.personalization.persister_subject_provider.token_storage');
};