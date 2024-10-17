<?php
/**
 * @author Shilpa G
 * @copyright Copyright (c) 2024 VML All rights reserved.
 * @package Vml_CustomerImport
 */
declare(strict_types=1);

namespace Vml\CustomerImport\Model;

use Vml\CustomerImport\Api\CustomerImportInterface;
use Vml\CustomerImport\Api\ProfileInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\AlreadyExistsException;
use Vml\CustomerImport\Model\Import\CsvImport;
use Vml\CustomerImport\Model\Import\JsonImport;

class CustomerImport implements CustomerImportInterface
{
    protected $profileFactory;
    protected $customerRepository;
    protected $dataObjectHelper;
    protected $customerDataFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper $dataObjectHelper,
        CustomerInterfaceFactory $customerDataFactory
    ) {
        $this->profileFactory = $objectManager;
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerDataFactory = $customerDataFactory;
    }

    public function import(string $profile, string $source)
    {
        $profileClass = $this->getProfileClass($profile);
        if (!$profileClass) {
            throw new LocalizedException(__('Profile %1 not found', $profile));
        }

        $profileProcessor = $this->profileFactory->create($profileClass);
        $customers = $profileProcessor->process($source);

        foreach ($customers as $customerData) {
            try {
                $this->saveOrUpdateCustomer($customerData);
            } catch (\Exception $e) {
                throw new LocalizedException(__("Error importing customer: %1", $e->getMessage()));
            }
        }
    }

    protected function getProfileClass(string $profile): ?string
    {
        $profiles = [
            'sample-csv' => CsvImport::class,
            'sample-json' => JsonImport::class
        ];

        return $profiles[$profile] ?? null;
    }

    protected function saveOrUpdateCustomer(array $customerData)
    {
        // Check if the customer exists by email
        try {
            $existingCustomer = $this->customerRepository->get($customerData['email']);
            $this->updateCustomer($existingCustomer, $customerData);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->createCustomer($customerData);
        }
    }

    protected function createCustomer(array $customerData)
    {
        $customer = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray($customer, $customerData, \Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerRepository->save($customer);
    }

    protected function updateCustomer($existingCustomer, array $customerData)
    {
        $this->dataObjectHelper->populateWithArray($existingCustomer, $customerData, \Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerRepository->save($existingCustomer);
    }
}
