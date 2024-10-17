<?php
/**
 * @author Shilpa G
 * @copyright Copyright (c) 2024 VML All rights reserved.
 * @package Vml_CustomerImport
 */
declare(strict_types=1);

namespace Vml\CustomerImport\Model\Import;

use Vml\CustomerImport\Api\ProfileInterface;
use Magento\Framework\Exception\LocalizedException;

class JsonImport implements ProfileInterface
{
    public function process(string $source): array
    {
        if (!file_exists($source) || !is_readable($source)) {
            throw new LocalizedException(__('The file %1 cannot be read.', $source));
        }

        $data = json_decode(file_get_contents($source), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new LocalizedException(__('Invalid JSON format: %1', json_last_error_msg()));
        }

        // Map the JSON keys to Magento's expected customer fields
        $mappedData = [];
        foreach ($data as $item) {
            $customerData = [
                'firstname' => $item['fname'] ?? $item['firstname'] ?? null,
                'lastname' => $item['lname'] ?? $item['lastname'] ?? null,
                'email' => $item['emailaddress'] ?? $item['email'] ?? null
            ];

            if ($customerData['firstname'] && $customerData['lastname'] && $customerData['email']) {
                $mappedData[] = $customerData;
            }
        }

        return $mappedData;
    }
}
