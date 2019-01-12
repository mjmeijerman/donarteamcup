<?php

namespace AppBundle\Command;

use AppBundle\Entity\TeamRepository;
use AppBundle\Entity\User;
use AppBundle\Entity\UserRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FillEmptyTeamNamesCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:fill-empty-team-names')
            ->setDescription('Lege teamnamen vullen met naam vereniging + nummer');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var UserRepository $repository */
        $repository = $this->getContainer()->get("doctrine")->getRepository("AppBundle:User");

        /** @var TeamRepository $teamRepository */
        $teamRepository = $this->getContainer()->get("doctrine")->getRepository("AppBundle:Team");

        /** @var User[] $users */
        $users = $repository->findAll();
        foreach ($users as $user) {
            $number = 1;
            foreach ($user->getTeams() as $team) {
                if (!$team->getName()) {
                    $teamName = $user->getVereniging()->getNaam() . ' ' . $user->getVereniging()->getPlaats() . ' ' . $number;
                    while ($teamRepository->findByTeamName($teamName) !== null) {
                        $number++;
                    }

                    $team->setName($teamName);
                    $this->addToDB($team);
                    $number++;
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
