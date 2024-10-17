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

class CsvImport implements ProfileInterface
{
    public function process(string $source): array
    {
        // Check if file exists and is readable
        if (!file_exists($source) || !is_readable($source)) {
            throw new LocalizedException(__('The file %1 cannot be read.', $source));
        }
        $file = fopen($source, 'r');
        $header = fgetcsv($file); // Get the headers
        $data = [];

        $headerMap = [
            'emailaddress' => 'email',
            'fname' => 'firstname',
            'lname' => 'lastname'
        ];

        // Process each row of the CSV
        while (($row = fgetcsv($file)) !== false) {
            $customerData = [];
            foreach ($header as $index => $column) {
                // Map custom header names to the expected Magento field names
                $field = $headerMap[strtolower($column)] ?? strtolower($column);
                $customerData[$field] = $row[$index];
            }
            $data[] = $customerData;
        }

        fclose($file);

        return $data;
    }
}
