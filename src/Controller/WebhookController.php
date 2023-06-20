<?php

namespace App\Controller;

use App\Service\EmailService;
use App\Service\PhoneService;
use App\Service\SmsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    #[Route(path: '/webhook', name: 'webhook')]
    public function index(Request $request): Response
    {
        return $this->json(['status' => 200, 'title' => 'success', 'details' => 'Webhooks are available.']);
    }

    #[Route(path: '/webhook/phone-service', name: 'webhook_phone_service', methods: ['POST'])]
    public function phoneServiceWebhook(Request $request, PhoneService $phoneService): Response
    {
        if (!$phoneService->isConfigured()) {
            return $this->json(['status' => 500, 'title' => 'error', 'details' => 'Phone service not configured.'], 500);
        }
        if (!$request->get('token')
            || $request->get('token') != $phoneService->getWebhookToken()
        ) {
            return $this->json(['status' => 403, 'title' => 'error', 'details' => 'Invalid credentials.'], 403);
        }
        try {
            $response = new Response();
            $response->headers->set('Content-type', 'text/xml');
            $response->setContent($phoneService->handleWebhook($request));

            return $response;
        } catch (\Exception $e) {
            return $this->json(['status' => 500, 'title' => 'error', 'details' => $e->getMessage()], 500);
        }
    }

    #[Route(path: '/webhook/sms-service', name: 'webhook_sms_service', methods: ['POST'])]
    public function smsServiceWebhook(Request $request, SmsService $smsService): Response
    {
        if (!$smsService->isConfigured()) {
            return $this->json(['status' => 500, 'title' => 'error', 'details' => 'SMS service not configured.'], 500);
        }
        if (!$request->get('token')
            || $request->get('token') != $smsService->getWebhookToken()
        ) {
            return $this->json(['status' => 403, 'title' => 'error', 'details' => 'Invalid credentials.'], 403);
        }
        try {
            $response = new Response();
            $response->headers->set('Content-type', 'text/xml');
            $response->setContent($smsService->handleWebhook($request));

            return $response;
        } catch (\Exception $e) {
            return $this->json(['status' => 500, 'title' => 'error', 'details' => $e->getMessage()], 500);
        }
    }

    #[Route(path: '/webhook/email-service', name: 'webhook_email_service', methods: ['POST'])]
    public function emailServiceWebhook(Request $request, EmailService $emailService): Response
    {
        if (!$emailService->isConfigured()) {
            return $this->json(['status' => 500, 'title' => 'error', 'details' => 'Email service not configured.'], 500);
        }
        if (!$request->get('token')
            || $request->get('token') != $emailService->getWebhookToken()
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
