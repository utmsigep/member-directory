<?php

namespace App\Controller;

use App\Service\SmsService;
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
        return $this->json(['status' => 200, 'title' => 'success', 'details' => 'Webhooks are available.']);
    }

    /**
     * @Route("/webhook/sms-service", name="webhook_sms_service", methods={"POST"})
     */
    public function smsServiceWebhook(Request $request, SmsService $smsService): Response
    {
        if (!$smsService->isConfigured()) {
            return $this->json(['status' => 500, 'title' => 'error', 'details' => 'SMS service not configured.'], 500);
        }
        if (!$request->get('token') ||
            $request->get('token') != $smsService->getWebhookToken()
        ) {
            return $this->json(['status' => 403, 'title' => 'error', 'details' => 'Invalid credentials.'], 403);
        }
        try {
            $output = $smsService->handleWebhook($request);
        } catch (\Exception $e) {
            return $this->json(['status' => 500, 'title' => 'error', 'details' => $e->getMessage()], 500);
        }
        return $this->json(['status' => 200, 'title' => 'success', 'details' => 'Processed webhook.', 'extra' => $output]);
    }

    /**
     * @Route("/webhook/email-service", name="webhook_email_service", methods={"POST"})
     */
    public function emailServiceWebhook(Request $request, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            return $this->json(['status' => 500, 'title' => 'error', 'details' => 'Email service not configured.'], 500);
        }
        if (!$request->get('token') ||
            $request->get('token') != $emailService->getWebhookToken()
        ) {
            return $this->json(['status' => 403, 'title' => 'error', 'details' => 'Invalid credentials.'], 403);
        }
        try {
            $output = $emailService->processWebhookBody($request->getContent());
            return $this->json(['status' => 200, 'title' => 'success', 'details' => 'Processed webhook.', 'extra' => $output]);
        } catch (\Exception $e) {
            return $this->json(['status' => 500, 'title' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


}
