<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude('tests/cache')
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'yoda_style' => false,
        'native_constant_invocation' => false,
        'no_superfluous_phpdoc_tags' => [
            'remove_inheritdoc' => false,
        ],
        'nullable_type_declaration_for_default_null_value' => false,
        'declare_strict_types' => true,

        // Can be removed once PHP requirement is upgraded
        'get_class_to_class_keyword' => false,
        'modernize_strpos' => false,
    ])
    ->setFinder($finder)
    ;
