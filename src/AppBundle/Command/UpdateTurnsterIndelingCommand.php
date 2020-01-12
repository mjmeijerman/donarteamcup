<?php

namespace AppBundle\Command;

use AppBundle\Entity\WedstrijdRonde;
use AppBundle\Entity\WedstrijdRondeRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateTurnsterIndelingCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:update-turnster-indeling')
            ->setDescription('Updates de turnster indeling op basis van ingeschreven wedstrijdronde');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var WedstrijdRondeRepository $repository */
        $repository = $this->getContainer()->get("doctrine")->getRepository("AppBundle:WedstrijdRonde");

        /** @var WedstrijdRonde[] $wedstrijdRondes */
        $wedstrijdRondes = $repository->findAll();
        foreach ($wedstrijdRondes as $wedstrijdRonde) {
            foreach ($wedstrijdRonde->getTeams() as $team) {
                foreach ($team->getTurnsters() as $turnster) {
                    if (strtolower($turnster->getVoornaam()) !== 'leeg') {
                        $turnster->getScores()->setBaan($wedstrijdRonde->getBaan());
                        $turnster->getScores()->setGroep($turnster->getScores()->getBegintoestel());
                        $turnster->getScores()->setWedstrijddag($wedstrijdRonde->getDag());
                        $turnster->getScores()->setWedstrijdronde($wedstrijdRonde->getRonde());


                        $this->addToDB($turnster);
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
