<?php

namespace Lights\Commands;

use Carbon\Carbon;
use Phue\Client;
use Phue\Command\CreateSchedule;
use Phue\Command\SetLightState;
use Phue\Light;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FadeIn extends Command
{
    /** @var \Phue\Client */
    private $hueClient;

    private static $STEPS = 51;

    /** @var float 255**(1/51) */
    private static $GROWTH = 1.1147745937;

    public function __construct(Client $hue)
    {
        parent::__construct();

        $this->hueClient = $hue;
    }

    protected function configure()
    {
        $this->setName('fadein')
            ->setDescription('Fade in a light.')
            ->setHelp('This command will fade in the given light.')
            ->addArgument(
                'minutes',
                InputArgument::OPTIONAL,
                'Fade duration in minutes.',
                10
            )
            ->addargument('light',
                InputArgument::OPTIONAL,
                'Light ID to fade in. See `lights list` command.',
                (int) getenv('HUE_FADEIN_LIGHT_ID')
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->clearSchedule($output);

        foreach ($this->hueClient->getLights() as $light) {
            if ($light->getId() === (int) $input->getArgument('light')) {
                $this->scheduleFadeIn($light, $input->getArgument('minutes') * 60, $output);
                $output->writeln('<info>Done.</info>');
                break;
            }
        }

    }

    private function scheduleFadeIn(Light $light, $duration, OutputInterface $output)
    {
        $time = Carbon::now()->addSeconds(2);

        for ($step = 1; $step <= self::$STEPS; $step++) {
            $groupCommand = new SetLightState($light);
            if ($this->calculateBrightness($step) > ($brightness ?? 0)) {
                $brightness = $this->calculateBrightness($step);
                $groupCommand->brightness($brightness);
                $groupCommand->hue(6244);
                $groupCommand->saturation(149);
                $groupCommand->on(true);

                $scheduleCommand = new CreateSchedule($this->commandId(), $time, $groupCommand);
                $scheduleCommand->description('Fade in light '.$light->getName());

                $this->hueClient->sendCommand($scheduleCommand);

                $output->writeln(
                    sprintf('Schedule brightness %s at %s.', $brightness, $time->format('H:i:s')),
                    OutputInterface::VERBOSITY_VERY_VERBOSE
                );
            }

            $time->addMilliseconds($duration / self::$STEPS * 1000);
        }
    }

    private function clearSchedule(OutputInterface $output)
    {
        foreach ($this->hueClient->getSchedules() as $schedule) {
            if ($schedule->getName() === $this->commandId()) {
                $schedule->delete();
                $output->writeln(
                    '<comment>Scheduled fadein deleted.</comment>',
                    OutputInterface::VERBOSITY_DEBUG
                );
            }
        }
    }

    private function commandId()
    {
        return hash('md5', $this->getName());
    }

    private function calculateBrightness($step)
    {
        return min(SetLightState::BRIGHTNESS_MAX, ceil(self::$GROWTH ** $step));
    }
}
