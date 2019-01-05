<?php

namespace Lights\Commands;

use Phue\Client;
use Phue\Command\GetLightById;
use Phue\Light;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListLights extends Command
{
    protected static $defaultName = 'list';

    /** @var \Phue\Client */
    protected $hueClient;

    public function __construct(Client $hue)
    {
        parent::__construct();

        $this->hueClient = $hue;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Group']);

        foreach ($this->hueClient->getGroups() as $group) {
            foreach ($group->getLightIds() as $lightId) {
                /** @var Light $light */
                $light = $this->hueClient->sendCommand(new GetLightById($lightId));
                $table->addRow([$light->getId(), $light->getName(), $group->getName()]);
            }
        }

        $table->render();
    }
}
