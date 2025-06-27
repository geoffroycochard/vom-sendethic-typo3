<?php
declare(strict_types=1);

namespace OrleansMetropole\SendethicTypo3\Middleware;

use OrleansMetropole\SendethicTypo3\Service\NewsletterUnsubscribeLinkService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\SiteFinder;

class SendethicNewsletterMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly NewsletterUnsubscribeLinkService $linkService,
        private readonly SiteFinder $siteFinder,
    )
    {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        
        if (!preg_match('/\/sendethic/', $path, $matches)) {
            return $handler->handle($request);
        }
        
        if($request->getHeaderLine('referer') !== 'https://tracking.orleans-metropole.fr/') {
            throw new \Exception('Sendethic newsletter middleware: Invalid referer', 1);
        }

        $site = $request->getAttribute('site');
        $params = $request->getQueryParams();
        $email = $params['contact'] ?? '';

        if (!$email) {
            return $handler->handle($request);
        }

        if (isset($params['type']) && (int) $params['type'] > 0) {
            $segmentId = (int) $params['type'];
            $link = $this->linkService->generateSegmentUnsubscribeLink($site, $email, $segmentId);
            return new RedirectResponse($link);
        }

        return $handler->handle($request);
    }
} 