<?php

declare(strict_types=1);

namespace OrleansMetropole\SendethicTypo3\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use OrleansMetropole\SendethicTypo3\Service\NewsletterUnsubscribeLinkService;

class GenerateUnsubscribeLinksCommand extends Command
{
    private SiteFinder $siteFinder;
    private NewsletterUnsubscribeLinkService $linkService;

    public function __construct(SiteFinder $siteFinder, NewsletterUnsubscribeLinkService $linkService)
    {
        $this->siteFinder = $siteFinder;
        $this->linkService = $linkService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Génère des liens de désinscription newsletter pour tous les sites')
            ->setHelp('Cette commande génère des liens de désinscription pour tous les segments de newsletter configurés.');

        $this->addArgument('email', InputArgument::REQUIRED, 'L\'email à utiliser pour générer les liens');
        $this->addArgument('siteIdentifier', InputArgument::OPTIONAL, 'L\'identifiant du site à utiliser');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $siteIdentifier = $input->getArgument('siteIdentifier');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('L\'email fourni n\'est pas valide.');
            return Command::FAILURE;
        }

        try {
            if ($siteIdentifier) {
                $sites = [$this->siteFinder->getSiteByIdentifier($siteIdentifier)];
            } else {
                $sites = $this->siteFinder->getAllSites();
            }

            $io->title('Génération des liens de désinscription pour : ' . $email);

            foreach ($sites as $site) {
                $io->section('Site : ' . $site->getIdentifier());
                
                try {
                    $links = $this->linkService->generateAllLinks($site, $email);
                    
                    if (isset($links['segments'])) {
                        $io->text('Liens de désinscription par segment :');
                        foreach ($links['segments'] as $segmentId => $segmentData) {
                            $io->text(sprintf('  - %s (ID: %d): %s', $segmentData['name'], $segmentId, $segmentData['url']));
                        }
                    }
                    
                    if (isset($links['all'])) {
                        $io->text('Lien de désinscription complète (RGPD) :');
                        $io->text('  - ' . $links['all']['url']);
                    }
                    
                } catch (\Exception $e) {
                    $io->warning('Erreur pour le site ' . $site->getIdentifier() . ': ' . $e->getMessage());
                }
                
                $io->newLine();
            }

            $io->success('Génération des liens terminée.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors de la génération des liens : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 