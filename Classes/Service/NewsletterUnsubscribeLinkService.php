<?php
declare(strict_types=1);

namespace OrleansMetropole\SendethicTypo3\Service;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;

class NewsletterUnsubscribeLinkService
{
    public const TYPE_SEGMENT = 'segment';
    public const TYPE_ALL = 'all';

    private const TOKEN_SECRET = 'sendethic_newsletter_unsubscribe_secret_2024';
    private const TOKEN_EXPIRY_DAYS = 7;

    public function generateToken(string $email, ?int $segmentId = null): string
    {
        $data = [
            'email' => $email,
            'segmentId' => $segmentId,
            'timestamp' => time(),
            'expires' => time() + (self::TOKEN_EXPIRY_DAYS * 24 * 60 * 60)
        ];

        $jsonData = json_encode($data);
        $signature = hash_hmac('sha256', $jsonData, self::TOKEN_SECRET);
        
        return base64_encode($jsonData . '.' . $signature);
    }

    public function validateToken(string $token): bool
    {
        try {
            $decoded = base64_decode($token);
            if ($decoded === false) {
                return false;
            }
            
            $parts = preg_split('/\.(?=[a-f0-9]+$)/i', $decoded);
            
            if (count($parts) !== 2) {
                return false;
            }
            
            [$jsonData, $signature] = $parts;
            
            if (!preg_match('/^[a-f0-9]+$/i', $signature)) {
                return false;
            }
            
            $expectedSignature = hash_hmac('sha256', $jsonData, self::TOKEN_SECRET);
            if (!hash_equals($expectedSignature, $signature)) {
                return false;
            }
            
            $data = json_decode($jsonData, true);
            if (!$data || !isset($data['expires'])) {
                return false;
            }

            return $data['expires'] > time();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function decodeToken(string $token): array
    {
        $decoded = base64_decode($token);
        $parts = preg_split('/\.(?=[a-f0-9]+$)/i', $decoded);
        $jsonData = $parts[0] ?? '';
        
        return json_decode($jsonData, true) ?? [];
    }

    public function generateSegmentUnsubscribeLink(Site $site, string $email, int $segmentId): string
    {
        $token = $this->generateToken($email, $segmentId);
        return $this->buildUrl($site, self::TYPE_SEGMENT, $token);
    }

    public function generateAllUnsubscribeLink(Site $site, string $email): string
    {
        $token = $this->generateToken($email);
        return $this->buildUrl($site, self::TYPE_ALL, $token);
    }

    public function generateAllLinks(Site $site, string $email): array
    {
        $links = [];
        $segments = $this->getSegments($site);

        foreach ($segments as $segmentId => $segmentName) {
            $links['segments'][(int)$segmentId] = [
                'name' => $segmentName,
                'url' => $this->generateSegmentUnsubscribeLink($site, $email, (int)$segmentId)
            ];
        }

        $links['all'] = [
            'name' => 'Désinscription complète (RGPD)',
            'url' => $this->generateAllUnsubscribeLink($site, $email)
        ];

        return $links;
    }

    public function generateLink(Site $site, string $type, string $email, ?int $segmentId = null): string
    {
        switch ($type) {
            case self::TYPE_SEGMENT:
                if (!$segmentId) {
                    throw new \InvalidArgumentException('Segment ID requis pour le type "segment"');
                }
                return $this->generateSegmentUnsubscribeLink($site, $email, $segmentId);

            case self::TYPE_ALL:
                return $this->generateAllUnsubscribeLink($site, $email);

            default:
                throw new \InvalidArgumentException(sprintf('Type de lien invalide: %s', $type));
        }
    }

    /**
     * Construit l'URL en utilisant uniquement les routes configurées
     */
    private function buildUrl(Site $site, string $type, string $token): string
    {
        $pid = $site->getConfiguration()['newsletterUnsubscribePid'] ?? 0;
        $base = $site->getRouter()->generateUri($pid);
        $routePath = $site->getConfiguration()['routeEnhancers']['NewsletterUnsubscribe']['routePath'] ?? '';

        if ($pid === 0 || $base === null || $routePath === null) {
            throw new \Exception('NewsletterUnsubscribeLinkService: PID or base is null');
        }
        
        $routePath = str_replace('{action}', $type === self::TYPE_SEGMENT ? 'unsubscribeSegment' : 'unsubscribeAll', $routePath);
        $routePath = str_replace('{token}', $token, $routePath);

        $uri = $base . $routePath;
        return (string) $uri;
    }

    private function getSegments(Site $site): array
    {
        if ($site) {
            return $site->getAttribute('newsletterSegments') ?? [];
        }
        return [];
    }

    public function isValidSegment(int $segmentId, Site $site): bool
    {
        $segments = $this->getSegments($site);
        return isset($segments[(string)$segmentId]);
    }

    public function getSegmentName(int $segmentId, Site $site): string
    {
        $segments = $this->getSegments($site);
        return $segments[(string)$segmentId] ?? 'Newsletter inconnue';
    }

    public function getAllSegments(Site $site): array
    {
        return $this->getSegments($site);
    }
} 