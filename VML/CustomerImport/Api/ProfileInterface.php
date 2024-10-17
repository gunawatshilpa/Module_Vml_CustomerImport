<?php
/**
 * @author Shilpa G
 * @copyright Copyright (c) 2024 VML All rights reserved.
 * @package Vml_CustomerImport
 */

namespace Vml\CustomerImport\Api;

interface ProfileInterface
{
    public function process(string $source): array;
}
