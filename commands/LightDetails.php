<?php

namespace Lights\Commands;

use Phue\Client;
use Phue\Command\GetLightById;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LightDetails extends Command
{
    /** @var \Phue\Client */
    private $hueClient;

    public function __construct(Client $hue)
    {
        parent::__construct();

        $this->hueClient = $hue;
    }

    protected function configure()
    {
        $this->setName('details')
            ->setDescription('Shows details for a light.')
            ->setHelp('This command will fade in the given light.')
            ->addArgument(
                'light',
                InputArgument::REQUIRED,
                'The light ID.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Phue\Light $light */
        $light = $this->hueClient->sendCommand(
            new GetLightById(3)
        );

        $table = (new Table($output))
            ->setHeaders([
                'ID',
                'State',
                'Type',
                'Hue',
                'Saturation',
                'Brightness',
                'Software version',
            ])
            ->setRows([
                [
                    $light->getId(),
                    $light->isOn() ? 'On' : 'Off',
                    $light->getType(),
                    $light->getHue(),
                    $light->getSaturation(),
                    $light->getBrightness(),
                    $light->getSoftwareVersion(),
                ],
            ]);

        $table->render();
    }
}
