<style>
    /* Sayı input alanlarındaki artırma/azaltma butonlarını gizle */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
        /* Firefox */
    }
    
    /* Form bölümleri için stil */
    .form-section {
        display: none;
        animation: fadeIn 0.5s ease-in-out;
    }
    
    .form-section.active {
        display: block;
    }
    
    /* Radio butonlar için özel stil */
    .radio-container input[type="radio"]:checked ~ div .radio-circle .w-3 {
        transform: scale(1);
    }
    
    .radio-container input[type="radio"]:checked ~ div .radio-circle {
        border-color: var(--color-primary);
    }
    
    .radio-container input[type="radio"]:checked + div {
        background-color: rgba(var(--color-primary-rgb), 0.1);
    }
    
    /* Checkbox için özel stil */
    .checkbox-container input[type="checkbox"]:checked ~ .checkbox-box {
        border-color: var(--color-primary);
        background-color: rgba(var(--color-primary-rgb), 0.1);
    }
    
    .checkbox-container input[type="checkbox"]:checked ~ .checkbox-box .checkbox-icon {
        transform: scale(1);
    }
    
    /* Animasyonlar */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(var(--color-primary-rgb), 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(var(--color-primary-rgb), 0); }
        100% { box-shadow: 0 0 0 0 rgba(var(--color-primary-rgb), 0); }
    }
</style>
