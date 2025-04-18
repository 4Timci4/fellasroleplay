<?php
declare(strict_types=1);

namespace Core\Exceptions;

/**
 * Doğrulama hatalarını işlemek için exception sınıfı
 * 
 * Form doğrulama, veri doğrulama gibi işlemlerde oluşabilecek
 * hatalar için kullanılır. Birden fazla doğrulama hatasını
 * aynı anda yönetebilir.
 * 
 * @package Core\Exceptions
 */
class ValidationException extends AppException
{
    /**
     * Doğrulama hataları
     * @var array
     */
    protected array $errors = [];
    
    /**
     * Constructor
     * 
     * @param array $errors Doğrulama hataları [alan => hata mesajı]
     * @param string $message Genel hata mesajı
     * @param int $code Hata kodu
     * @param string $userMessage Kullanıcıya gösterilecek genel mesaj
     * @param array $context Ek hata verileri
     * @param \Throwable|null $previous Önceki exception
     */
    public function __construct(
        array $errors = [],
        string $message = "Doğrulama hatası",
        int $code = 0,
        string $userMessage = "Girdiğiniz bilgilerde hatalar var. Lütfen kontrol edip tekrar deneyin.",
        array $context = [],
        \Throwable $previous = null
    ) {
        $this->errors = $errors;
        
        // Context'e errors dizisini ekle
        if (!empty($errors) && !isset($context['errors'])) {
            $context['errors'] = $errors;
        }
        
        parent::__construct($message, $code, 400, $userMessage, $context, $previous);
    }
    
    /**
     * Tüm doğrulama hatalarını döndürür
     * 
     * @return array [alan => hata mesajı] formatında
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Belirli bir alan için hata mesajını döndürür
     * 
     * @param string $field Alan adı
     * @return string|null Hata mesajı veya null
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Belirli bir alanın hatalı olup olmadığını kontrol eder
     * 
     * @param string $field Alan adı
     * @return bool
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }
    
    /**
     * Hata mesajlarını HTML formatında döndürür
     * 
     * @return string
     */
    public function getErrorsAsHtml(): string
    {
        if (empty($this->errors)) {
            return '';
        }
        
        $html = '<div class="validation-errors">';
        $html .= '<ul>';
        
        foreach ($this->errors as $field => $message) {
            $html .= '<li>' . htmlspecialchars($message) . '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Yeni bir ValidationException oluşturmak için static factory metodu
     * 
     * @param array $errors Doğrulama hataları
     * @return ValidationException
     */
    public static function withErrors(array $errors): ValidationException
    {
        return new self($errors);
    }
    
    /**
     * Tek bir hata ile ValidationException oluşturmak için static factory metodu
     * 
     * @param string $field Alan adı
     * @param string $message Hata mesajı
     * @return ValidationException
     */
    public static function forField(string $field, string $message): ValidationException
    {
        return new self([$field => $message]);
    }
}
