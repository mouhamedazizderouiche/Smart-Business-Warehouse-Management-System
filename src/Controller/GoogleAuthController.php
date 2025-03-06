<?php

namespace App\Controller;

use Google\Client;
use Google\Service\Calendar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleAuthController extends AbstractController
{
    private string $credentialsPath;
    private string $tokenPath;
    private string $redirectUri;

    public function __construct(
        // string $projectDir,
        string $credentialsPath,
        string $tokenPath,
        string $redirectUri
    ) {
        // Set paths for credentials and token files
        $this->credentialsPath = $credentialsPath;
        $this->tokenPath = $tokenPath;
        $this->redirectUri = $redirectUri;
    }

    #[Route('/oauth/google', name: 'google_auth')]
    public function authenticate(): Response
    {
        // Create the Google Client
        $client = $this->getGoogleClient();

        // Generate the authorization URL
        $authUrl = $client->createAuthUrl();

        // Redirect the user to Google's authorization page
        return $this->redirect($authUrl);
    }

    #[Route('/oauth/google/callback', name: 'google_auth_callback')]
    public function callback(Request $request): Response
    {
        // Get the authorization code from the query parameters
        $code = $request->query->get('code');

        if (!$code) {
            return new Response('Authorization code not found.', 400);
        }

        // Create the Google Client
        $client = $this->getGoogleClient();

        try {
            // Exchange the authorization code for an access token
            $token = $client->fetchAccessTokenWithAuthCode($code);

            // Check for errors in the token response
            if (isset($token['error'])) {
                return new Response('Error during authentication: ' . $token['error'], 400);
            }

            // Set the access token in the client
            $client->setAccessToken($token);

            // Save the token to a file (for future use)
            file_put_contents($this->tokenPath, json_encode($token));

            // Redirect to the calendar events page
            return $this->redirectToRoute('calendar_events');
        } catch (\Exception $e) {
            return new Response('Error during authentication: ' . $e->getMessage(), 500);
        }
    }

    #[Route('/calendar/events', name: 'calendar_events')]
    public function showEvents(): Response
    {
        try {
            // Create the Google Client
            $client = $this->getGoogleClient();

            // Check if the token is expired and refresh it if necessary
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    // Refresh the access token using the refresh token
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    file_put_contents($this->tokenPath, json_encode($client->getAccessToken()));
                } else {
                    // No refresh token available, re-authenticate
                    return $this->redirectToRoute('google_auth');
                }
            }

            // Create the Calendar service
            $calendarService = new Calendar($client);

            // Fetch events from the primary calendar
            $calendarId = 'primary';
            $events = $calendarService->events->listEvents($calendarId);

            // Render the events in a Twig template
            return $this->render('calendar/index.html.twig', [
                'events' => $events->getItems(),
            ]);
        } catch (\Exception $e) {
            return new Response('Error fetching events: ' . $e->getMessage(), 500);
        }
    }

    private function getGoogleClient(): Client
    {
        // Create and configure the Google Client
        $client = new Client();
        $client->setAuthConfig($this->credentialsPath); // Use the credentials.json file
        $client->addScope(Calendar::CALENDAR_READONLY);
        $client->setRedirectUri($this->redirectUri); // Use the redirect URI from .env
        $client->setAccessType('offline'); // Required to get a refresh token
        $client->setPrompt('select_account consent'); // Force consent prompt

        // Load the token if it exists
        if (file_exists($this->tokenPath)) {
            $token = json_decode(file_get_contents($this->tokenPath), true);
            $client->setAccessToken($token);
        }

        return $client;
    }
}