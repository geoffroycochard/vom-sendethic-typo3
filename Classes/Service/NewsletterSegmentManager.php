<?php
declare(strict_types=1);

namespace OrleansMetropole\SendethicTypo3\Service;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NewsletterSegmentManager
{
    /**
     * Récupère le nom d'un segment pour le site actuel
     */
    public function getSegmentNameForCurrentSite(int $segmentId): string
    {
        $site = $this->getCurrentSite();
        if (!$site) {
            return 'Newsletter inconnue';
        }

        $segments = $site->getAttribute('newsletterSegments') ?? [];
        return $segments[(string)$segmentId] ?? 'Newsletter inconnue';
    }

    /**
     * Récupère les segments actifs pour un contact sur le site actuel
     */
    public function getActiveSegmentsForContactCurrentSite(array $contactData): array
    {
        $site = $this->getCurrentSite();
        if (!$site) {
            return [];
        }

        $segments = $site->getAttribute('newsletterSegments') ?? [];
        $activeSegments = [];

        foreach ($segments as $segmentId => $segmentName) {
            if (isset($contactData[$segmentId]) && $contactData[$segmentId] === '1') {
                $activeSegments[$segmentId] = $segmentName;
            }
        }

        return $activeSegments;
    }

    /**
     * Récupère tous les segments configurés pour le site actuel
     */
    public function getAllSegmentsForCurrentSite(): array
    {
        $site = $this->getCurrentSite();
        if (!$site) {
            return [];
        }

        return $site->getAttribute('newsletterSegments') ?? [];
    }

    /**
     * Vérifie si un segment est valide pour le site actuel
     */
    public function isValidSegmentForCurrentSite(int $segmentId): bool
    {
        $site = $this->getCurrentSite();
        if (!$site) {
            return false;
        }

        $segments = $site->getAttribute('newsletterSegments') ?? [];
        return isset($segments[(string)$segmentId]);
    }

    /**
     * Récupère le site actuel
     */
    private function getCurrentSite(): ?Site
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request) {
            return null;
        }

        return $request->getAttribute('site');
    }
} 