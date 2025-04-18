<?php
declare(strict_types=1);

namespace Core\Exceptions;

/**
 * Veritabanı işlemleri sırasında oluşan hatalar için exception sınıfı
 * 
 * Veritabanı bağlantısı, sorgu çalıştırma, veri ekleme/güncelleme
 * gibi işlemlerde oluşabilecek hataları yönetir.
 * 
 * @package Core\Exceptions
 */
class DatabaseException extends AppException
{
    /**
     * SQL sorgusu
     * @var string|null
     */
    protected ?string $query = null;
    
    /**
     * Constructor
     * 
     * @param string $message Hata mesajı
     * @param string|null $query Hataya neden olan SQL sorgusu
     * @param int $code Hata kodu
     * @param string $userMessage Kullanıcıya gösterilecek mesaj
     * @param array $context Ek hata verileri
     * @param \Throwable|null $previous Önceki exception
     */
    public function __construct(
        string $message = "Veritabanı işlemi sırasında bir hata oluştu",
        ?string $query = null,
        int $code = 0,
        string $userMessage = "Veritabanı işlemi gerçekleştirilemedi. Lütfen daha sonra tekrar deneyin.",
        array $context = [],
        \Throwable $previous = null
    ) {
        $this->query = $query;
        
        // Eğer sorgu verilmişse ve bağlam içinde sorgu yoksa, sorguyu bağlama ekle
        if ($query !== null && !isset($context['query'])) {
            $context['query'] = $query;
        }
        
        parent::__construct($message, $code, 500, $userMessage, $context, $previous);
    }
    
    /**
     * SQL sorgusunu döndürür
     * 
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }
    
    /**
     * Veritabanı bağlantı hatası için static factory metodu
     * 
     * @param string $message Hata mesajı
     * @param int $code Hata kodu
     * @param \Throwable|null $previous Önceki exception
     * @return DatabaseException
     */
    public static function connectionError(
        string $message = "Veritabanı bağlantısı kurulamadı",
        int $code = 0,
        \Throwable $previous = null
    ): DatabaseException {
        return new self(
            $message,
            null,
            $code,
            "Veritabanına bağlanılamadı. Lütfen daha sonra tekrar deneyin.",
            [],
            $previous
        );
    }
    
    /**
     * Sorgu hatası için static factory metodu
     * 
     * @param string $query Hataya neden olan SQL sorgusu
     * @param string $message Hata mesajı
     * @param int $code Hata kodu
     * @param \Throwable|null $previous Önceki exception
     * @return DatabaseException
     */
    public static function queryError(
        string $query,
        string $message = "SQL sorgusu çalıştırılırken hata oluştu",
        int $code = 0,
        \Throwable $previous = null
    ): DatabaseException {
        return new self(
            $message,
            $query,
            $code,
            "Veri işleme sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.",
            [],
            $previous
        );
    }
    
    /**
     * Kayıt bulunamadı hatası için static factory metodu
     * 
     * @param string $entity Aranan entity türü (örn. 'kullanıcı', 'forum konusu')
     * @param mixed $id Entity ID'si
     * @param string|null $query İlgili SQL sorgusu
     * @param int $code Hata kodu
     * @return DatabaseException
     */
    public static function recordNotFound(
        string $entity,
        $id,
        ?string $query = null,
        int $code = 0
    ): DatabaseException {
        return new self(
            sprintf("%s bulunamadı (ID: %s)", $entity, $id),
            $query,
            $code,
            sprintf("Belirtilen %s bulunamadı.", $entity),
            ['entity' => $entity, 'id' => $id]
        );
    }
}
