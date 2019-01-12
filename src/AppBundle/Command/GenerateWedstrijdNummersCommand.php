<?php

namespace AppBundle\Command;

use AppBundle\Entity\ScoresRepository;
use AppBundle\Entity\WedstrijdRonde;
use AppBundle\Entity\WedstrijdRondeRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateWedstrijdNummersCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:generate-wedstrijd-nummers')
            ->setDescription('Wedstrijdnummers genereren');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ScoresRepository $wedstrijdRondeRepository */
        $scoresRepository = $this->getContainer()->get("doctrine")->getRepository("AppBundle:Scores");
        $scoresRepository->resetWedstrijdNummers();

        /** @var WedstrijdRondeRepository $repository */
        $wedstrijdRondeRepository = $this->getContainer()->get("doctrine")->getRepository("AppBundle:WedstrijdRonde");

        /** @var WedstrijdRonde[] $wedstrijdRondes */
        $wedstrijdRondes = $wedstrijdRondeRepository->findBy([], ['startTijd' => 'ASC', 'baan' => 'ASC']);

        $toestellen = ['Sprong', 'Brug', 'Balk', 'Vloer'];

        $wedstrijdNumber = 1;
        foreach ($wedstrijdRondes as $wedstrijdRonde) {
            foreach ($toestellen as $toestel) {
                foreach ($wedstrijdRonde->getTeams() as $team) {
                    if ($team->getBeginToestel() !== $toestel) {
                        continue;
                    }

                    foreach ($team->getTurnsters() as $turnster) {
                        if ($turnster->getVoornaam() === 'leeg') {
                            continue;
                        }

                        $scores = $turnster->getScores();
                        $scores->setWedstrijdnummer($wedstrijdNumber);
                        $this->addToDB($scores);

                        $wedstrijdNumber++;
                    }
                }
            }
        }
    }

    private function addToDB($object)
    {
        $em = $this->getContainer()->get("doctrine")->getManager();
        $em->persist($object);
        $em->flush();
    }
}
