<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'        => 'The :attribute must be accepted.',
    'active_url'      => 'The :attribute is not a valid URL.',
    'after'           => 'The :attribute must be a date after :date.',
    'after_or_equal'  => 'The :attribute must be a date after or equal to :date.',
    'alpha'           => 'The :attribute may only contain letters.',
    'alpha_dash'      => 'The :attribute may only contain letters, numbers, dashes and underscores.',
    'alpha_num'       => 'The :attribute may only contain letters and numbers.',
    'array'           => 'The :attribute must be an array.',
    'before'          => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between'         => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'        => 'The :attribute field must be true or false.',
    'confirmed'      => 'The :attribute confirmation does not match.',
    'date'           => 'The :attribute is not a valid date.',
    'date_equals'    => 'The :attribute must be a date equal to :date.',
    'date_format'    => 'The :attribute does not match the format :format.',
    'different'      => 'The :attribute and :other must be different.',
    'digits'         => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions'     => 'The :attribute has invalid image dimensions.',
    'distinct'       => 'The :attribute field has a duplicate value.',
    'email'          => 'The :attribute must be a valid email address.',
    'ends_with'      => 'The :attribute must end with one of the following: :values',
    'exists'         => 'The selected :attribute is invalid.',
    'file'           => 'The :attribute must be a file.',
    'filled'         => 'The :attribute field must have a value.',
    'gt'             => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file'    => 'The :attribute must be greater than :value kilobytes.',
        'string'  => 'The :attribute must be greater than :value characters.',
        'array'   => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal :value.',
        'file'    => 'The :attribute must be greater than or equal :value kilobytes.',
        'string'  => 'The :attribute must be greater than or equal :value characters.',
        'array'   => 'The :attribute must have :value items or more.',
    ],
    'image'    => 'The :attribute must be an image.',
    'in'       => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer'  => 'The :attribute must be an integer.',
    'ip'       => 'The :attribute must be a valid IP address.',
    'ipv4'     => 'The :attribute must be a valid IPv4 address.',
    'ipv6'     => 'The :attribute must be a valid IPv6 address.',
    'json'     => 'The :attribute must be a valid JSON string.',
    'lt'       => [
        'numeric' => 'The :attribute must be less than :value.',
        'file'    => 'The :attribute must be less than :value kilobytes.',
        'string'  => 'The :attribute must be less than :value characters.',
        'array'   => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal :value.',
        'file'    => 'The :attribute must be less than or equal :value kilobytes.',
        'string'  => 'The :attribute must be less than or equal :value characters.',
        'array'   => 'The :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'     => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min'       => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'not_regex'            => 'The :attribute format is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values are present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values',
    'string'      => 'The :attribute must be a string.',
    'timezone'    => 'The :attribute must be a valid zone.',
    'unique'      => 'The :attribute has already been taken.',
    'uploaded'    => 'The :attribute failed to upload.',
    'url'         => 'The :attribute format is invalid.',
    'uuid'        => 'The :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'terms' => [
            'required' => 'You must agree to our Terms of Service and Privacy Policy before you can register.',
        ],

        'permissions' => [
            'required' => 'You need to select at least one permission.',
        ],

        'fees' => [
            'static' => [
                'transfer' => [
                    'required' => 'The transfer fee field is required.',
                ],
                'secondSignature' => [
                    'required' => 'The second signature fee field is required.',
                ],
                'delegateRegistration' => [
                    'required' => 'The delegate registration fee field is required.',
                ],
                'vote' => [
                    'required' => 'The vote fee field is required.',
                ],
                'multiSignature' => [
                    'required' => 'The multi signature fee field is required.',
                ],
                'ipfs' => [
                    'required' => 'The IPFS fee field is required.',
                ],
                'multiPayment' => [
                    'required' => 'The multipayment fee field is required.',
                ],
                'delegateResignation' => [
                    'required' => 'The delegate resignation fee field is required.',
                ],
            ],
            'dynamic' => [
                'minFeePool' => [
                    'required' => 'The min fee pool field is required.',
                ],
                'minFeeBroadcast' => [
                    'required' => 'The min fee broadcast field is required.',
                ],
                'addonBytes' => [
                    'transfer' => [
                        'required_if' => 'The transfer fee field is required when dynamic fees are enabled.',
                    ],
                    'secondSignature' => [
                        'required_if' => 'The second signature fee field is required when dynamic fees are enabled.',
                    ],
                    'delegateRegistration' => [
                        'required_if' => 'The delegate registration fee field is required when dynamic fees are enabled.',
                    ],
                    'vote' => [
                        'required_if' => 'The vote fee field is required when dynamic fees are enabled.',
                    ],
                    'multiSignature' => [
                        'required_if' => 'The multi signature fee field is required when dynamic fees are enabled.',
                    ],
                    'ipfs' => [
                        'required_if' => 'The IPFS fee field is required when dynamic fees are enabled.',
                    ],
                    'multiPayment' => [
                        'required_if' => 'The multipayment fee field is required when dynamic fees are enabled.',
                    ],
                    'delegateResignation' => [
                        'required_if' => 'The delegate resignation fee field is required when dynamic fees are enabled.',
                    ],
                ],
            ],
        ],
        'email' => [
            'unique' => 'The email has already been registered.',
        ],
        'blacklisted' => 'The :attribute contains a reserved name.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

    'messages' => [
        'address_prefix'                              => 'The address prefix is invalid.',
        'bip39_passphrase'                            => 'The given passphrase is not a BIP39 Passphrase. Please enter a valid BIP39 passphrase to continue.',
        'current_password'                            => 'The given password does not match our records.',
        'current_name'                                => 'The given name does not match our records.',
        'one_time_password'                           => 'We were not able to enable two-factor authentication with this one-time password.',
        'port'                                        => 'The port is out of range.',
        'secure_shell_key'                            => 'The SSH key is invalid.',
        'strong_password'                             => 'The :attribute must be 12–128 characters, and include a number, a symbol, a lower and an upper case letter.',
        'unique_token_extra_attribute'                => 'The provided :key already exists.',
        'valid_digital_ocean_server_name'             => 'The server name does not accept spaces.',
        'valid_hetzner_server_name'                   => 'The server name may only contain letters, digits, periods, and dashes.',
        'valid_linode_server_name'                    => 'The server name may only contain letters, digits, periods, and dashes and cannot have two dashes (--), underscores (__) or periods (..) in a row.',
    ],
];
