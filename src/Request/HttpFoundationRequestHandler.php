<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableBundle\Request;

use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Personalization\PersonalizationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class HttpFoundationRequestHandler implements RequestHandlerInterface
{
    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function handle(DataTableInterface $dataTable, mixed $request = null): void
    {
        if (null === $request) {
            return;
        }

        if (!$request instanceof Request) {
            throw new \InvalidArgumentException();
        }

        $this->filter($dataTable, $request);
        $this->sort($dataTable, $request);
        $this->personalize($dataTable, $request);
        $this->paginate($dataTable, $request);
    }

    private function filter(DataTableInterface $dataTable, Request $request): void
    {
        $filtrationParameterName = $dataTable->getConfig()->getFiltrationParameterName();

        $filtrationData = FiltrationData::fromArray([
            'filters' => $this->extractQueryParameter($request, "[$filtrationParameterName]", []),
        ]);

        if ($filtrationData->isEmpty()) {
            return;
        }
        
        $dataTable->filter($filtrationData);
    }

    private function sort(DataTableInterface $dataTable, Request $request): void
    {
        $sortParameterName = $dataTable->getConfig()->getSortParameterName();

        $sortingData = new SortingData();

        $sortField = $this->extractQueryParameter($request, "[$sortParameterName][field]");
        $sortDirection = $this->extractQueryParameter($request, "[$sortParameterName][direction]", 'DESC');

        if (null !== $sortField) {
            $sortingData->addField(new SortingField($sortField, $sortDirection));
        }

        $dataTable->sort($sortingData);
    }

    private function paginate(DataTableInterface $dataTable, Request $request): void
    {
        $pageParameterName = $dataTable->getConfig()->getPageParameterName();
        $perPageParameterName = $dataTable->getConfig()->getPerPageParameterName();

        $page = $this->extractQueryParameter($request, "[$pageParameterName]", 1);
        $perPage = $this->extractQueryParameter($request, "[$perPageParameterName]", 25);

        $dataTable->paginate(new PaginationData(
            page: (int) $page,
            perPage: (int) $perPage,
        ));
    }

    private function personalize(DataTableInterface $dataTable, Request $request): void
    {
        $personalizationData = (array) $request->request->get($dataTable->getConfig()->getPersonalizationParameterName());

        if (empty($personalizationData)) {
            return;
        }

        $dataTable->personalize(new PersonalizationData($personalizationData));
    }

    private function extractQueryParameter(Request $request, string $path, mixed $default = null): mixed
    {
        return $this->propertyAccessor->getValue($request->query->all(), $path) ?? $default;
    }
}