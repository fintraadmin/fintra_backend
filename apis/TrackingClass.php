<?php


/**
 * Interface for tracking user journey events
 */
interface UserJourneyTrackerInterface
{
    /**
     * Get redirect URL with click ID for journey initialization
     */
    public function getRedirectUrl($initialUrl, $params);

    /**
     * Track a step in the user journey using postback URL
     */
    public function trackStep(
        $clickId,
        $journeyState,
        $params
    );

    /**
     * Extract click ID from the redirected URL
     */
    public function extractClickId($redirectedUrl);

    /**
     * Log the tracking event to server logs
     */
    public function logEvent(
        $clickId,
        $journeyState,
        $params
    );
}

/**
 * Data Transfer Object for tracking parameters
 */
class TrackingParams
{
    private  $subSource;
    private  $productId;
    private  $productClass;
    private  $leadId;
    private  $leadName;
    private  $leadMobile;

    public function __construct(
        $subSource,
        $productId,
        $productClass,
        $leadId = null,
        $leadName = null,
        $leadMobile = null
    ) {
        $this->subSource = $subSource;
        $this->productId = $productId;
        $this->productClass = $productClass;
        $this->leadId = $leadId;
        $this->leadName = $leadName;
        $this->leadMobile = $leadMobile;
    }

    public function toArray(): array
    {
        return [
            'source' => $this->subSource,
            'p1' => $this->productClass,
            'p2' => $this->productId,	
            'p3' => $this->leadId,
            'sub2' => $this->leadName,
	    'sub3' => $this->leadMobile
        ];
    }
}

/**
 * Base implementation with common functionality
 */
abstract class BaseUserJourneyTracker implements UserJourneyTrackerInterface
{
     protected $redirectUrl;
     protected $postbackUrl;
     protected $securityToken;
     protected $logFile;

    public function __construct(
         $redirectUrl,
         $postbackUrl, 
         $securityToken,
         $logFile = '/var/log/tracking.log'
    ) {
        $this->redirectUrl = $redirectUrl;
        $this->postbackUrl = $postbackUrl;
        $this->securityToken = $securityToken;
        $this->logFile = $logFile;
    }

    public function logEvent(
        $clickId,
        $journeyState,
        $params
    ){
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = sprintf(
            "[%s] UserJourney - ClickID: %s, State: %s, Params: %s\n",
            $timestamp,
            $clickId,
            $journeyState,
            json_encode($params)
        );

        error_log($logMessage, 3, $this->logFile);
    }

    /**
     * Extract click ID from URL parameters
     */
    public function extractClickId($redirectedUrl): string
    {
	echo "$redirectedUrl \n";
	$parsedUrl = parse_url($redirectedUrl);
        if (!isset($parsedUrl['query'])) {
            throw new \RuntimeException('No query parameters found in redirected URL');
        }


        parse_str($parsedUrl['query'], $params);
        if (!isset($params['clickid'])) {
            throw new \RuntimeException('Click ID not found in redirected URL');
        }

        return $params['clickid'];
    }

    /**
     * Build URL with query parameters
     */
    protected function buildUrl($baseUrl, array $params): string
    {
        // Add security token and timestamp to params
        $params['token'] = $this->securityToken;
        $params['ts'] = time();
        
        // URL encode all parameter values
        $encodedParams = array_map('urlencode', $params);
        
        // Build query string
        $queryString = http_build_query($encodedParams);
        
        return $baseUrl . (strpos($baseUrl, '?') === false ? '?' : '&') . $queryString;
    }

    /**
     * Make HTTP GET request
     */
    protected function makeHttpRequest($url): array
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => 30,
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            $error = error_get_last();
            throw new \RuntimeException('HTTP request failed: ' . ($error['message'] ?? 'Unknown error'));
        }

        // Get response headers
        $responseHeaders = $http_response_header ?? [];
        $statusCode = 0;

        // Extract status code
        foreach ($responseHeaders as $header) {
            if (strpos($header, 'HTTP/') === 0) {
                $parts = explode(' ', $header);
                $statusCode = (int) ($parts[1] ?? 0);
                break;
            }
        }

	 // Get final URL after all redirects
        if (isset($http_response_header)) {
            $finalUrl = $redirectUrl;
            foreach ($http_response_header as $header) {
                if (strpos($header, 'HTTP/') === 0 && strpos($header, ' 200 ') !== false) {
                    // If we got a 200 status, the previous Location header was the final destination
                    break;
                }
                if (stripos($header, 'Location:') === 0) {
                    $finalUrl = trim(substr($header, 9));
                }
            }
        }

        return [
            'body' => $result,
            'status_code' => $statusCode,
	    'final_url' => $finalUrl ?? $url	
        ];
    }

    /**
     * Validate API response
     */
    protected function validateResponse($response): bool
    {
        return $response['status_code'] >= 200 && $response['status_code'] < 500;
    }
}

/**
 * Sample implementation for a specific tracking service
 */
class TrackierTrackingService extends BaseUserJourneyTracker
{
    public function getRedirectUrl($initialUrl, $params): string
    {
        try {
            $url = $this->buildUrl($this->redirectUrl, array_merge(
                $params,
                ['initial_url' => $initialUrl]
            ));
	    echo "$url \n";
            $response = $this->makeHttpRequest($url);

            if (!$this->validateResponse($response)) {
                throw new \RuntimeException('Failed to get redirect URL: Invalid response');
            }
            echo json_encode($response). " \n"; 

            if (!isset($response['final_url'])) {
                throw new \RuntimeException('Redirect URL not found in response');
            }

            // Log the initial redirect
            $this->logEvent(
                'pending',
                'redirect_generated',
                array_merge($params, ['redirect_url' => $response['final_url']])
            );
            
            return $response['final_url'];
        } catch (\Exception $e) {
            $this->logEvent(
                'error',
                'redirect_error',
                ['error' => $e->getMessage(), 'params' => $params]
            );
            throw $e;
        }
    }

    public function trackStep(
        $clickId,
        $journeyState,
        $params
    ): bool {
        try {
            $url = $this->buildUrl($this->postbackUrl, array_merge(
                $params,
                [
                    'click_id' => $clickId,
                    'sub1' => $journeyState
                ]
            ));

            $response = $this->makeHttpRequest($url);

            if (!$this->validateResponse($response)) {
                throw new \RuntimeException('Failed to track step: Invalid response');
            }

            $this->logEvent($clickId, $journeyState, $params);
            
            return true;
        } catch (\Exception $e) {
            $this->logEvent($clickId, $journeyState . '_error', [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            return false;
        }
    }
}
/*
// Usage example:
$tracker = new ExampleTrackingService(
    'https://fintra.gotrackier.io/click?campaign_id=2&pub_id=2',  // Redirect URL
    'https://fintra.trackier.co/acquisition?security_token=630542d888e41e52814f',  // Postback URL
    '630542d888e41e52814f'
);

$params = new TrackingParams(
    'facebook',
    'PROD123',
    'insurance',
    'LEAD456',
    'John Doe',
    '1234567890'
);

// Get redirect URL
$redirectUrl = $tracker->getRedirectUrl('https://example.com', $params->toArray());
// Example redirect URL:
// https://redirect.tracking-service.com?sub_source=facebook&product_id=PROD123&...&token=your-security-token-here&ts=1234567890

// Later, after redirect, extract click ID from the redirected URL
echo "1. $redirectUrl \n";
$clickId = $tracker->extractClickId($redirectUrl);
 echo "clickid $clickId \n";
// Track journey steps (will use postback URL)
$tracker->trackStep($clickId, 'initiated', $params->toArray());
// Example postback URL:

// https://postback.tracking-service.com?click_id=abc123&journey_state=form_started&...&token=your-security-token-here&ts=1234567890

*/
