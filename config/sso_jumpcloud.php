<?php

// return [
//     'idp' => [
//         'entityId' => env('SAML2_IDP_ENTITYID'),
//         'sso'      => env('SAML2_IDP_SSO'),
//         'x509'     => env('SAML2_IDP_X509'),
//     ],
// ];




    // 'strict' => true,
    // 'debug' => true,

    // 'sp' => [
    //     'entityId' => env('APP_URL') . '/saml/metadata',
    //     'assertionConsumerService' => [
    //         'url' => env('APP_URL') . '/saml/acs',
    //     ],
    //     'sso' => [
    //         'url' => env('APP_URL') . '/saml/logout',
    //     ],
    //     'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
    //     'x509' => '',
    //     'privateKey' => '',
    // ],

  return [
    'strict' => true,
    'debug' => true,
    'sp' => [
        'entityId' => 'http://beta.localhost:8000/sso/metadata',
        'assertionConsumerService' => [
            'url' => 'http://beta.localhost:8000/sso/acs',

        ],
        'singleLogoutService' => [
            'url' => 'http://beta.localhost:8000/sso/logout',
        ],
        'x509cert' => '', 
        'privateKey' => '', 
    ],
    'idp' => [
        'entityId' => 'JumpCloud', // Your IdP entity ID
        'singleSignOnService' => [
            'url' => 'https://sso.jumpcloud.com/saml2/grab', // Your IdP SSO URL
        ],
        'singleLogoutService' => [
            'url' => 'https://sso.jumpcloud.com/saml2/grab/logout', // Your IdP SLO URL
        ],
        'x509cert' => '-----BEGIN CERTIFICATE-----
MIIFiDCCA3CgAwIBAgIUKLcWq7xAjOiG6KEREG7c0GXdtU0wDQYJKoZIhvcNAQELBQAwfjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNPMRAwDgYDVQQHEwdCb3VsZGVyMRkwFwYDVQQKExBzdGVsbGVuIGluZm90ZWNoMRkwFwYDVQQLExBKdW1wQ2xvdWRTQU1MSWRQMRowGAYDVQQDExFKdW1wQ2xvdWRTQU1MVXNlcjAeFw0yNTEwMzAwNzU5NDVaFw0zMDEwMzAwNzU5NDVaMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDTzEQMA4GA1UEBxMHQm91bGRlcjEZMBcGA1UEChMQc3RlbGxlbiBpbmZvdGVjaDEZMBcGA1UECxMQSnVtcENsb3VkU0FNTElkUDEaMBgGA1UEAxMRSnVtcENsb3VkU0FNTFVzZXIwggIiMA0GCSqGSIb3DQEBAQUAA4ICDwAwggIKAoICAQC+jBdIgRAwBvfW7cE+P2rCIeBQq911PDsira7FIONdtSs0+Li+ptDXZTrO+gPWL2Mi6p8gjOGVcnpJ1daQM+8/8E+TG1OvqEbI3GnZFW1umgQ38a4Uc0AEf/UoRDWQPx7sqkNwXd+v22PHRz4fanH8VUixcwMoqv3NkCFMHQVkHZkR8MeZGID1ww/4CwqCqu19wARMTnoKsi146b0ipLfYy/sA46Bp6l0wXCkXRVIvnNMznk0rLa6YjGRpQChiuO7ZWoOe9h/sy+AWTplaOf1jgyqkw0NYY6vsjpjj5mVG6SqE3KpVbMLCgSSu2Qtto7IUDH0ur73DUYGfWb3S8aFn07lzqTr+mXxcCnobsckiDJtixWiVTjMS0nuyg73adbBDBnF6EJRRkKcmvUmJSjWtAbourGH8S5Tf/9X4pPyOeCT7li3BM9fyzpHcL7gtxOxxMat1QB6b5dO9u0xZb87kmOoF4mq9nHrytXvAPdY0j0p2SM3Og0iSUEANFK4hxkn88A3H8e/Fq2FL4WkmSezevRvqVRvwJvLBYhbh4WjkT8Ae+fIDosAHKOgTeSJudTXZJ/tlGasQxW3JXIeHuEWXifvZMYoT2rHYZx1pXIQDlaIvqxSr+H6KTJH4nthtIhI0z6u6nTD/KL4avi0nvq51fNAfFd0sTd7Z3jO9nQmWLwIDAQABMA0GCSqGSIb3DQEBCwUAA4ICAQAKgRE1eC4mxTQOtOfyihIM458OWq9TUy5MC21w52QXGsQtn6+ADMEDUKBxdNGq2WwbE9lLh1TKKcq4Zmf6t52MSMpMt1hYxBowGqKlFTIL6ujjcHmMpVVpryLhxgv78Y/YE3VBBNf+ZHc0qe/DgeCQQM4ZoKtfHQEq43+Cp3QwqJKFitQ9KwyYtWd4TbxE9Pnctn2N1+qQJjg/7/BxV2Nw72TjJjBvmdppy1Eb+8qxo852wlVUEYHOlfCA6ycHwiuMN9cfsZYxU2YtuusU0LDu17B++5pyoPyOVtZc5oGlBa2Zo1FyLoF8lZEHG7MXAa5QIFtsdZn6BgtvR/23xF5jpuFwzll9xc89QjCJHRXJ4UG5ZN2Rd4GMWFNb08rwfDWOZBPoaLsRDN3xnMuOTWZb09tDyrcHRNWOP5BklmaTSY4lhjrEoPBGJfa2j3oRP6btJIcxJ/rvWboYtM2/oMO/k2ZWSIPhY0qvFyw/BrZP1UZnp71qdoBqXwgg4RETm0yf/sg3E5ol/WBShS3MCLYhwrlTYGFt7CiQkOI6R7gt2rdV1HDCqZVAq718xbGs2ipsUVhaHzPjQxEqbM+A4DKI21g+MoXa29wvs2pF14EPYLuULeM3/mi1u9Hs0D48mRehGczrTD3x/QZ1iPa3F30N+490fMVRtlTUlPU24kBXbg==
-----END CERTIFICATE-----',
    ],
];

