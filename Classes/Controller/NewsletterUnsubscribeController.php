<?php
declare(strict_types=1);

namespace OrleansMetropole\SendethicTypo3\Controller;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use OrleansMetropole\SendethicApi\Api\ContactApi;
use OrleansMetropole\SendethicApi\Configuration;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use OrleansMetropole\SendethicTypo3\Service\NewsletterUnsubscribeLinkService;
use OrleansMetropole\SendethicTypo3\Service\NewsletterSegmentManager;

class NewsletterUnsubscribeController extends ActionController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private NewsletterUnsubscribeLinkService $linkService;
    private NewsletterSegmentManager $segmentManager;

    public function __construct(
        NewsletterUnsubscribeLinkService $linkService,
        NewsletterSegmentManager $segmentManager
    ) {
        $this->linkService = $linkService;
        $this->segmentManager = $segmentManager;
    }

    /**
     * Action pour désinscrire d'un segment spécifique
     */
    public function unsubscribeSegmentAction(string $token): ResponseInterface
    {
        if (!$this->linkService->validateToken($token)) {
            $this->addFlashMessage('Token invalide ou expiré', 'Erreur', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('error');
        }

        $tokenData = $this->linkService->decodeToken($token);
        $email = $tokenData['email'] ?? '';
        $segmentId = $tokenData['segmentId'] ?? '';

        if (!$email || !$segmentId) {
            $this->addFlashMessage('Données de désinscription invalides', 'Erreur', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('error');
        }

        try {
            $contactData = $this->getContactData($email);
            $segmentName = $this->segmentManager->getSegmentNameForCurrentSite((int)$segmentId);
            
            $this->view->assignMultiple([
                'email' => $email,
                'segmentId' => $segmentId,
                'segmentName' => $segmentName,
                'token' => $token
            ]);
            
            return $this->htmlResponse();
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des données de contact: ' . $e->getMessage());
            $this->addFlashMessage('Impossible de récupérer vos informations', 'Erreur', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('error');
        }
    }

    /**
     * Action pour confirmer la désinscription d'un segment
     */
    public function confirmSegmentUnsubscribeAction(): ResponseInterface
    {
        $token = $this->request->getArgument('token') ?? '';
        $confirmed = $this->request->getArgument('confirmed') ?? false;

        if (!$this->linkService->validateToken($token)) {
            $this->addFlashMessage('Token invalide ou expiré', 'Erreur', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('error');
        }

        if (!$confirmed) {
            return $this->redirect('unsubscribeSegment', null, null, ['token' => $token]);
        }

        $tokenData = $this->linkService->decodeToken($token);
        $email = $tokenData['email'] ?? '';
        $segmentId = $tokenData['segmentId'] ?? '';

        try {
            $this->unsubscribeFromSegment($email, $segmentId);
            $this->addFlashMessage('Vous avez été désinscrit avec succès de cette newsletter', 'Confirmation', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK);
            return $this->redirect('success');
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la désinscription: ' . $e->getMessage());
            $this->addFlashMessage('Erreur lors de la désinscription', 'Erreur', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('error');
        }
    }

    /**
     * Action pour désinscrire de toutes les newsletters (RGPD)
     */
    public function unsubscribeAllAction(): ResponseInterface
    {
        $token = $this->request->getArgument('token') ?? '';
        
        if (!$this->linkService->validateToken($token)) {
            $this->addFlashMessage('Token invalide ou expiré', 'Erreur', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('error');
        }

        $tokenData = $this->linkService->decodeToken($token);
        $email = $tokenData['email'] ?? '';

        if (!$email) {
            $this->addFlashMessage('Données de désinscription invalides', 'Erreur', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('error');
        }

        try {
            $contactData = $this->getContactData($email);
            $segments = $this->segmentManager->getActiveSegmentsForContactCurrentSite($contactData);

            $this->view->assignMultiple([
                'email' => $email,
                'segments' => $segments,
                'token' => $token
            ]);
            
            return $this->htmlResponse();
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des données de contact: ' . $e->getMessage());
            $this->addFlashMessage('Impossible de récupérer vos informations', 'Erreur', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('error');
        }
    }

    /**
     * Action pour confirmer la suppression complète (RGPD)
     */
    public function confirmDeleteAllAction(): ResponseInterface
    {
        $token = $this->request->getArgument('token') ?? '';
        $confirmed = $this->request->getArgument('confirmed') ?? false;

        if (!$this->linkService->validateToken($token)) {
            $this->addFlashMessage('Token invalide ou expiré', 'Erreur', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('error');
        }

        if (!$confirmed) {
            return $this->redirect('unsubscribeAll', null, null, ['token' => $token]);
        }

        $tokenData = $this->linkService->decodeToken($token);
        $email = $tokenData['email'] ?? '';

        try {
            $this->deleteContact($email);
            $this->addFlashMessage('Vos données ont été supprimées avec succès', 'Confirmation', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK);
            return $this->redirect('success');
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression: ' . $e->getMessage());
            $this->addFlashMessage('Erreur lors de la suppression', 'Erreur', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return $this->redirect('error');
        }
    }

    /**
     * Action de succès
     */
    public function successAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * Action d'erreur
     */
    public function errorAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * Récupère les données du contact via l'API Sendethic
     */
    private function getContactData(string $email): array
    {
        /** @var Site $site */
        $site = $this->request->getAttribute('site');
        $config = Configuration::getDefaultConfiguration()
            ->setUsername($site->getAttribute('sendethicApiUsername'))
            ->setPassword($site->getAttribute('sendethicApiPassword'));

        $contactApi = new ContactApi(new Client([]), $config);
        $return = $contactApi->contactGetContactAttributeKey($email);
        
        $contactData = current($return);
        
        if (!$contactData) {
            throw new \Exception('Contact non trouvé');
        }

        return $this->transformAttributes($contactData->getAttributes());
    }

    /**
     * Transforme les attributs du contact
     */
    private function transformAttributes($contactData): array
    {
        $attributes = [];
        foreach ($contactData as $contactAttribute) {
            $attributes[$contactAttribute->getId()] = $contactAttribute->getFieldValue();
        }
        return $attributes;
    }

    /**
     * Désinscrit d'un segment spécifique
     */
    private function unsubscribeFromSegment(string $email, int $segmentId): void
    {
        /** @var Site $site */
        $site = $this->request->getAttribute('site');
        $config = Configuration::getDefaultConfiguration()
            ->setUsername($site->getAttribute('sendethicApiUsername'))
            ->setPassword($site->getAttribute('sendethicApiPassword'));

        $contactApi = new ContactApi(new Client([]), $config);

        $attributes = [
            [
                'id' => $segmentId,
                'fieldValue' => '0' // Valeur pour désinscription
            ]   
        ];

        $contact = [
            'id' => 0,
            'contactKey' => $email,
            'attributes' => $attributes,
        ];

        $contactApi->contactPostContactAttributeKey($contact);
    }

    /**
     * Supprime complètement le contact (RGPD)
     */
    private function deleteContact(string $email): void
    {
        /** @var Site $site */
        $site = $this->request->getAttribute('site');
        $config = Configuration::getDefaultConfiguration()
            ->setUsername($site->getAttribute('sendethicApiUsername'))
            ->setPassword($site->getAttribute('sendethicApiPassword'));

        $contactApi = new ContactApi(new Client([]), $config);
        
        // Récupérer d'abord l'ID du contact
        $return = $contactApi->contactGetContactAttributeKey($email);
        $contactData = current($return);
        
        if ($contactData) {
            $contactApi->contactDeleteContactById($contactData->getId());
        }
    }
} 