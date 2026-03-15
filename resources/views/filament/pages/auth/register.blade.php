<x-filament-panels::page>
    @push('styles')
    <style>
        /* Responsive register page for mobile */
        @media (max-width: 768px) {
            /* Full-width form and comfortable padding */
            .fi-simple-page main,
            [data-page="register"] main,
            .fi-wizard {
                width: 100%;
                max-width: 100%;
                padding-left: 1rem;
                padding-right: 1rem;
                box-sizing: border-box;
            }
            [data-page="register"] .fi-wizard .fi-fo-field-wrp,
            .fi-wizard .fi-fo-field-wrp {
                max-width: 100%;
            }
            [data-page="register"] .fi-wizard input,
            [data-page="register"] .fi-wizard textarea,
            [data-page="register"] .fi-wizard [data-slot="input"],
            .fi-wizard input,
            .fi-wizard textarea,
            .fi-wizard [data-slot="input"] {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
            .fi-wizard .fi-fo-phone-input,
            .fi-wizard .fi-fo-phone-input .tel-input {
                width: 100% !important;
                max-width: 100%;
            }
            /* Stack wizard step indicators on small screens if needed */
            .fi-wizard .fi-wi-steps {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
        }
    </style>
    @endpush
</x-filament-panels::page>
