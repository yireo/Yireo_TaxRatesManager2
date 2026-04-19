<?php
declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TaxRatesManager2\Provider\OnlineRates as OnlineRatesProvider;
use Yireo\TaxRatesManager2\Provider\StoredRates as StoredRatesProvider;

class ImportAllCommand extends Command
{
    private StoredRatesProvider $storedRatesProvider;
    private OnlineRatesProvider $onlineRatesProvider;

    public function __construct(
        StoredRatesProvider $storedRatesProvider,
        OnlineRatesProvider $onlineRatesProvider,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->storedRatesProvider = $storedRatesProvider;
        $this->onlineRatesProvider = $onlineRatesProvider;
    }

    /**
     * Initialization of the command.
     */
    protected function configure()
    {
        $this->setName('yireo_taxratesmanager:import:all');
        $this->setDescription('Import all available tax rates');
        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rates = $this->onlineRatesProvider->getRates();
        foreach ($rates as $rate) {
            $this->storedRatesProvider->saveRate($rate);
        }

        return Command::SUCCESS;
    }
}
