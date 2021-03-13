<?php

namespace App\Controller;

use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    /**
     * @Route("/webhook", name="webhook")
     */
    public function index(Request $request): Response
    {
        return $this->json([
            'status' => 200,
            'title' => 'Success',
            'message' => 'Webhooks are available.'
        ]);
    }

    /**
     * @Route("/webhook/email-service", name="webhook_email_service", methods={"POST"})
     */
    public function emailServiceWebhook(Request $request, EmailService $emailService): Response
    {
        // Fail if not configured
        if (!$emailService->isConfigured()) {
            return $this->json([
                'status' => 500,
                'title' => 'Internal server error',
                'details' => 'Email service not configured.'
            ], 500);
        }

        // Fail if token is missing or mismatched
        if (!$request->get('token') ||
            $request->get('token') != $emailService->getWebhookToken()
        ) {
            return $this->json([
                'status' => 403,
                'title' => 'Access denied',
                'details' => 'Invalid credentials.'
            ], 403);
        }

        // Process payload
        try {
            $output = $emailService->processWebhookBody($request->getContent());
            return $this->json([
                'status' => 200,
                'title' => 'success',
                'details' => 'Processed webhook.',
                'extra' => $output
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'title' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
