<?php
declare(strict_types=1);

namespace Core\Exceptions;

/**
 * Uygulama genelinde kullanılan temel Exception sınıfı
 * 
 * Tüm özel exception sınıfları bu temel sınıftan türetilir.
 * Böylece Exception türüne göre farklı işlemler yapılabilir.
 * 
 * @package Core\Exceptions
 */
class AppException extends \Exception
{
    /**
     * Hata kodu
     * @var int
     */
    protected $code;
    
    /**
     * HTTP durum kodu
     * @var int
     */
    protected $httpStatusCode;
    
    /**
     * Kullanıcıya gösterilecek hata mesajı
     * @var string
     */
    protected $userMessage;
    
    /**
     * Ek hata verileri
     * @var array
     */
    protected $context = [];

    /**
     * Constructor
     * 
     * @param string $message Hata mesajı (teknik)
     * @param int $code Hata kodu
     * @param int $httpStatusCode HTTP durum kodu
     * @param string $userMessage Kullanıcıya gösterilecek mesaj
     * @param array $context Ek hata verileri
     * @param \Throwable|null $previous Önceki exception
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        int $httpStatusCode = 500,
        string $userMessage = "",
        array $context = [],
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->httpStatusCode = $httpStatusCode;
        $this->userMessage = $userMessage ?: $message;
        $this->context = $context;
    }
    
    /**
     * HTTP durum kodunu döndürür
     * 
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
    
    /**
     * Kullanıcıya gösterilecek mesajı döndürür
     * 
     * @return string
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }
    
    /**
     * Ek hata verilerini döndürür
     * 
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
    
    /**
     * Exception'ı log formatında döndürür
     * 
     * @return string
     */
    public function getLogMessage(): string
    {
        $message = sprintf(
            "Exception: %s [%d] %s in %s on line %d",
            get_class($this),
            $this->code,
            $this->message,
            $this->file,
            $this->line
        );
        
        if (!empty($this->context)) {
            $message .= " Context: " . json_encode($this->context);
        }
        
        return $message;
    }
}
