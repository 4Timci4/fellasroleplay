<?php
declare(strict_types=1);

namespace Core\Exceptions;

/**
 * API istekleri sırasında oluşan hatalar için exception sınıfı
 * 
 * Discord API veya diğer dış API'lerle iletişim sırasında oluşabilecek
 * hatalar için kullanılır. İstek detayları, yanıt kodları ve hata
 * mesajları gibi bilgileri taşır.
 * 
 * @package Core\Exceptions
 */
class APIException extends AppException
{
    /**
     * API yanıt kodu
     * @var int|null
     */
    protected ?int $apiStatusCode = null;
    
    /**
     * API endpoint'i
     * @var string|null
     */
    protected ?string $endpoint = null;
    
    /**
     * API isteği
     * @var array|null
     */
    protected ?array $request = null;
    
    /**
     * API yanıtı
     * @var mixed
     */
    protected $response = null;
    
    /**
     * Constructor
     * 
     * @param string $message Hata mesajı
     * @param string|null $endpoint API endpoint'i
     * @param int|null $apiStatusCode API yanıt kodu
     * @param array|null $request İstek verileri
     * @param mixed $response Yanıt verileri
     * @param int $code Hata kodu
     * @param string $userMessage Kullanıcıya gösterilecek mesaj
     * @param array $context Ek hata verileri
     * @param \Throwable|null $previous Önceki exception
     */
    public function __construct(
        string $message = "API isteği sırasında bir hata oluştu",
        ?string $endpoint = null,
        ?int $apiStatusCode = null,
        ?array $request = null,
        $response = null,
        int $code = 0,
        string $userMessage = "Uzak sunucu ile iletişim sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.",
        array $context = [],
        \Throwable $previous = null
    ) {
        $this->apiStatusCode = $apiStatusCode;
        $this->endpoint = $endpoint;
        $this->request = $request;
        $this->response = $response;
        
        // Context'e API bilgilerini ekle
        $apiContext = [
            'endpoint' => $endpoint,
            'api_status_code' => $apiStatusCode,
        ];
        
        // İstek ve yanıt verilerini sadece debug amaçlı olarak context'e ekle
        // Bu bilgiler üretim ortamında loglanır ancak kullanıcıya gösterilmez
        if ($request !== null) {
            // Hassas verileri temizle
            $safeRequest = $this->sanitizeSensitiveData($request);
            $apiContext['request'] = $safeRequest;
        }
        
        if ($response !== null) {
            $apiContext['response'] = $response;
        }
        
        $context = array_merge($context, $apiContext);
        
        parent::__construct($message, $code, 502, $userMessage, $context, $previous);
    }
    
    /**
     * API yanıt kodunu döndürür
     * 
     * @return int|null
     */
    public function getApiStatusCode(): ?int
    {
        return $this->apiStatusCode;
    }
    
    /**
     * API endpoint'ini döndürür
     * 
     * @return string|null
     */
    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }
    
    /**
     * API isteğini döndürür
     * 
     * @return array|null
     */
    public function getRequest(): ?array
    {
        return $this->request;
    }
    
    /**
     * API yanıtını döndürür
     * 
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
    
    /**
     * HTTP hata kodları için static factory metodu
     * 
     * @param int $statusCode HTTP durum kodu
     * @param string $endpoint API endpoint
     * @param array|null $request İstek verileri
     * @param mixed $response Yanıt verileri
     * @param string|null $message Özel hata mesajı (null ise otomatik oluşturulur)
     * @return APIException
     */
    public static function httpError(
        int $statusCode,
        string $endpoint,
        ?array $request = null,
        $response = null,
        ?string $message = null
    ): APIException {
        if ($message === null) {
            $message = sprintf("API yanıtı başarısız: %d - %s", $statusCode, self::getHttpStatusMessage($statusCode));
        }
        
        $userMessage = "Uzak sunucu ile iletişim sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
        
        return new self(
            $message,
            $endpoint,
            $statusCode,
            $request,
            $response,
            0,
            $userMessage
        );
    }
    
    /**
     * Discord API hatası için static factory metodu
     * 
     * @param string $endpoint Discord API endpoint
     * @param int $statusCode HTTP durum kodu
     * @param array|null $request İstek verileri
     * @param mixed $response Yanıt verileri
     * @param string|null $message Özel hata mesajı
     * @return APIException
     */
    public static function discordApiError(
        string $endpoint,
        int $statusCode,
        ?array $request = null,
        $response = null,
        ?string $message = null
    ): APIException {
        if ($message === null) {
            $message = sprintf("Discord API hatası: %d - %s", $statusCode, self::getHttpStatusMessage($statusCode));
        }
        
        $userMessage = "Discord ile iletişim sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
        
        return new self(
            $message,
            $endpoint,
            $statusCode,
            $request,
            $response,
            0,
            $userMessage
        );
    }
    
    /**
     * Bağlantı hatası için static factory metodu
     * 
     * @param string $endpoint API endpoint
     * @param string $error Hata mesajı
     * @param array|null $request İstek verileri
     * @return APIException
     */
    public static function connectionError(
        string $endpoint,
        string $error,
        ?array $request = null
    ): APIException {
        $message = sprintf("API bağlantı hatası: %s", $error);
        
        return new self(
            $message,
            $endpoint,
            null,
            $request,
            null,
            0,
            "Uzak sunucuya bağlanılamadı. Lütfen daha sonra tekrar deneyin."
        );
    }
    
    /**
     * HTTP durum kodu için genel mesaj döndürür
     * 
     * @param int $statusCode HTTP durum kodu
     * @return string
     */
    private static function getHttpStatusMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout'
        ];
        
        return $messages[$statusCode] ?? 'Unknown Status';
    }
    
    /**
     * Hassas verileri istek dizisinden temizler
     * 
     * @param array $request İstek dizisi
     * @return array Temizlenmiş istek dizisi
     */
    private function sanitizeSensitiveData(array $request): array
    {
        $sensitiveKeys = ['password', 'token', 'api_key', 'secret', 'authorization'];
        $result = $request;
        
        foreach ($sensitiveKeys as $key) {
            if (isset($result[$key])) {
                $result[$key] = '***REDACTED***';
            }
            
            // Headers dizisini de kontrol et
            if (isset($result['headers']) && is_array($result['headers'])) {
                foreach ($sensitiveKeys as $headerKey) {
                    $headerKey = strtolower($headerKey);
                    if (isset($result['headers'][$headerKey])) {
                        $result['headers'][$headerKey] = '***REDACTED***';
                    }
                }
            }
        }
        
        return $result;
    }
}
