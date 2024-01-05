<?php

namespace Starfruit\HelperBundle\Command\Asset;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Starfruit\HelperBundle\Tool\AssetTool;

class CleanerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('starfruit:asset:clean-unused')
            ->setDescription('Clean unused assets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeInfo('RUN starfruit:asset:clean-unused');

        AssetTool::cleanUnusedAssets();

        return 1;
    }
}
