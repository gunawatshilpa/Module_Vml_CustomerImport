<?php
/**
 * @author Shilpa G
 * @copyright Copyright (c) 2024 VML All rights reserved.
 * @package Vml_CustomerImport
 */
declare(strict_types=1);

namespace Vml\CustomerImport\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vml\CustomerImport\Api\CustomerImportInterface;

class CustomerImportCommand extends Command
{
    const PROFILE_ARGUMENT = 'profile';
    const SOURCE_ARGUMENT = 'source';

    protected $customerImport;

    public function __construct(CustomerImportInterface $customerImport)
    {
        $this->customerImport = $customerImport;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('customer:import')
            ->setDescription('Import customers from different profiles')
            ->addArgument(self::PROFILE_ARGUMENT, InputArgument::REQUIRED, 'Profile Name')
            ->addArgument(self::SOURCE_ARGUMENT, InputArgument::REQUIRED, 'Source File');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profile = $input->getArgument(self::PROFILE_ARGUMENT);
        $source = $input->getArgument(self::SOURCE_ARGUMENT);

        try {
            $this->customerImport->import($profile, $source);
            $output->writeln("<info>Customer import was successful</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>Error during import: " . $e->getMessage() . "</error>");
        }

        return Cli::RETURN_SUCCESS;
    }
}
