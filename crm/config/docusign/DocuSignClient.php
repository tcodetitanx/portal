<?php
class DocuSignClient {
    private $integrationKey;
    private $userId;
    private $basePath;
    private $accountId;
    private $privateKeyPath;
    private $accessToken;
    
    public function __construct($integrationKey, $userId, $basePath, $privateKeyPath) {
        $this->integrationKey = $integrationKey;
        $this->userId = $userId;
        $this->basePath = $basePath;
        $this->privateKeyPath = $privateKeyPath;
        $this->accessToken = null;
        $this->accountId = null;
    }
    
    /**
     * Authenticate with DocuSign using JWT
     */
    public function authenticate() {
        // Read private key
        $privateKey = file_get_contents($this->privateKeyPath);
        
        // Create JWT token
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'RS256'
        ]);
        
        $now = time();
        $exp = $now + 3600; // 1 hour expiration
        
        $payload = json_encode([
            'iss' => $this->integrationKey,
            'sub' => $this->userId,
            'iat' => $now,
            'exp' => $exp,
            'aud' => 'account-d.docusign.com',
            'scope' => 'signature impersonation'
        ]);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = '';
        openssl_sign($base64UrlHeader . '.' . $base64UrlPayload, $signature, $privateKey, 'SHA256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
        
        // Get access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://account-d.docusign.com/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode != 200) {
            throw new Exception('Failed to authenticate with DocuSign: ' . $response);
        }
        
        $responseData = json_decode($response, true);
        $this->accessToken = $responseData['access_token'];
        
        // Get user info to get account ID
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://account-d.docusign.com/oauth/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode != 200) {
            throw new Exception('Failed to get user info from DocuSign: ' . $response);
        }
        
        $responseData = json_decode($response, true);
        $this->accountId = $responseData['accounts'][0]['account_id'];
        
        return $this->accessToken;
    }
    
    /**
     * Send an envelope with a document for signature
     */
    public function sendEnvelope($documentPath, $documentName, $clientName, $clientEmail, $williamName, $williamEmail) {
        if (!$this->accessToken) {
            $this->authenticate();
        }
        
        // Read document file
        $documentContent = file_get_contents($documentPath);
        $documentBase64 = base64_encode($documentContent);
        
        // Create envelope definition
        $envelopeDefinition = [
            'emailSubject' => 'Please sign your Bull Axiom contract',
            'documents' => [
                [
                    'documentBase64' => $documentBase64,
                    'name' => $documentName,
                    'fileExtension' => 'pdf',
                    'documentId' => '1'
                ]
            ],
            'recipients' => [
                'signers' => [
                    [
                        'email' => $clientEmail,
                        'name' => $clientName,
                        'recipientId' => '1',
                        'routingOrder' => '1',
                        'tabs' => [
                            'signHereTabs' => [
                                [
                                    'documentId' => '1',
                                    'pageNumber' => '1',
                                    'xPosition' => '100',
                                    'yPosition' => '650'
                                ]
                            ]
                        ]
                    ],
                    [
                        'email' => $williamEmail,
                        'name' => $williamName,
                        'recipientId' => '2',
                        'routingOrder' => '2',
                        'tabs' => [
                            'signHereTabs' => [
                                [
                                    'documentId' => '1',
                                    'pageNumber' => '1',
                                    'xPosition' => '400',
                                    'yPosition' => '650'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'status' => 'sent'
        ];
        
        // Send envelope
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->basePath . '/v2.1/accounts/' . $this->accountId . '/envelopes');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($envelopeDefinition));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception('Failed to send envelope: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get envelope status
     */
    public function getEnvelopeStatus($envelopeId) {
        if (!$this->accessToken) {
            $this->authenticate();
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->basePath . '/v2.1/accounts/' . $this->accountId . '/envelopes/' . $envelopeId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode != 200) {
            throw new Exception('Failed to get envelope status: ' . $response);
        }
        
        return json_decode($response, true);
    }
}
?>
