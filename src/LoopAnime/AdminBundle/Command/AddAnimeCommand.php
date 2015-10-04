<?php

namespace LoopAnime\AdminBundle\Command;

use LoopAnime\AppBundle\Parser\Implementation\TheTVDB;
use LoopAnime\AppBundle\Parser\ParserAnime;
use LoopAnime\ShowsAPIBundle\Entity\AnimesAPI;
use LoopAnime\AppBundle\BusCommand\Anime\CreateAnime;
use LoopAnime\AppBundle\BusCommand\Anime\EditAnime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddAnimeCommand extends ContainerAwareCommand {

    /** @var OutputInterface */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('loopanime:admin:import:add-anime')
            ->setDescription('Adds one anime to the database')
            ->addArgument('tvdbId', InputArgument::REQUIRED, 'TVDB ID', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tvdbId = $input->getArgument('tvdbId');
        $this->output = $output;

        $doctrine = $this->getContainer()->get('doctrine');
        /** @var TheTVDB $theTVDb */
        $theTVDb = $this->getContainer()->get('loopanime.parser.tvdb');

        $this->output->writeln('Parsing the Content...');
        /** @var ParserAnime $parserAnime */
        $parserAnime = $theTVDb->parseAnime($tvdbId);
        $this->output->writeln('Content has been parsed. Anime: ' . $parserAnime->getTitle() . ' Seasons: ' . count($parserAnime->getSeasons()));

        /** @var AnimesAPI $animeApi */
        $animeApi = $doctrine->getRepository('LoopAnime\ShowsAPIBundle\Entity\AnimesAPI')->findOneBy(['apiAnimeKey' => $tvdbId]);
        if (!$animeApi) {
            $output->writeln("<comment>Anime doesn't exists, creating new anime!</comment>");
            $command = new CreateAnime($parserAnime, $output);
        } else {
            $output->writeln("<comment>Anime already exists. Updating anime key " . $animeApi->getIdAnime() . "!</comment>");
            $anime = $doctrine->getRepository('LoopAnimeShowsBundle:Animes')->find($animeApi->getIdAnime());
            $command = new EditAnime($anime, $parserAnime, $output);
        }

        $this->getContainer()->get('command_bus')->handle($command);
        $output->writeln("<info>Command has ran successfully!</info>");
    }

}
